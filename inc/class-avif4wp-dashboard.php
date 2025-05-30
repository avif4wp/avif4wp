<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Avif4WP_Dashboard {

	public static function render_dashboard() {
		wp_enqueue_style( 'avif4wp-dashboard-style', plugin_dir_url( __FILE__ ) . 'dashboard-style.css' );

		$space_saved      = get_option( 'avif4wp_space_saved', 0 );
		$images_converted = get_option( 'avif4wp_images_converted', 0 );
		$output_format    = get_option( 'avif_output_format', 'avif' );

		$timestamp   = current_time( 'timestamp' );
		$server_time = date_i18n( 'H:i d/m/Y', $timestamp );
		echo '<p>' . sprintf( esc_html__( 'Waktu server saat ini: %s', 'avif4wp' ), $server_time ) . '</p>';

		$imagickSupported = false;
		if ( extension_loaded( 'imagick' ) && class_exists( 'Imagick' ) ) {
			$formats = Imagick::queryFormats();
			if ( in_array( 'AVIF', $formats ) ) {
				$imagickSupported = true;
			}
		}

		$gmagickSupported = false;
		if ( extension_loaded( 'gmagick' ) && class_exists( 'Gmagick' ) ) {
			$gmOutput = shell_exec( 'gm convert -list format 2>&1' );
			if ( strpos( $gmOutput, 'AVIF' ) !== false ) {
				$gmagickSupported = true;
			}
		}

		$ffmpegSupported = false;
		$ffmpegOutput    = shell_exec( 'ffmpeg -encoders 2>&1' );
		if ( strpos( $ffmpegOutput, 'libaom-av1' ) !== false ) {
			$ffmpegSupported = true;
		}

		$libavifSupported = false;
		$libavifOutput    = shell_exec( 'avifenc --version 2>&1' );
		if ( strpos( $libavifOutput, 'avifenc' ) !== false ) {
			$libavifSupported = true;
		}

		$phpImageavifSupported = function_exists( 'imageavif' );

		$countLibrary = 0;
		if ( $imagickSupported ) $countLibrary++;
		if ( $gmagickSupported ) $countLibrary++;
		if ( $ffmpegSupported ) $countLibrary++;
		if ( $libavifSupported ) $countLibrary++;
		if ( $phpImageavifSupported ) $countLibrary++;

		$gdSupported = false;
		if ( function_exists( 'gd_info' ) ) {
			$gd_info     = gd_info();
			$gdSupported = ( isset( $gd_info['WebP Support'] ) && $gd_info['WebP Support'] );
		}

		$status      = 'Abnormal';
		$statusClass = 'red';
		if ( $gdSupported && $countLibrary >= 2 ) {
			$status      = 'Normal';
			$statusClass = 'normal';
		} elseif ( $gdSupported && $countLibrary === 1 ) {
			$status      = 'Abnormal';
			$statusClass = 'yellow';
		}

		$conversion_logs = get_option( 'avif4wp_conversion_logs', array() );
		if ( ! empty( $conversion_logs ) ) {
			$conversion_logs = array_reverse( $conversion_logs );
			$uniqueLogs      = array();
			$seen            = array();

			foreach ( $conversion_logs as $log ) {
				$fileKey = $log['image_name'];
				if ( ! isset( $seen[ $fileKey ] ) ) {
					$uniqueLogs[]     = $log;
					$seen[ $fileKey ] = true;
				}
			}

			$uniqueLogs      = array_slice( $uniqueLogs, 0, 10 );
			$conversion_logs = $uniqueLogs;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Dasbor', 'avif4wp' ); ?></h1>

			<div class="avif4wp-analytics-cards">
				<div class="avif4wp-card">
					<h2><?php esc_html_e( 'Total Ruang Dihemat', 'avif4wp' ); ?></h2>
					<p><?php echo size_format( $space_saved ); ?></p>
				</div>
				<div class="avif4wp-card">
					<h2><?php esc_html_e( 'Total Gambar Dikoversi', 'avif4wp' ); ?></h2>
					<p><?php echo intval( $images_converted ); ?></p>
				</div>
				<div class="avif4wp-card">
					<h2><?php esc_html_e( 'Status Plugin', 'avif4wp' ); ?></h2>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=avif4wp&tab=system_info' ) ); ?>" class="avif4wp-status <?php echo esc_attr( $statusClass ); ?>">
						<?php echo esc_html( $status ); ?>
					</a>
				</div>
			</div>

			<h2><?php esc_html_e( 'Konversi Terakhir', 'avif4wp' ); ?></h2>
			<table class="widefat fixed avif4wp-conversion-table" cellspacing="0">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tanggal', 'avif4wp' ); ?></th>
						<th><?php esc_html_e( 'Nama File', 'avif4wp' ); ?></th>
						<th><?php esc_html_e( 'Ukuran Sebelum', 'avif4wp' ); ?></th>
						<th><?php esc_html_e( 'Ukuran Sesudah', 'avif4wp' ); ?></th>
						<th><?php esc_html_e( 'Selisih', 'avif4wp' ); ?></th>
						<th><?php esc_html_e( 'Output', 'avif4wp' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if ( ! empty( $conversion_logs ) ) : ?>
						<?php foreach ( $conversion_logs as $log ) : ?>
							<tr>
								<td><?php echo esc_html( $log['date'] ); ?></td>
								<td><?php echo esc_html( $log['image_name'] ); ?></td>
								<td><?php echo size_format( $log['original_size'] ); ?></td>
								<td><?php echo size_format( $log['converted_size'] ); ?></td>
								<td>
									<?php
									$diff = $log['original_size'] - $log['converted_size'];
									echo size_format( max( 0, $diff ) );
									?>
								</td>
								<td><?php echo esc_html( strtoupper( $log['output'] ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr>
							<td colspan="6"><?php esc_html_e( 'Belum ada konversi', 'avif4wp' ); ?></td>
						</tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
		<?php
	}
}