<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Avif4WP_About {

	public static function render() {
		$allowed_tags = array(
			'br' => array(),
		);
		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'Tentang AVIF4WP', 'avif4wp' ); ?></h2>
			<p>
				<?php
				$text = __( 'AVIF4WP adalah plugin konversi dan kompresi gambar yang ringan dan sederhana untuk situs WordPress.<br>Pugin ini memungkinkan Anda mengonversi gambar JPG dan PNG ke format AVIF atau WebP secara otomatis dan langsung di server,<br>menghasilkan gambar berkualitas tinggi dengan ukuran file yang lebih efisien.<br>Selain itu, plugin ini juga memperbarui metadata attachment dan menghasilkan thumbnail dari file hasil konversi.', 'avif4wp' );
				echo wp_kses( $text, $allowed_tags );
				?>
			</p>
			<br>

			<h2><?php esc_html_e( 'Tentang AVIF', 'avif4wp' ); ?></h2>
			<p>
				<?php
				$text = __( 'AVIF (AV1 Image File Format) adalah format gambar modern yang menawarkan kompresi lebih baik dan kualitas gambar<br>yang superior dibandingkan format tradisional seperti JPG dan PNG. AVIF dapat mengurangi ukuran file secara signifikan<br>tanpa mengorbankan kualitas gambar.', 'avif4wp' );
				echo wp_kses( $text, $allowed_tags );
				?>
			</p>
			<br>

			<h2><?php esc_html_e( 'Tentang WebP', 'avif4wp' ); ?></h2>
			<p>
				<?php
				$text = __( 'WebP adalah format gambar yang dikembangkan oleh Google, menyediakan metode kompresi gambar yang efisien dengan kualitas tinggi.<br>Format ini mendukung kompresi lossy dan lossless, sehingga ideal untuk mengoptimalkan kecepatan dan performa situs web.', 'avif4wp' );
				echo wp_kses( $text, $allowed_tags );
				?>
			</p>
		</div>
		<?php
	}
}