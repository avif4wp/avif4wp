<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Avif4WP_Settings {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	public function enqueue_admin_scripts( $hook ) {
		if ( 'toplevel_page_avif4wp' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(document).ready(function($){ $(".avif-color-picker").wpColorPicker(); });'
		);
	}

	public function register_settings() {
		register_setting(
			'avif4wp_settings_group',
			'avif_delete_original',
			array(
				'sanitize_callback' => function( $input ) {
					return ( 'yes' === $input ) ? 'yes' : 'no';
				},
			)
		);
		register_setting(
			'avif4wp_settings_group',
			'avif_placeholder_color',
			array(
				'sanitize_callback' => 'sanitize_hex_color',
			)
		);
		add_settings_section(
			'avif4wp_general_settings',
			__( 'Pengaturan Umum', 'avif4wp' ),
			null,
			'avif4wp-settings'
		);
		add_settings_field(
			'avif_delete_original',
			__( 'Hapus Gambar Asli', 'avif4wp' ),
			array( $this, 'field_delete_original' ),
			'avif4wp-settings',
			'avif4wp_general_settings'
		);
		add_settings_field(
			'avif_placeholder_color',
			__( 'Warna Placeholder', 'avif4wp' ),
			array( $this, 'field_placeholder_color' ),
			'avif4wp-settings',
			'avif4wp_general_settings'
		);
	}

	public function field_delete_original() {
		$value = get_option( 'avif_delete_original', 'no' );
		echo '<label>';
		echo '<input type="checkbox" name="avif_delete_original" value="yes" ' . checked( $value, 'yes', false ) . '/> ';
		echo esc_html__( 'Hapus file JPG/PNG asli setelah konversi ke AVIF', 'avif4wp' );
		echo '</label>';
		echo '<p class="description">'
			. esc_html__( 'Centang untuk secara otomatis menghapus file asli setelah berhasil dikonversi.', 'avif4wp' )
			. '</p>';
	}

	public function field_placeholder_color() {
		$value = get_option( 'avif_placeholder_color', '#ffffff' );
		printf(
			'<input type="text" name="avif_placeholder_color" value="%1$s" class="avif-color-picker" data-default-color="#ffffff" />',
			esc_attr( $value )
		);
		echo '<p class="description">'
			. esc_html__( 'Pilih warna latar untuk placeholder (saat gambar sedang dimuat).', 'avif4wp' )
			. '</p>';
	}
}