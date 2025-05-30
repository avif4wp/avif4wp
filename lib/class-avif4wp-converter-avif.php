<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Avif4WP_ConverterAVIF {

	public function convert( $file_path ) {
		return $this->convert_to_avif_imagick( $file_path );
	}

	private function convert_to_avif_imagick( $file_path ) {
		$quality = 60;

		if ( ! extension_loaded( 'imagick' ) || ! class_exists( 'Imagick' ) ) {
			return false;
		}

		try {
			$imagick = new Imagick( $file_path );
			$imagick->setImageFormat( 'avif' );
			$imagick->setImageCompressionQuality( $quality );
			$converted_file = preg_replace( '/\.(jpe?g|png)$/i', '.avif', $file_path );
			$imagick->writeImage( $converted_file );
			$imagick->destroy();

			if ( file_exists( $converted_file ) ) {
				$this->update_analytics( $file_path, $converted_file );
				return $converted_file;
			}
		} catch ( Exception $e ) {}

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

		$conversion_logs   = get_option( 'avif4wp_conversion_logs', array() );
		$conversion_logs[] = array(
			'date'           => current_time( 'mysql' ),
			'image_name'     => basename( $original_file ),
			'original_size'  => $original_size,
			'converted_size' => $converted_size,
			'quality'        => 60,
		);
		update_option( 'avif4wp_conversion_logs', $conversion_logs );
	}

	public function redirect_to_converted( $url, $post_id ) {
		$file_path = get_attached_file( $post_id );
		if ( false !== strpos( $file_path, '-scaled' ) ) {
			return $url;
		}

		$converted = preg_replace( '/\.(jpe?g|png)$/i', '.avif', $file_path );
		if ( file_exists( $converted ) ) {
			return preg_replace( '/\.(jpe?g|png)$/i', '.avif', $url );
		}

		return $url;
	}
}