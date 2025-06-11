<?php
/*
Plugin Name: Custom Elementor OTR Widget
Description: Lists OTR episodes with tabs by year and download links.
Version: 2.6.2
Author: Andrew Rhynes
GitHub Plugin URI: https://github.com/eagle4life69/custom-elementor-otr-widget
GitHub Branch: main
*/


function ceow_enqueue_assets() {
    wp_enqueue_script('otr-widget-script', plugins_url('assets/widget.js', __FILE__), ['jquery'], false, true);
    wp_enqueue_style('otr-widget-style', plugins_url('assets/style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'ceow_enqueue_assets');

function ceow_register_elementor_widget() {
    require_once(__DIR__ . '/widgets/otr-episode-table.php');
    \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor\OTR_Episode_Table());
}
add_action('elementor/widgets/widgets_registered', 'ceow_register_elementor_widget');
