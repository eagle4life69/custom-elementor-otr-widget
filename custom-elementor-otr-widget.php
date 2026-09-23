<?php
/*
Plugin Name: Custom Elementor OTR Widget
Description: Lists OTR episodes with tabs by year and download links.
Version: 2.7.8
Author: Andrew Rhynes
Author URI: https://github.com/eagle4life69
Plugin URI: https://github.com/eagle4life69/custom-elementor-otr-widget
GitHub Plugin URI: https://github.com/eagle4life69/custom-elementor-otr-widget
Requires Plugins: elementor
Elementor tested up to: 4.3.0
Elementor Pro tested up to: 4.3.0
*/

if (!defined('ABSPATH')) exit;

define('CEOW_VERSION', '2.7.8');
define('CEOW_PLUGIN_FILE', __FILE__);
require_once __DIR__ . '/github-updater.php';

function ceow_register_assets() {
    wp_register_script('otr-widget-script', plugins_url('assets/widget.js', __FILE__), ['jquery'], CEOW_VERSION, true);
    wp_register_style('otr-widget-style', plugins_url('assets/style.css', __FILE__), [], CEOW_VERSION);
}
add_action('wp_enqueue_scripts', 'ceow_register_assets');

function ceow_register_elementor_widget($widgets_manager) {
    require_once __DIR__ . '/widgets/otr-episode-table.php';
    $widgets_manager->register(new \Elementor\OTR_Episode_Table());
}
add_action('elementor/widgets/register', 'ceow_register_elementor_widget');
