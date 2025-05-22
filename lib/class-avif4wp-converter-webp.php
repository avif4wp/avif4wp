<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_ConverterWebP {

    private $delete_original;
    private $enhanced_responsive;

    public function __construct() {
        $this->delete_original     = ( 'yes' === get_option( 'avif_delete_original', 'no' ) );
        $this->enhanced_responsive = ( 'yes' === get_option( 'avif_enhanced_responsive', 'no' ) );
    }

    public function convert( $file_path ) {
        return $this->convert_to_webp_gd( $file_path );
    }

    private function convert_to_webp_gd( $file_path ) {
        $settings      = new Avif4WP_Settings();
        $quality       = $settings->get_webp_quality();

        if ( ! function_exists( 'imagewebp' ) ) {
            return false;
        }

        $image_contents = @file_get_contents( $file_path );
        if ( false === $image_contents ) {
            return false;
        }

        $gd_image = @imagecreatefromstring( $image_contents );
        if ( false === $gd_image ) {
            return false;
        }

        if ( ! imageistruecolor( $gd_image ) ) {
            imagepalettetotruecolor( $gd_image );
        }

        $converted_file = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $file_path );
        $result         = imagewebp( $gd_image, $converted_file, $quality );
        imagedestroy( $gd_image );

        if ( $result && file_exists( $converted_file ) ) {
            $this->update_analytics( $file_path, $converted_file );
            return $converted_file;
        }

        return false;
    }

    private function update_analytics( $original_file, $converted_file ) {
        $original_size    = file_exists( $original_file ) ? filesize( $original_file ) : 0;
        $converted_size   = file_exists( $converted_file ) ? filesize( $converted_file ) : 0;
        $space_saved      = max( 0, $original_size - $converted_size );
        $images_converted = (int) get_option( 'avif4wp_images_converted', 0 );

        update_option( 'avif4wp_images_converted', $images_converted + 1 );
        $total_space_saved = (int) get_option( 'avif4wp_space_saved', 0 );
        update_option( 'avif4wp_space_saved', $total_space_saved + $space_saved );

        $output_format   = 'webp';
        $conversion_logs = get_option( 'avif4wp_conversion_logs', array() );
        $conversion_logs[] = array(
            'date'           => current_time( 'mysql' ),
            'image_name'     => basename( $original_file ),
            'original_size'  => $original_size,
            'converted_size' => $converted_size,
            'output'         => $output_format,
        );
        update_option( 'avif4wp_conversion_logs', $conversion_logs );
    }

    public function redirect_to_converted( $url, $post_id ) {
        $file_path     = get_attached_file( $post_id );
        $output_format = 'webp';

        if ( false !== strpos( $file_path, '-scaled' ) ) {
            return $url;
        }

        if ( ! preg_match( '/\.' . preg_quote( $output_format, '/' ) . '$/i', $file_path ) ) {
            $converted_file = preg_replace( '/\.(jpg|jpeg|png)$/i', '.' . $output_format, $file_path );
            if ( file_exists( $converted_file ) ) {
                $url = preg_replace( '/\.(jpg|jpeg|png)$/i', '.' . $output_format, $url );
            }
        }

        return $url;
    }
}