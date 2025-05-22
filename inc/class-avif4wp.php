<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Avif4WP {
    public static $instance;
    public $version;
    public $settings;
    public $converter;

    public function __construct() {
        self::$instance = $this;
        $this->version  = defined( 'AVIF4WP_VERSION' ) ? AVIF4WP_VERSION : '1.0.0';

        add_action( 'init', array( $this, 'load_textdomain' ) );
        add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );
        add_action( 'admin_menu', array( $this, 'remove_submenu_duplicate' ), 999 );
    }

    public function load_textdomain() {
        load_plugin_textdomain( 'avif4wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }

    public function run() {
        $this->settings = new Avif4WP_Settings();

        require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-convertselector.php';
        $this->converter = new Avif4WP_ConvertSelector();
        $this->converter->init_hooks();

        add_filter( 'wp_get_attachment_url', array( $this->converter, 'redirect_to_converted' ), 10, 2 );
        add_filter( 'the_content', array( $this->converter, 'process_editor_images' ) );

        add_filter( 'wp_get_attachment_url', array( $this->converter, 'redirect_to_converted' ), 10, 2 );
        add_filter( 'the_content', array( $this->converter, 'process_editor_images' ) );
    }

    public function register_admin_menu() {
        add_menu_page(
            'AVIF4WP',
            'AVIF4WP',
            'manage_options',
            'avif4wp',
            array( $this, 'render_admin_page' ),
            'dashicons-controls-repeat'
        );
    }

    public function remove_submenu_duplicate() {
        remove_submenu_page( 'avif4wp', 'avif4wp' );
    }

    public function render_admin_page() {
        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'dashboard';
        ?>
        <div class="wrap">
            <h1>AVIF4WP</h1>
            <?php
            if ( 'settings' === $active_tab && ! empty( $_GET['settings-updated'] ) ) {
                echo '<div id="message" class="updated notice is-dismissible"><p>' . esc_html__( 'Pengaturan berhasil disimpan.', 'avif4wp' ) . '</p></div>';
            }
            self::render_tab_menu( $active_tab );
            ?>
            <div class="avif4wp-content">
                <?php self::render_tab_content( $active_tab ); ?>
            </div>
        </div>
        <?php
    }

    public function avif4wp_default_image_alt( $attr, $attachment, $size ) {
        if ( empty( $attr['alt'] ) ) {
            $attr['alt'] = get_the_title( $attachment->ID );
        }
        return $attr;
    }

    public static function render_tab_menu( $active_tab ) {
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=avif4wp&tab=dashboard' ) ); ?>" class="nav-tab <?php echo ( 'dashboard' === $active_tab ? 'nav-tab-active' : '' ); ?>">Dasbor</a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=avif4wp&tab=settings' ) ); ?>" class="nav-tab <?php echo ( 'settings' === $active_tab ? 'nav-tab-active' : '' ); ?>">Pengaturan</a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=avif4wp&tab=system_info' ) ); ?>" class="nav-tab <?php echo ( 'system_info' === $active_tab ? 'nav-tab-active' : '' ); ?>">Info Sistem</a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=avif4wp&tab=changelog' ) ); ?>" class="nav-tab <?php echo ( 'changelog' === $active_tab ? 'nav-tab-active' : '' ); ?>">Catatan Perubahan</a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=avif4wp&tab=about' ) ); ?>" class="nav-tab <?php echo ( 'about' === $active_tab ? 'nav-tab-active' : '' ); ?>">Tentang</a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=avif4wp&tab=upgrade' ) ); ?>" class="nav-tab <?php echo ( 'upgrade' === $active_tab ? 'nav-tab-active' : '' ); ?>"><?php esc_html_e( 'Upgrade', 'avif4wp' ); ?></a>
        </h2>
        <?php
    }

    public static function render_tab_content( $active_tab ) {
        if ( 'dashboard' === $active_tab ) {
            self::display_dashboard();

        } elseif ( 'settings' === $active_tab ) {
            ?>
            <form method="post" action="options.php">
                <?php
                    settings_fields( 'avif4wp_settings_group' );
                    do_settings_sections( 'avif4wp-settings' );
                    submit_button();
                ?>
            </form>
            <?php

        } elseif ( 'system_info' === $active_tab ) {
            require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-system-info.php';
            Avif4WP_System_Info::render();

        } elseif ( 'changelog' === $active_tab ) {
            require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-changelog.php';
            Avif4WP_Changelog::render();

        } elseif ( 'about' === $active_tab ) {
            require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-about.php';
            Avif4WP_About::render();

        } elseif ( 'upgrade' === $active_tab ) {
            require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-upgrade.php';
            if ( class_exists( 'Avif4WP_Upgrade' ) ) {
                Avif4WP_Upgrade::render();
            } else {
                echo '<div class="notice notice-error"><p>' . esc_html__( 'Kelas Avif4WP_Upgrade tidak ditemukan.', 'avif4wp' ) . '</p></div>';
            }

        } elseif ( 'cdn' === $active_tab ) {
            if ( ! empty( self::$instance->cdn ) ) {
                self::$instance->cdn->render_settings_page();
            } else {
                echo '<div class="notice notice-error"><p>Kelas Avif4WP_CDN belum diinisialisasi.</p></div>';
            }

        } else {
            echo '<p>' . esc_html__( 'Tab tidak ditemukan.', 'avif4wp' ) . '</p>';
        }
    }

    public static function display_dashboard() {
        $dashboard_file = AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-dashboard.php';
        if ( file_exists( $dashboard_file ) ) {
            require_once $dashboard_file;
            if ( class_exists( 'Avif4WP_Dashboard' ) && method_exists( 'Avif4WP_Dashboard', 'render_dashboard' ) ) {
                Avif4WP_Dashboard::render_dashboard();
            } else {
                echo '<div class="notice notice-error"><p>Kelas atau metode render_dashboard() tidak ditemukan.</p></div>';
            }
        } else {
            echo '<div class="notice notice-error"><p>File dashboard tidak ditemukan: ' . esc_html( $dashboard_file ) . '</p></div>';
        }
    }

}