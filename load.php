<?php
/**
 * Plugin Name: Icreative Wordpress Details
 * Description: Creates a Rest API route to retrieve wordpress core, theme and plugins versions of the website.
 * Version: 1.1.0
 * Author: Icreative
 */

function ic_details_token_auth($request): bool
{
    return $request->get_header('x-api-key') === API_KEY;
}

add_action('rest_api_init', function () {
    register_rest_route('api/v1', '/icreative/wordpress-details', array(
        'methods' => 'GET',
        'callback' => 'handle_get_wp_info_request',
        'permission_callback' => 'ic_details_token_auth',
    ));
});

function handle_get_wp_info_request(): array
{
    if (!function_exists('get_plugin_data')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    global $wp_version;
    $theme = wp_get_theme();
    if ($theme->parent()) {
        $theme = $theme->parent();
    }

    $plugins = [];
    foreach (get_option('active_plugins') as $plugin_file) {
        $plugin_data = get_plugin_data(WP_PLUGIN_DIR . '/' . $plugin_file);
        $plugins[] = [
            'slug' => explode('/', $plugin_file)[0],
            'name' =>  $plugin_data['Name'],
            'version' => $plugin_data['Version'],
        ];
    }

    return [
        'wordpress_core' => [[
            'slug' => 'wordpress_core',
            'name' => 'Wordpress Core',
            'version' => $wp_version,
        ]],
        'wordpress_theme' => [[
            'slug' => get_template(),
            'name' => $theme->get('Name') ?? 'Without name',
            'version' => $theme->get('Version') ?: null,
        ]],
        'wordpress_plugin' => $plugins,
    ];
}
