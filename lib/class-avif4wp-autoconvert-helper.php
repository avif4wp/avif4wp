<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_AutoConverterHelper {

    const TRANSIENT_KEY = 'avif_auto_convert_queue';
    const BATCH_SIZE    = 3;

    public static function queue_image( $file_path ) {
        $queue = get_transient( self::TRANSIENT_KEY );
        if ( ! is_array( $queue ) ) {
            $queue = array();
        }

        if ( ! in_array( $file_path, $queue, true ) ) {
            $queue[] = $file_path;
            set_transient( self::TRANSIENT_KEY, $queue, HOUR_IN_SECONDS );
        }
    }

    public static function process_batch() {
        $queue = get_transient( self::TRANSIENT_KEY );
        if ( empty( $queue ) || ! is_array( $queue ) ) {
            return;
        }

        $batch = array_splice( $queue, 0, self::BATCH_SIZE );
        set_transient( self::TRANSIENT_KEY, $queue, HOUR_IN_SECONDS );

        $converter = self::get_converter_instance();
        foreach ( $batch as $file_path ) {
            if ( file_exists( $file_path ) ) {
                $converted = $converter->convert( $file_path );
            }
        }
    }

    protected static function get_converter_instance() {
        $format = get_option( 'avif_output_format', 'avif' );

        if ( 'webp' === $format ) {
            require_once __DIR__ . '/class-avif4wp-converter-webp.php';
            return new Avif4WP_ConverterWebP();
        }

        require_once __DIR__ . '/class-avif4wp-converter-avif.php';
        return new Avif4WP_ConverterAVIF();
    }
}