<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class avif4wp_mediarow {

    public function __construct() {
        add_filter( 'manage_media_columns', array( $this, 'add_custom_media_columns' ) );
        add_action( 'manage_media_custom_column', array( $this, 'render_custom_media_columns' ), 10, 2 );
    }

    public function add_custom_media_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $value ) {
            $new_columns[ $key ] = $value;
            if ( 'title' === $key ) {
                $new_columns['converted_avif'] = __( 'AVIF', 'avif4wp' );
                $new_columns['converted_webp'] = __( 'WebP', 'avif4wp' );
            }
        }
        return $new_columns;
    }
    
    public function render_custom_media_columns( $column_name, $post_id ) {
        if ( 'converted_avif' === $column_name ) {
            $converted_avif = get_post_meta( $post_id, 'converted_avif', true );
            if ( $converted_avif ) {
                $upload_dir = wp_upload_dir();
                $file_url   = trailingslashit( $upload_dir['baseurl'] ) . $converted_avif;
                echo '<a href="' . esc_url( $file_url ) . '" target="_blank">' . __( 'View AVIF', 'avif4wp' ) . '</a>';
            } else {
                echo __( 'None', 'avif4wp' );
            }
        }
        if ( 'converted_webp' === $column_name ) {
            $converted_webp = get_post_meta( $post_id, 'converted_webp', true );
            if ( $converted_webp ) {
                $upload_dir = wp_upload_dir();
                $file_url   = trailingslashit( $upload_dir['baseurl'] ) . $converted_webp;
                echo '<a href="' . esc_url( $file_url ) . '" target="_blank">' . __( 'View WebP', 'avif4wp' ) . '</a>';
            } else {
                echo __( 'None', 'avif4wp' );
            }
        }
    }
}