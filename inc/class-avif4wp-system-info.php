<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Avif4WP_System_Info {
	public static function render() {
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Info Sistem', 'avif4wp' ); ?></h2>
			<p class="description"><?php esc_html_e( 'Informasi server dan dukungan library untuk memastikan lingkungan mendukung konversi ke AVIF atau WebP.', 'avif4wp' ); ?></p>
		</div>
		<?php

		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$plugin_data    = get_plugin_data( AVIF4WP_MAIN_FILE );
		$plugin_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '1.0.0';

		$os         = php_uname( 's' );
		$os_version = php_uname( 'r' );
		if ( stristr( PHP_OS, 'Linux' ) ) {
			if ( file_exists( '/etc/os-release' ) ) {
				$os_data    = parse_ini_file( '/etc/os-release' );
				$os         = isset( $os_data['NAME'] ) ? $os_data['NAME'] : 'Unknown Linux';
				$os_version = isset( $os_data['VERSION'] ) ? $os_data['VERSION'] : php_uname( 'r' );
			} else {
				$os = 'Linux';
			}
		} elseif ( stristr( PHP_OS, 'BSD' ) ) {
			if ( stristr( $os, 'FreeBSD' ) ) {
				$os = 'FreeBSD';
			} elseif ( stristr( $os, 'OpenBSD' ) ) {
				$os = 'OpenBSD';
			} elseif ( stristr( $os, 'NetBSD' ) ) {
				$os = 'NetBSD';
			} elseif ( stristr( $os, 'DragonFlyBSD' ) ) {
				$os = 'DragonFlyBSD';
			} else {
				$os = 'BSD Family';
			}
		} elseif ( stristr( PHP_OS, 'WINNT' ) ) {
			$os         = 'Windows Server';
			$os_version = php_uname( 'r' );
		}

		$web_server = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : __( 'Server Web Tidak Dikenal', 'avif4wp' );
		$php_fpm    = ( false !== strpos( php_sapi_name(), 'fpm-fcgi' ) ) ? __( 'Dimuat', 'avif4wp' ) : __( 'Tidak Dimuat', 'avif4wp' );

		$avif_engine            = 'Tidak Ada';
		$avif_support_imageavif = function_exists( 'imageavif' ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );

		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			$imagick_version = Imagick::getVersion()['versionString'];
			$imagick_formats = Imagick::queryFormats();
			$imagick_avif    = in_array( 'AVIF', $imagick_formats ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );
			if ( 'Didukung' === $imagick_avif ) {
				$avif_engine = 'Imagick';
			}
		} else {
			$imagick_version = __( 'Tidak Dimuat', 'avif4wp' );
			$imagick_avif    = __( 'Tidak Didukung', 'avif4wp' );
		}

		if ( extension_loaded( 'gmagick' ) && class_exists( 'Gmagick' ) ) {
			$gmagick_status = __( 'Dimuat', 'avif4wp' );
			$gmagick_avif   = ( strpos( shell_exec( 'gm convert -list format 2>&1' ), 'AVIF' ) !== false ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );
			if ( 'Didukung' === $gmagick_avif && 'Tidak Ada' === $avif_engine ) {
				$avif_engine = 'Gmagick';
			}
		} else {
			$gmagick_status = __( 'Tidak Dimuat', 'avif4wp' );
			$gmagick_avif   = __( 'Tidak Didukung', 'avif4wp' );
		}

		if ( 'Didukung' === $avif_support_imageavif && 'Tidak Ada' === $avif_engine ) {
			$avif_engine = 'PHP imageavif()';
		}

		$ffmpeg_avif = ( strpos( shell_exec( 'ffmpeg -encoders 2>&1' ), 'libaom-av1' ) !== false ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );
		if ( 'Didukung' === $ffmpeg_avif && 'Tidak Ada' === $avif_engine ) {
			$avif_engine = 'FFmpeg';
		}		

		$libavif_avif = ( strpos( shell_exec( 'avifenc --version 2>&1' ), 'avifenc' ) !== false ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );
		if ( 'Didukung' === $libavif_avif && 'Tidak Ada' === $avif_engine ) {
			$avif_engine = 'libavif';
		}

		$libheif_support = ( strpos( shell_exec( 'heif-convert --version 2>&1' ), 'heif-convert' ) !== false ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );
		if ( 'Didukung' === $libheif_support && 'Tidak Ada' === $avif_engine ) {
			$avif_engine = 'libheif';
		}

		if ( function_exists( 'gd_info' ) ) {
			$gd_loaded     = __( 'Didukung', 'avif4wp' );
			$gd_info       = gd_info();
			$gd_webp_support = ( isset( $gd_info['WebP Support'] ) && $gd_info['WebP Support'] ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );
		} else {
			$gd_loaded     = __( 'Tidak Didukung', 'avif4wp' );
			$gd_webp_support = __( 'GD Tidak Dimuat', 'avif4wp' );
		}

		$avif_format_support = ( 'Tidak Ada' !== $avif_engine ) ? __( 'Didukung', 'avif4wp' ) : __( 'Tidak Didukung', 'avif4wp' );
		?>
		<table class="form-table">
			<tr>
				<th colspan="2" style="background:#f1f1f1;"><?php esc_html_e( 'Informasi Umum', 'avif4wp' ); ?></th>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Versi Plugin', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $plugin_version ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Library Aktif', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $avif_engine ); ?></td>
			</tr>

			<tr>
				<th colspan="2" style="background:#f1f1f1;"><?php esc_html_e( 'Informasi Server', 'avif4wp' ); ?></th>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Sistem Operasi', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $os . ' ' . $os_version ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Web Server', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $web_server ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Versi PHP', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( PHP_VERSION ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'PHP-FPM', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $php_fpm ); ?></td>
			</tr>

			<tr>
				<th colspan="2" style="background:#f1f1f1;"><?php esc_html_e( 'Format Output', 'avif4wp' ); ?></th>
			</tr>
			<tr>
				<th><?php esc_html_e( 'AVIF', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $avif_format_support ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'WebP', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $gd_webp_support ); ?></td>
			</tr>

			<tr>
				<th colspan="2" style="background:#f1f1f1;"><?php esc_html_e( 'Library', 'avif4wp' ); ?></th>
			</tr>
			<tr>
				<th><?php esc_html_e( 'imageavif', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $avif_support_imageavif ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Imagick/ImageMagick', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $imagick_avif . ' (' . $imagick_version . ')' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'Gmagick', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $gmagick_avif . ' (' . $gmagick_status . ')' ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'FFmpeg', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $ffmpeg_avif ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'libavif', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $libavif_avif ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'libheif', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $libheif_support ); ?></td>
			</tr>
			<tr>
				<th><?php esc_html_e( 'GD', 'avif4wp' ); ?></th>
				<td><?php echo esc_html( $gd_loaded ); ?></td>
			</tr>
		</table>
		<?php
	}
}