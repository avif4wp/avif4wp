<?php
/**
 * Plugin Name:     AVIF4WP
 * Plugin URI:      https://avif4wp.com
 * Description:     AVIF4WP mengoptimalkan gambar WordPress Anda dengan mengonversi JPG/PNG ke AVIF atau WebP secara otomatis. Dilengkapi fitur regenerasi thumbnail, konversi massal dengan dukungan caching, pengaturan kualitas, dan placeholder warna.
 * Version:         1.0.0
 * Update URI:      https://github.com/avif4wp/avif4wp
 * Author:          AVIF4WP
 * License:         GPLv2
 * License URI:     https://opensource.org/licenses/GPL-2.0
 * Requires at least:   5.6
 * Tested up to:     6.7
 * Requires PHP:     8.1
 * Text Domain:     avif4wp
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'get_plugin_data' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}
$plugin_data = get_plugin_data( __FILE__ );
if ( ! defined( 'AVIF4WP_VERSION' ) ) {
    define( 'AVIF4WP_VERSION', $plugin_data['Version'] );
}
define( 'AVIF4WP_MAIN_FILE', __FILE__ );
define( 'AVIF4WP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AVIF4WP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( is_admin() ) {
    add_filter( 'pre_set_site_transient_update_plugins', 'avif4wp_github_update_plugin' );
    add_filter( 'plugins_api',                  'avif4wp_github_plugins_api', 10, 3 );
}

function avif4wp_github_update_plugin( $transient ) {
    if ( empty( $transient->checked ) ) {
        return $transient;
    }

    $repo = 'avif4wp/avif4wp';

    $response = wp_remote_get(
        "https://api.github.com/repos/{$repo}/releases/latest",
        [
            'headers' => [
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            ],
        ]
    );
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return $transient;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );
    if ( empty( $data->tag_name ) ) {
        return $transient;
    }

    if ( version_compare( $data->tag_name, AVIF4WP_VERSION, '>' ) ) {
        $plugin_file = plugin_basename( __FILE__ );
        $transient->response[ $plugin_file ] = (object) [
            'slug'        => 'avif4wp',
            'new_version' => $data->tag_name,
            'package'     => $data->zipball_url,
            'url'         => $data->html_url,
        ];
    }

    return $transient;
}

function avif4wp_github_plugins_api( $res, $action, $args ) {
    if ( 'plugin_information' !== $action || 'avif4wp' !== $args->slug ) {
        return $res;
    }

    $repo = 'avif4wp/avif4wp';
    $response = wp_remote_get(
        "https://api.github.com/repos/{$repo}/releases/latest",
        [
            'headers' => [
                'Accept'     => 'application/vnd.github.v3+json',
                'User-Agent' => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
            ],
        ]
    );
    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return $res;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ) );
    $body = isset( $data->body ) ? $data->body : '';

    $extract = function( $body, $heading ) {
        if ( preg_match( '/##\s*' . preg_quote( $heading, '/' ) . '\s*(.+?)(?=##|$)/s', $body, $m ) ) {
            return trim( $m[1] );
        }
        return '';
    };

    $raw_description = $extract( $body, 'Deskripsi' );

    $raw_changelog = $extract( $body, 'Changelog' );
    $html_changelog = '';
    if ( $raw_changelog ) {
        $lines = preg_split( '/[\r\n]+/', trim( $raw_changelog ) );
        $html_changelog  = '<ul>';
        foreach ( $lines as $line ) {
            $line = trim( $line );
            if ( empty( $line ) ) {
                continue;
            }
            $clean = preg_replace( '/^\-\s*/', '', $line );
            $html_changelog .= '<li>' . esc_html( $clean ) . '</li>';
        }
        $html_changelog .= '</ul>';
    }

    return (object) [
        'name'          => $data->name,
        'slug'          => 'avif4wp',
        'version'       => $data->tag_name,
        'author'        => '<a href="https://github.com/avif4wp">AVIF4WP</a>',
        'homepage'      => $data->html_url,
        'download_link' => $data->zipball_url,
        'sections'      => [
            'description' => $raw_description,
            'changelog'   => $html_changelog,
        ],
    ];
}

function avif4wp_set_activation_redirect() {
    set_transient( 'avif4wp_activation_redirect', true, 30 );
}
register_activation_hook( __FILE__, 'avif4wp_set_activation_redirect' );

function avif4wp_redirect_after_activation() {
    if ( is_admin() && get_transient( 'avif4wp_activation_redirect' ) ) {
        delete_transient( 'avif4wp_activation_redirect' );
        wp_redirect( admin_url( 'admin.php?page=avif4wp' ) );
        exit;
    }
}
add_action( 'admin_init', 'avif4wp_redirect_after_activation' );

require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp.php';
require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-dashboard.php';
require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-settings.php';
require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-upgrade.php';
require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-mediarow.php';
require_once AVIF4WP_PLUGIN_DIR . 'inc/class-avif4wp-convertselector.php';

add_action( 'plugins_loaded', 'avif4wp_init' );
function avif4wp_init() {
    $plugin = new AVIF4WP();
    if ( method_exists( $plugin, 'run' ) ) {
        $plugin->run();
    }
}

add_action( 'wp_enqueue_scripts', 'avif4wp_enqueue_picturefill' );
function avif4wp_enqueue_picturefill() {
    if ( 'yes' === get_option( 'avif_picturefill', 'yes' ) ) {
        wp_enqueue_script(
            'picturefill',
            AVIF4WP_PLUGIN_URL . 'assets/js/picturefill.min.js',
            [],
            AVIF4WP_VERSION,
            true
        );
    }
}

add_action( 'admin_enqueue_scripts', 'avif4wp_enqueue_admin_styles' );
function avif4wp_enqueue_admin_styles() {
    wp_enqueue_style(
        'avif4wp_admin_css',
        AVIF4WP_PLUGIN_URL . 'assets/css/admin-style.css',
        [],
        AVIF4WP_VERSION
    );
}

add_action( 'admin_enqueue_scripts', 'avif4wp_enqueue_dashboard_styles' );
function avif4wp_enqueue_dashboard_styles( $hook ) {
    if ( 'toplevel_page_avif4wp' === $hook ) {
        wp_enqueue_style(
            'avif4wp_dashboard_css',
            AVIF4WP_PLUGIN_URL . 'assets/css/dashboard-style.css',
            [],
            AVIF4WP_VERSION
        );
    }
}