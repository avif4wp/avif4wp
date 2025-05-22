<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Avif4WP_Changelog {
	public static function get_changelog() {
		$changelog_file = dirname( dirname( __FILE__ ) ) . '/changelog.json';

		if ( ! file_exists( $changelog_file ) ) {
			return '<p>' . esc_html__( 'Changelog belum tersedia.', 'avif4wp' ) . '</p>';
		}

		$json_content = file_get_contents( $changelog_file );
		$data = json_decode( $json_content, true );

		if ( empty( $data ) ) {
			return '<p>' . esc_html__( 'Changelog tidak dapat dibaca.', 'avif4wp' ) . '</p>';
		}

		$html = '';
		if ( isset( $data['title'] ) ) {
			$html .= $data['title'];
		}
		if ( isset( $data['description'] ) ) {
			$html .= $data['description'];
		}
		if ( isset( $data['changelog'] ) ) {
			$html .= $data['changelog'];
		}
		if ( isset( $data['note'] ) ) {
			$html .= $data['note'];
		}

		return $html;
	}

	public static function render() {
		echo self::get_changelog();
	}
}