<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP_ConvertHelper {

    public static function cleanup_srcset_files() {
        $upload_dir = wp_upload_dir();
        $base_dir   = trailingslashit( $upload_dir['basedir'] );

        if ( ! is_dir( $base_dir ) ) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator( $base_dir, RecursiveDirectoryIterator::SKIP_DOTS )
        );

        foreach ( $iterator as $file ) {
            if ( ! $file->isFile() ) {
                continue;
            }

            $filename  = $file->getFilename();
            $filepath  = $file->getPathname();
            $extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

            if ( ! in_array( $extension, array( 'jpg', 'jpeg', 'png' ), true ) ) {
                continue;
            }

            if ( false !== strpos( $filename, '-scaled' ) ) {
                continue;
            }

            if ( preg_match( '/-\d+x\d+\.(jpg|jpeg|png)$/i', $filename ) ) {
                @unlink( $filepath );
            }
        }
    }
}