<?php
/**
 * Native GitHub updater for Custom Elementor OTR Widget.
 */

if (!defined('ABSPATH')) exit;

function ceow_github_get_remote_version() {
    $cache_key = 'ceow_github_remote_version';
    $cached = get_transient($cache_key);
    if ($cached !== false) {
        return $cached;
    }

    $url = 'https://raw.githubusercontent.com/eagle4life69/custom-elementor-otr-widget/main/custom-elementor-otr-widget.php';
    $response = wp_remote_get($url, [
        'timeout' => 10,
        'headers' => [
            'User-Agent' => 'Custom-Elementor-OTR-Widget/' . CEOW_VERSION,
        ],
    ]);

    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    if (!preg_match('/^\s*Version:\s*(.+)$/mi', $body, $matches)) {
        return false;
    }

    $version = trim($matches[1]);
    set_transient($cache_key, $version, 6 * HOUR_IN_SECONDS);
    return $version;
}

function ceow_github_build_update_object($version, $plugin_file) {
    $update = new stdClass();
    $update->id = 'https://github.com/eagle4life69/custom-elementor-otr-widget';
    $update->slug = 'custom-elementor-otr-widget';
    $update->plugin = $plugin_file;
    $update->new_version = $version;
    $update->url = 'https://github.com/eagle4life69/custom-elementor-otr-widget';
    $update->package = 'https://github.com/eagle4life69/custom-elementor-otr-widget/archive/refs/heads/main.zip';
    $update->requires_php = '7.2';
    return $update;
}

function ceow_github_check_for_update($transient) {
    if (!is_object($transient)) {
        $transient = new stdClass();
    }
    if (empty($transient->response) || !is_array($transient->response)) {
        $transient->response = [];
    }
    if (empty($transient->no_update) || !is_array($transient->no_update)) {
        $transient->no_update = [];
    }

    $plugin_file = plugin_basename(CEOW_PLUGIN_FILE);
    $remote_version = ceow_github_get_remote_version();

    if (!$remote_version) {
        return $transient;
    }

    $update = ceow_github_build_update_object($remote_version, $plugin_file);

    if (version_compare(CEOW_VERSION, $remote_version, '<')) {
        $transient->response[$plugin_file] = $update;
        unset($transient->no_update[$plugin_file]);
    } else {
        $transient->no_update[$plugin_file] = $update;
        unset($transient->response[$plugin_file]);
    }

    return $transient;
}
add_filter('site_transient_update_plugins', 'ceow_github_check_for_update');

function ceow_github_plugin_information($result, $action, $args) {
    if ($action !== 'plugin_information' || empty($args->slug) || $args->slug !== 'custom-elementor-otr-widget') {
        return $result;
    }

    $remote_version = ceow_github_get_remote_version();

    $info = new stdClass();
    $info->name = 'Custom Elementor OTR Widget';
    $info->slug = 'custom-elementor-otr-widget';
    $info->version = $remote_version ?: CEOW_VERSION;
    $info->author = '<a href="https://github.com/eagle4life69">Andrew Rhynes</a>';
    $info->homepage = 'https://github.com/eagle4life69/custom-elementor-otr-widget';
    $info->requires_php = '7.2';
    $info->download_link = 'https://github.com/eagle4life69/custom-elementor-otr-widget/archive/refs/heads/main.zip';
    $info->sections = [
        'description' => 'Lists OTR episodes in Elementor with year tabs and download links.',
        'changelog' => 'See readme.txt in the GitHub repository for the current changelog.',
    ];

    return $info;
}
add_filter('plugins_api', 'ceow_github_plugin_information', 20, 3);

function ceow_github_fix_source_folder($source, $remote_source, $upgrader, $hook_extra) {
    if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== plugin_basename(CEOW_PLUGIN_FILE)) {
        return $source;
    }

    $desired_source = trailingslashit($remote_source) . 'custom-elementor-otr-widget/';
    if (untrailingslashit($source) === untrailingslashit($desired_source)) {
        return $source;
    }

    if (file_exists($desired_source)) {
        return $source;
    }

    if (@rename(untrailingslashit($source), untrailingslashit($desired_source))) {
        return $desired_source;
    }

    return new WP_Error('ceow_github_rename_failed', 'Unable to prepare the GitHub update package.');
}
add_filter('upgrader_source_selection', 'ceow_github_fix_source_folder', 10, 4);

function ceow_github_clear_update_cache($upgrader, $options) {
    if (!empty($options['action']) && $options['action'] === 'update' && !empty($options['type']) && $options['type'] === 'plugin') {
        delete_transient('ceow_github_remote_version');
    }
}
add_action('upgrader_process_complete', 'ceow_github_clear_update_cache', 10, 2);
