<?php
/**
 * Plugin Name: Mind Elixir Admin Mind Maps
 * Description: Adds an admin page for creating/editing mind maps using Mind Elixir.
 * Version: 1.0
 * Author: bomura
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add admin menu page (only for administrators.
function mea_add_admin_menu() {
    add_menu_page(
        'Mind Map Editor',            // Page title
        'Mind Map Editor',            // Menu title
        'manage_options',             // Capability (admin only)
        'wp-mind-elixir-editor',      // Menu slug
        'mea_render_admin_page',      // Callback to render the page
        'dashicons-chart-pie',        // Icon (example)
        80                            // Position
    );
}
add_action( 'admin_menu', 'mea_add_admin_menu' );

// Enqueue scripts/styles for our admin page.
function mea_admin_enqueue_scripts( $hook_suffix ) {
    // Only load on our plugin's admin page.
    if ( $hook_suffix !== 'toplevel_page_wp-mind-elixir-editor' ) {
        return;
    }
    // Mind Elixir library from CDN.
    wp_enqueue_script(
        'wp-mind-elixir-cdn',
        'https://cdn.jsdelivr.net/npm/mind-elixir/dist/MindElixir.iife.js',
        array(),
        '4.5.2',
        true
    );
    // Our custom JS (depends on jQuery and Mind Elixir).
    wp_enqueue_script(
        'wp-mind-elixir-admin-js',
        plugin_dir_url(__FILE__) . 'js/wp-mind-elixir-admin.js',
        array( 'jquery', 'wp-mind-elixir-cdn' ),
        '1.0',
        true
    );
    // Localize script with saved data and AJAX info.
    $saved = get_option( 'mind_elixir_map_data', '' );
    wp_localize_script( 'wp-mind-elixir-admin-js', 'MEMapData', array(
        'initial'  => $saved ? $saved : '',
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'me_save_map' )
    ) );
    // Basic CSS for the map container.
    wp_enqueue_style(
        'wp-mind-elixir-admin-css',
        plugin_dir_url(__FILE__) . 'css/wp-mind-elixir-admin.css',
        array(),
        '1.0'
    );
}
add_action( 'admin_enqueue_scripts', 'mea_admin_enqueue_scripts' );

// Render the admin page content.
function mea_render_admin_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1>Mind Map Editor</h1>
        <div id="map"></div>
        <p>
            <button id="save-map-button" class="button button-primary">Save Mind Map</button>
            <button id="reset-map-button" class="button">Reset Mind Map</button>
        </p>
        <div id="save-status"></div>
    </div>
    <?php
}

// Handle AJAX save (wp_ajax only for logged-in users).
function mea_save_mind_map() {
    // Capability check.
   if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied' );
    }
    // Nonce verification.
    check_ajax_referer( 'me_save_map', 'nonce' );
    // Retrieve posted data.
    $data = isset($_POST['data']) ? wp_unslash( $_POST['data'] ) : '';

    if ( $data ) {
        update_option( 'mind_elixir_map_data', $data );
        wp_send_json_success( 'Map saved' );
    } else {
        wp_send_json_error( 'No data to save' );
    }
}
add_action( 'wp_ajax_save_mind_map', 'mea_save_mind_map' );

