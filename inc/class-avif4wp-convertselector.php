<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_ConvertSelector {

    private $converter;

    public function __construct() {
        $output_format   = get_option( 'avif_output_format', 'avif' );
        $previous_format = get_option( 'avif_previous_format', 'avif' );

        if ( $output_format !== $previous_format ) {
            $this->handle_format_switch( $previous_format, $output_format );
        }

        if ( 'webp' === $output_format ) {
            require_once __DIR__ . '/../lib/class-avif4wp-converter-webp.php';
            $this->converter = new Avif4WP_ConverterWebP();
        } else {
            require_once __DIR__ . '/../lib/class-avif4wp-converter-avif.php';
            $this->converter = new Avif4WP_ConverterAVIF();
        }

        $this->init_hooks();
    }

	private function handle_format_switch( $previous_format, $new_format ) {
		update_option( 'avif_previous_format', $new_format );
		require_once __DIR__ . '/../lib/class-avif4wp-autoconvert-helper.php';
		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'post_mime_type' => array( 'image/jpeg', 'image/jpg', 'image/png' ),
			'posts_per_page' => -1,
			'fields'         => 'ids',
		) );
		foreach ( $attachments as $attachment_id ) {
			$file_path = get_attached_file( $attachment_id );
			if ( $file_path ) {
				Avif4WP_AutoConverterHelper::queue_image( $file_path );
			}
		}
		Avif4WP_AutoConverterHelper::process_batch();
	}	

    public function init_hooks() {
        add_filter( 'wp_handle_upload',         array( $this, 'handle_upload' ),               10, 2 );
        add_filter( 'wp_generate_attachment_metadata', array( $this, 'update_attachment_metadata' ), 10, 2 );
        add_filter( 'wp_get_attachment_url',    array( $this, 'redirect_to_converted' ),      10, 2 );
        add_filter( 'the_content',              array( $this, 'process_editor_images' ) );
    }

    public function handle_upload( $upload, $context = null ) {
		require_once __DIR__ . '/../lib/class-avif4wp-autoconvert-timeout.php';
		Avif4WP_AutoConvert_Timeout::apply_timeout( $batch );
		
		$file_path    = $upload['file'];
        $ext          = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
        $cache_active = ( 'yes' === get_option( 'avif_cache_active', 'no' ) );

        if ( $cache_active && in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {

            require_once __DIR__ . '/../lib/class-avif4wp-autoconvert-helper.php';

            Avif4WP_AutoConverterHelper::queue_image( $file_path );
            Avif4WP_AutoConverterHelper::process_batch();

            return $upload;
        }

        if ( in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
            $converted = $this->converter->convert( $file_path );
        }

        return $upload;
    }

	public function update_attachment_metadata( $metadata, $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );
		
		if ( ! preg_match( '/\.(jpg|jpeg|png)$/i', $file_path ) ) {
			return $metadata;
		}
		
		$output_format = get_option( 'avif_output_format', 'avif' );
		$upload_dir    = wp_upload_dir();

		$converted_file = preg_replace( '/\.(jpg|jpeg|png)$/i', '.' . $output_format, $file_path );

		if ( ! file_exists( $converted_file ) ) {
			$converted = $this->converter->convert( $file_path );
			if ( $converted && file_exists( $converted ) ) {
				$converted_file = $converted;
			} else {
				return $metadata;
			}
		}

		remove_filter( 'wp_generate_attachment_metadata', array( $this, 'update_attachment_metadata' ), 10 );
		$new_metadata = wp_generate_attachment_metadata( $attachment_id, $converted_file );
		add_filter( 'wp_generate_attachment_metadata', array( $this, 'update_attachment_metadata' ), 10, 2 );

		if ( ! empty( $new_metadata['sizes'] ) && is_array( $new_metadata['sizes'] ) ) {
			foreach ( $new_metadata['sizes'] as $size_key => &$size_data ) {
				if ( ! empty( $size_data['file'] ) ) {
					$original_size_path = trailingslashit( $upload_dir['basedir'] ) . $size_data['file'];
					if ( strtolower( pathinfo( $original_size_path, PATHINFO_EXTENSION ) ) === strtolower( $output_format ) ) {
						continue;
					}
					$converted_size_path = preg_replace( '/\.(jpg|jpeg|png)$/i', '.' . $output_format, $original_size_path );

					if ( ! file_exists( $converted_size_path ) ) {
						$converted_sub = $this->converter->convert( $original_size_path );
						if ( $converted_sub && file_exists( $converted_sub ) ) {
							$converted_size_path = $converted_sub;
						}
					}

					$size_data['file'] = basename( $converted_size_path );
				}
			}
		}

		if ( empty( $new_metadata['sizes'] ) || ! isset( $new_metadata['sizes']['thumbnail'] ) ) {
			$new_metadata['sizes']['thumbnail'] = array(
				'file'      => basename( $converted_file ),
				'width'     => $new_metadata['width'],
				'height'    => $new_metadata['height'],
				'mime-type' => 'image/' . $output_format,
				'filesize'  => file_exists( $converted_file ) ? filesize( $converted_file ) : 0,
			);
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

	private function get_running_conversions() {
		$running = get_transient( 'avif_running_conversions' );
		if ( false === $running ) {
			$running = 0;
			set_transient( 'avif_running_conversions', $running, 3600 );
		}
		return (int) $running;
	}

	private function queue_conversion( $file_path ) {
		$queue = get_transient( 'avif_conversion_queue' );
		if ( false === $queue ) {
			$queue = array();
		}
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
		$cache_key = 'avif_metadata_' . $attachment_id;
		$metadata = wp_cache_get( $cache_key );
		if ( ! $metadata ) {
			$metadata = wp_get_attachment_metadata( $attachment_id );
			wp_cache_set( $cache_key, $metadata );
		}
		return $metadata;
	}

	private function batch_process_files( $files, $batch_size = 10 ) {
		$chunks = array_chunk( $files, $batch_size );
		foreach ( $chunks as $batch ) {
			foreach ( $batch as $file ) {
                $this->converter->convert( $file );
			}
		}
	}

	private function maybe_delete_original_and_scaled( $file_path ) {
		if ( 'yes' === get_option( 'avif_delete_original', 'no' ) && file_exists( $file_path ) ) {
			@unlink( $file_path );
			if ( false !== strpos( $file_path, '-scaled' ) ) {
				$this->delete_non_scaled_file( $file_path );
			}
		}
	}

	private function delete_non_scaled_file( $scaled_file_path ) {
		$non_scaled = str_replace( '-scaled', '', $scaled_file_path );
		if ( $non_scaled !== $scaled_file_path && file_exists( $non_scaled ) ) {
			@unlink( $non_scaled );
		}
	}

	public function redirect_to_converted( $url, $post_id ) {
		$file_path = get_attached_file( $post_id );
		$avif_file = preg_replace( '/\.(jpg|jpeg|png)$/i', '.avif', $file_path );
		$webp_file = preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $file_path );
		
		if (file_exists($avif_file)) {
			return preg_replace( '/\.(jpg|jpeg|png)$/i', '.avif', $url );
		} else if (file_exists($webp_file)) {
			return preg_replace( '/\.(jpg|jpeg|png)$/i', '.webp', $url );
		}
		
		return $url;
	}

	public function process_editor_images( $content ) {
		$upload_dir    = wp_upload_dir();
		$output_format = get_option( 'avif_output_format', 'avif' );

		$content = preg_replace_callback(
			'/<img\s+([^>]*?)src\s*=\s*[\'"]([^\'"]+\.(?:jpg|jpeg|png))[\'"]([^>]*)>/i',
			function( $matches ) use ( $upload_dir, $output_format ) {
				$img_url = $matches[2];
				if ( false === strpos( $img_url, $upload_dir['baseurl'] ) ) {
					return $matches[0];
				}
				$relative_path = str_replace( trailingslashit( $upload_dir['baseurl'] ), '', $img_url );
				$file_path     = trailingslashit( $upload_dir['basedir'] ) . $relative_path;

				if ( file_exists( $file_path ) ) {
					$converted_file = preg_replace( '/\.(jpg|jpeg|png)$/i', '.' . $output_format, $file_path );
					if ( ! file_exists( $converted_file ) ) {
						$converted = $this->converter->convert( $file_path );
						if ( $converted && file_exists( $converted ) ) {
							$converted_file = $converted;
						} else {
							return $matches[0];
						}
					}

					$new_url = trailingslashit( $upload_dir['baseurl'] ) .
						str_replace( trailingslashit( $upload_dir['basedir'] ), '', $converted_file );

					$enhanced_responsive = ( 'yes' === get_option( 'avif_enhanced_responsive', 'no' ) );
					if ( $enhanced_responsive ) {
						$srcset = esc_url( $new_url ) . ' 1x, ' . esc_url( $new_url ) . ' 2x';
						return '<img ' . trim( $matches[1] ) . ' src="' . esc_url( $new_url ) . '" srcset="' . esc_attr( $srcset ) . '" ' . $matches[3] . '>';
					}
					return '<img ' . trim( $matches[1] ) . ' src="' . esc_url( $new_url ) . '" ' . $matches[3] . '>';
				}
				return $matches[0];
			},
			$content
		);

		return $content;
	}

	private function cleanup_attachment( $attachment_id ) {
		$this->delete_scaled_files( $attachment_id );
	}

	private function delete_scaled_files( $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );
		if ( ! $file_path || ! file_exists( $file_path ) ) {
			return;
		}
		$pathinfo  = pathinfo( $file_path );
		$directory = $pathinfo['dirname'];
		$filename  = $pathinfo['filename'];
		if ( is_dir( $directory ) ) {
			$files = scandir( $directory );
			if ( $files ) {
				foreach ( $files as $f ) {
					if ( strpos( $f, $filename . '-scaled' ) === 0 ) {
						$ext = strtolower( pathinfo( $f, PATHINFO_EXTENSION ) );
						if ( in_array( $ext, array( 'jpg', 'jpeg', 'png' ), true ) ) {
							$file_to_delete = trailingslashit( $directory ) . $f;
							if ( file_exists( $file_to_delete ) ) {
								@unlink( $file_to_delete );
							}
						}
					}
				}
			}
		}
	}

	private function force_cleanup_uploads() {
		$upload_dir = wp_upload_dir();
		$base_dir   = trailingslashit( $upload_dir['basedir'] );
		$this->recursive_cleanup( $base_dir );
	}

	private function recursive_cleanup( $directory ) {
		$items = scandir( $directory );
		if ( ! $items ) {
			return;
		}
		foreach ( $items as $item ) {
			if ( $item === '.' || $item === '..' ) {
				continue;
			}
			$full_path = trailingslashit( $directory ) . $item;
			if ( is_dir( $full_path ) ) {
				$this->recursive_cleanup( $full_path );
			} else {
				$extension = strtolower( pathinfo( $full_path, PATHINFO_EXTENSION ) );
				if ( ! in_array( $extension, array( 'jpg', 'jpeg', 'png' ), true ) ) {
					continue;
				}
				if ( strpos( $item, '-scaled' ) !== false ) {
					if ( @unlink( $full_path ) ) {
					}
					continue;
				}
				if ( preg_match( '/-\d+x\d+\.(jpg|jpeg|png)$/i', $item ) ) {
					if ( @unlink( $full_path ) ) {
					}
				}
			}
		}
	}

	private function log( $message ) {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( '[avif4wp] ' . $message );
		}
	}
}
?>