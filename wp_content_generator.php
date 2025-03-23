<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://quitiweb.com
 * @since             1.0.1
 * @package           wp_content_generator
 *
 * @wordpress-plugin
 * Plugin Name:       WP Content Generator
 * Plugin URI:        https://quitiweb.com
 * Description:       The "WP Content Generator" plugin is particularly useful for website administrators who 
 *                    want to quickly populate their WordPress site with AI generated content. It saves time and 
 *                    effort by automatically generating content that mimics real posts and pages, enabling you 
 *                    to focus on other aspects of website development or testing.
 * Version:           1.0.1
 * Author: Quiti Kites
 * Author URI: https://quitiweb.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp_content_generator
 * WC tested up to: 7.8.1
 * Domain Path: /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0-beta and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
*/
define("wp_content_generator_PLUGIN_NAME_VERSION", "1.0.1" );
define("wp_content_generator_PLUGIN_BASE_URL", plugin_basename( __FILE__ ));
define("wp_content_generator_PLUGIN_BASE_URI", plugin_dir_path( __FILE__ ));
define("wp_content_generator_PLUGIN_DIR", plugin_basename( __DIR__ ));
define("wp_content_generator_PLUGIN_NAME", "WP Content Generator");
/**
 * The code that runs during plugin activation.
 */
function activate_wp_content_generator() {
    require_once plugin_dir_path( __FILE__ ) . "includes/class-wp_content_generator-activator.php";
    wp_content_generator_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_content_generator() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-wp_content_generator-deactivator.php';
    wp_content_generator_Deactivator::deactivate();
}

/**
 * Initialize plugin
 */
function wp_content_generator_init() {
    // Load translations
    if (did_action('init')) {
        load_plugin_textdomain(
            'wp_content_generator',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }
}

// Setup activation/deactivation hooks
register_activation_hook(__FILE__, 'activate_wp_content_generator');
register_deactivation_hook(__FILE__, 'deactivate_wp_content_generator');

// Load core functionality after plugins are loaded
add_action('plugins_loaded', function() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp_content_generator.php';
    require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
    require_once plugin_dir_path(__FILE__) . 'includes/functions-posts.php';
    
    // Initialize the plugin
    $plugin = new wp_content_generator();
    $plugin->run();
});

// Load translations at the right time
add_action('init', 'wp_content_generator_init', 10);

// Remove the old actions and functions that were loading too early
remove_action('wp_loaded', 'wp_content_generatorAllLoaded');
remove_action('plugins_loaded', 'wp_content_generator_load_textdomain');

/**
 * Disable jQuery Migrate warnings
 */
function wp_content_generator_disable_jquery_migrate_warnings() {
    if (!WP_DEBUG) {
        // Remove jQuery Migrate warnings
        add_filter('wp_default_scripts', function($scripts) {
            if (!empty($scripts->registered['jquery'])) {
                $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
            }
        });
    }
}
add_action('init', 'wp_content_generator_disable_jquery_migrate_warnings');
