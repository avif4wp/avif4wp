<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_AutoConvert_Timeout {
    public static function apply_timeout( $batch ) {
        if ( ! is_array( $batch ) || empty( $batch ) ) {
            return;
        }

        $total_size_mb = 0;
        foreach ( $batch as $file_path ) {
            if ( file_exists( $file_path ) ) {
                $size_bytes = filesize( $file_path );
                $total_size_mb += $size_bytes / ( 1024 * 1024 );
            }
        }

        $file_count = count( $batch );
        $timeout = self::calculate_timeout( $total_size_mb, $file_count );

        $php_limit = (int) ini_get( 'max_execution_time' );
        if ( $php_limit > 0 && $timeout < $php_limit ) {
            $timeout = $php_limit;
        }

        @set_time_limit( $timeout );

        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( "[avif4wp][Timeout] Set timeout to {$timeout} seconds for {$file_count} file(s), {$total_size_mb} MB total." );
        }
    }

    protected static function calculate_timeout( $total_mb, $count ) {
        $base = 3600;
        $extra_by_size = floor( $total_mb / 50 ) * 500;
        $extra_by_count = max( 0, $count - 1 ) * 250;

        return $base + $extra_by_size + $extra_by_count;
    }
}