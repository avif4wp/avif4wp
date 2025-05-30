<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_ConvertSelector {

    private $converter;

    public function __construct() {
        require_once __DIR__ . '/../lib/class-avif4wp-converter-avif.php';
        $this->converter = new Avif4WP_ConverterAVIF();
        $this->init_hooks();
    }

    public function init_hooks() {
        add_filter( 'wp_handle_upload',                [ $this, 'handle_upload' ],               10, 2 );
        add_filter( 'wp_generate_attachment_metadata', [ $this, 'update_attachment_metadata' ], 10, 2 );
        add_filter( 'wp_get_attachment_url',           [ $this, 'redirect_to_converted' ],      10, 2 );
        add_filter( 'the_content',                     [ $this, 'process_editor_images' ] );
    }

    public function handle_upload( $upload, $context = null ) {
        require_once __DIR__ . '/../lib/class-avif4wp-autoconvert-timeout.php';
        Avif4WP_AutoConvert_Timeout::apply_timeout();

        $file_path    = $upload['file'];
        $ext          = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
        $cache_active = ( 'yes' === get_option( 'avif_cache_active', 'no' ) );

        if ( in_array( $ext, [ 'jpg', 'jpeg', 'png' ], true ) ) {
            if ( $cache_active ) {
                require_once __DIR__ . '/../lib/class-avif4wp-autoconvert-helper.php';
                Avif4WP_AutoConverterHelper::queue_image( $file_path );
                Avif4WP_AutoConverterHelper::process_batch();
            } else {
                $converted = $this->converter->convert( $file_path );
            }
        }

        return $upload;
    }

    public function update_attachment_metadata( $metadata, $attachment_id ) {
        $file_path = get_attached_file( $attachment_id );
        if ( ! preg_match( '/\.(jpe?g|png)$/i', $file_path ) ) {
            return $metadata;
        }

        $upload_dir     = wp_upload_dir();
        $output_format  = 'avif';
        $converted_file = preg_replace( '/\.(jpe?g|png)$/i', '.' . $output_format, $file_path );

        if ( ! file_exists( $converted_file ) ) {
            $conv = $this->converter->convert( $file_path );
            if ( $conv && file_exists( $conv ) ) {
                $converted_file = $conv;
            } else {
                return $metadata;
            }
        }

        remove_filter( 'wp_generate_attachment_metadata', [ $this, 'update_attachment_metadata' ], 10 );
        $new_metadata = wp_generate_attachment_metadata( $attachment_id, $converted_file );
        add_filter( 'wp_generate_attachment_metadata', [ $this, 'update_attachment_metadata' ], 10, 2 );

        if ( ! empty( $new_metadata['sizes'] ) && is_array( $new_metadata['sizes'] ) ) {
            foreach ( $new_metadata['sizes'] as $key => &$size ) {
                $orig   = trailingslashit( $upload_dir['basedir'] ) . $size['file'];
                $target = preg_replace( '/\.(jpe?g|png)$/i', '.' . $output_format, $orig );

                if ( file_exists( $target ) ) {
                    $size['file'] = basename( $target );
                } else {
                    $conv = $this->converter->convert( $orig );
                    if ( $conv && file_exists( $conv ) ) {
                        $size['file'] = basename( $conv );
                    }
                }
            }
        }

        if ( empty( $new_metadata['sizes']['thumbnail'] ) ) {
            $new_metadata['sizes']['thumbnail'] = [
                'file'      => basename( $converted_file ),
                'width'     => $new_metadata['width'],
                'height'    => $new_metadata['height'],
                'mime-type' => 'image/' . $output_format,
                'filesize'  => file_exists( $converted_file ) ? filesize( $converted_file ) : 0,
            ];
        }

        wp_update_attachment_metadata( $attachment_id, $new_metadata );

        $this->cleanup_attachment( $attachment_id );
        $this->force_cleanup_uploads();
        if ( class_exists( 'Avif4WP_ConvertHelper' ) ) {
            Avif4WP_ConvertHelper::cleanup_srcset_files();
        }
        $this->maybe_delete_original_and_scaled( $file_path );

        return $new_metadata;
    }

    public function redirect_to_converted( $url, $post_id ) {
        $file = get_attached_file( $post_id );
        $avif = preg_replace( '/\.(jpe?g|png)$/i', '.avif', $file );
        if ( file_exists( $avif ) ) {
            return preg_replace( '/\.(jpe?g|png)$/i', '.avif', $url );
        }
        return $url;
    }

    public function process_editor_images( $content ) {
        $upload_dir    = wp_upload_dir();
        $output_format = 'avif';

        return preg_replace_callback(
            '/<img\s+([^>]+?)src=["\']([^"\']+\.(?:jpe?g|png))["\']([^>]*)>/i',
            function( $m ) use ( $upload_dir, $output_format ) {
                $url = $m[2];
                if ( false === strpos( $url, $upload_dir['baseurl'] ) ) {
                    return $m[0];
                }
                $rel  = str_replace( trailingslashit( $upload_dir['baseurl'] ), '', $url );
                $file = trailingslashit( $upload_dir['basedir'] ) . $rel;
                $avif = preg_replace( '/\.(jpe?g|png)$/i', '.avif', $file );

                if ( ! file_exists( $avif ) ) {
                    $conv = $this->converter->convert( $file );
                    if ( ! ( $conv && file_exists( $conv ) ) ) {
                        return $m[0];
                    }
                    $avif = $conv;
                }

                $new_url = trailingslashit( $upload_dir['baseurl'] ) . basename( $avif );
                $attrs   = trim( $m[1] );
                $extra   = trim( $m[3] );
                $srcset  = esc_url( $new_url ) . ' 1x, ' . esc_url( $new_url ) . ' 2x';

                return '<img ' . $attrs .
                       ' src="' . esc_url( $new_url ) .
                       '" srcset="' . esc_attr( $srcset ) . '" ' .
                       $extra . '>';
            },
            $content
        );
    }

    private function get_running_conversions() {
        $running = get_transient( 'avif_running_conversions' );
        if ( false === $running ) {
            $running = 0;
            set_transient( 'avif_running_conversions', $running, 3600 );
        }
        return (int) $running;
    }

    private function queue_conversion( $file_path ) {
        $queue = get_transient( 'avif_conversion_queue' ) ?: [];
        $queue[] = $file_path;
        set_transient( 'avif_conversion_queue', $queue, 3600 );
    }

    private function get_cached_conversion( $file_path ) {
        return wp_cache_get( 'avif_conversion_' . md5( $file_path ) );
    }

    private function cache_conversion( $file_path, $result ) {
        wp_cache_set( 'avif_conversion_' . md5( $file_path ), $result, '', 3600 );
    }

    private function get_attachment_metadata( $attachment_id ) {
        $key = 'avif_metadata_' . $attachment_id;
        if ( false === ( $meta = wp_cache_get( $key ) ) ) {
            $meta = wp_get_attachment_metadata( $attachment_id );
            wp_cache_set( $key, $meta );
        }
        return $meta;
    }

    private function batch_process_files( $files, $batch_size = 10 ) {
        foreach ( array_chunk( $files, $batch_size ) as $batch ) {
            foreach ( $batch as $file ) {
                $this->converter->convert( $file );
            }
        }
    }

    private function maybe_delete_original_and_scaled( $file_path ) {
        if ( 'yes' === get_option( 'avif_delete_original', 'no' ) && file_exists( $file_path ) ) {
            @unlink( $file_path );
            if ( strpos( $file_path, '-scaled' ) !== false ) {
                $this->delete_non_scaled_file( $file_path );
            }
        }
    }

    private function delete_non_scaled_file( $scaled ) {
        $orig = str_replace( '-scaled', '', $scaled );
        if ( $orig !== $scaled && file_exists( $orig ) ) {
            @unlink( $orig );
        }
    }

    private function cleanup_attachment( $attachment_id ) {
        $this->delete_scaled_files( $attachment_id );
    }

    private function delete_scaled_files( $attachment_id ) {
        $file = get_attached_file( $attachment_id );
        if ( ! $file || ! file_exists( $file ) ) {
            return;
        }
        $dir  = pathinfo( $file, PATHINFO_DIRNAME );
        $name = pathinfo( $file, PATHINFO_FILENAME );
        if ( is_dir( $dir ) ) {
            foreach ( scandir( $dir ) as $f ) {
                if ( strpos( $f, $name . '-scaled' ) === 0 ) {
                    $path = trailingslashit( $dir ) . $f;
                    if ( in_array( strtolower(pathinfo($f,PATHINFO_EXTENSION)), ['jpg','jpeg','png'], true ) ) {
                        @unlink( $path );
                    }
                }
            }
        }
    }

    private function force_cleanup_uploads() {
        $base = trailingslashit( wp_upload_dir()['basedir'] );
        $this->recursive_cleanup( $base );
    }

    private function recursive_cleanup( $dir ) {
        foreach ( scandir( $dir ) as $item ) {
            if ( in_array( $item, ['.', '..'], true ) ) {
                continue;
            }
            $full = trailingslashit( $dir ) . $item;
            if ( is_dir( $full ) ) {
                $this->recursive_cleanup( $full );
            } else {
                $ext = strtolower( pathinfo( $full, PATHINFO_EXTENSION ) );
                if ( in_array( $ext, ['jpg','jpeg','png'], true ) ) {
                    if ( strpos( $item, '-scaled' ) !== false
                      || preg_match('/-\d+x\d+\.(jpg|jpeg|png)$/i', $item ) ) {
                        @unlink( $full );
                    }
                }
            }
        }
    }
}