<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_AutoConvert_Timeout {
    
    public static function apply_timeout( $batch = array() ) {
        if ( ! is_array( $batch ) || empty( $batch ) ) {
            $timeout = (int) apply_filters( 'avif4wp_autoconvert_timeout', ini_get( 'max_execution_time' ) );
            @set_time_limit( $timeout );
            return;
        }

        $total_size_mb = 0;
        foreach ( $batch as $file_path ) {
            if ( file_exists( $file_path ) ) {
                $total_size_mb += filesize( $file_path ) / ( 1024 * 1024 );
            }
        }
        $file_count = count( $batch );
        $timeout = self::calculate_timeout( $total_size_mb, $file_count );

        $php_limit = (int) ini_get( 'max_execution_time' );
        if ( $php_limit > 0 && $timeout < $php_limit ) {
            $timeout = $php_limit;
        }
        @set_time_limit( $timeout );
    }

    protected static function calculate_timeout( $total_mb, $count ) {
        $base              = 3600;
        $extra_by_size     = floor( $total_mb / 50 ) * 500;
        $extra_by_count    = max( 0, $count - 1 ) * 250;

        return $base + $extra_by_size + $extra_by_count;
    }
}