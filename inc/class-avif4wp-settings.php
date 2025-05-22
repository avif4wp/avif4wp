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
		wp_add_inline_script( 'wp-color-picker', 'jQuery(document).ready(function($){ $(".avif-color-picker").wpColorPicker(); });' );
	}

	public function add_settings_page() {
	}

	public function register_settings() {
		register_setting( 'avif4wp_settings_group', 'avif_quality', array(
			'sanitize_callback' => 'sanitize_text_field'
		) );
		register_setting( 'avif4wp_settings_group', 'avif_delete_original', array(
			'sanitize_callback' => function( $input ) {
				return ( 'yes' === $input ) ? 'yes' : 'no';
			}
		) );
		register_setting( 'avif4wp_settings_group', 'avif_output_format', array(
			'sanitize_callback' => 'sanitize_text_field'
		) );
		register_setting( 'avif4wp_settings_group', 'avif_placeholder_color', array(
			'sanitize_callback' => 'sanitize_hex_color'
		) );
		register_setting( 'avif4wp_settings_group', 'avif_picturefill', array(
			'sanitize_callback' => function( $input ) {
				return ( 'yes' === $input ) ? 'yes' : 'no';
			}
		) );

		add_settings_section(
			'avif4wp_general_settings',
			__( 'Pengaturan Umum', 'avif4wp' ),
			null,
			'avif4wp-settings'
		);
	
		add_settings_field(
			'avif_quality',
			__( 'Kualitas Gambar', 'avif4wp' ),
			array( $this, 'field_quality' ),
			'avif4wp-settings',
			'avif4wp_general_settings'
		);
	
		add_settings_field(
			'avif_delete_original',
			__( 'Hapus Gambar Asli', 'avif4wp' ),
			array( $this, 'field_delete_original' ),
			'avif4wp-settings',
			'avif4wp_general_settings'
		);
	
		add_settings_field(
			'avif_output_format',
			__( 'Format Output', 'avif4wp' ),
			array( $this, 'field_output_format' ),
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
		
		add_settings_field(
			'avif_picturefill',
			__( 'Picturefill', 'avif4wp' ),
			array( $this, 'field_picturefill' ),
			'avif4wp-settings',
			'avif4wp_general_settings'
		);
	}

	public function field_quality() {
		$value = get_option( 'avif_quality', 'sedang' );
		?>
		<select name="avif_quality">
			<option value="rendah" <?php selected( $value, 'rendah' ); ?>><?php esc_html_e( 'Rendah', 'avif4wp' ); ?></option>
			<option value="sedang" <?php selected( $value, 'sedang' ); ?>><?php esc_html_e( 'Sedang', 'avif4wp' ); ?></option>
			<option value="tinggi" <?php selected( $value, 'tinggi' ); ?>><?php esc_html_e( 'Tinggi', 'avif4wp' ); ?></option>
		</select>
		<p class="description">
			<?php 
				echo wp_kses( __( 'Pilih kualitas gambar sesuai kebutuhan. "Rendah" menghasilkan file lebih kecil dengan sedikit reduksi warna dan detail. <br /> "Sedang" menawarkan keseimbangan optimal. "Tinggi" meningkatkan ketajaman dengan kualitas lebih baik, tetapi ukuran file lebih besar.', 'avif4wp' ), array( 'br' => array() ) );
			?>
			<br>
			<em><?php echo wp_kses( __( 'Catatan: Pengaturan kualitas gambar saat ini hanya berfungsi untuk format WebP karena keterbatasan dukungan library untuk AVIF.', 'avif4wp' ), array( 'br' => array() ) ); ?></em>
		</p>
		<?php
	}
	
	public function field_delete_original() {
		$value = get_option( 'avif_delete_original', 'no' );
		echo '<input type="checkbox" name="avif_delete_original" value="yes" ' . checked( $value, 'yes', false ) . ' />';
		echo '<p class="description">' . esc_html__( 'Centang untuk menghapus gambar asli (JPG/PNG) setelah konversi. Pastikan Anda memiliki backup jika diperlukan.', 'avif4wp' ) . '</p>';
	}
	
	public function field_output_format() {
		$value = get_option( 'avif_output_format', 'avif' );
		echo '<select name="avif_output_format">';
		echo '<option value="avif" ' . selected( $value, 'avif', false ) . '>' . esc_html__( 'AVIF', 'avif4wp' ) . '</option>';
		echo '<option value="webp" ' . selected( $value, 'webp', false ) . '>' . esc_html__( 'WebP', 'avif4wp' ) . '</option>';
		echo '</select>';
		echo '<p class="description">' . wp_kses( __( 'Silakan pilih format output gambar. AVIF adalah format modern dengan kompresi unggul. WebP didukung secara luas oleh berbagai platform.<br>Pilih sesuai kebutuhan Anda.', 'avif4wp' ), array( 'br' => array() ) ) . '</p>';
	}
	
	public function field_placeholder_color() {
		$value = get_option( 'avif_placeholder_color', '#ffffff' );
		echo '<input type="text" name="avif_placeholder_color" value="' . esc_attr( $value ) . '" class="avif-color-picker" data-default-color="#ffffff" />';
		echo '<p class="description">' . esc_html__( 'Pilih warna untuk area placeholder gambar saat gambar sedang dimuat.', 'avif4wp' ) . '</p>';
	}
	
	public function field_picturefill() {
		$value = get_option( 'avif_picturefill', 'yes' );
		?>
		<input type="checkbox" name="avif_picturefill" value="yes" <?php checked( $value, 'yes' ); ?> />
		<label for="avif_picturefill"></label>
		<p class="description">
			<?php 
			echo wp_kses(
				'Picturefill akan bekerja on-the-fly untuk memberikan fallback pada browser yang tidak mendukung elemen <picture> tanpa melakukan konversi gambar secara dinamis. <br /> Nonaktifkan opsi ini jika Anda ingin mengelola fallback secara manual atau menggunakan polyfill lain.',
				array(
					'br' => array(),
				)
			);
			?>
		</p>
		<?php
	}
	
	public function get_avif_quality() {
		$quality_option = get_option( 'avif_quality', 'sedang' );
		switch ( $quality_option ) {
			case 'rendah':
				return 40;
			case 'sedang':
				return 60;
			case 'tinggi':
				return 80;
			default:
				return 60;
		}
	}
	
	public function get_webp_quality() {
		$quality_option = get_option( 'avif_quality', 'sedang' );
		switch ( $quality_option ) {
			case 'rendah':
				return 40;
			case 'sedang':
				return 60;
			case 'tinggi':
				return 80;
			default:
				return 60;
		}
	}
}
?>