<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://codecanyon.net/user/h4mpy
 * @since             1.0.0
 * @package           H4mpy_Npm
 *
 * @wordpress-plugin
 * Plugin Name:       NPM Client for WordPress
 * Plugin URI:        https://codecanyon.net/user/h4mpy
 * Description:       Quick installation and updating of JS packages.
 * Version:           1.0.0
 * Author:            Anton Volosivets
 * Author URI:        https://codecanyon.net/user/h4mpy
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       h4mpy-npm
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

/**
 * Current plugin version.
 */
define('H4MPY_NPM_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 */
function activate_h4mpy_npm() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-h4mpy-npm-activator.php';
	H4mpy_Npm_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_h4mpy_npm() {
	require_once plugin_dir_path(__FILE__) . 'includes/class-h4mpy-npm-deactivator.php';
	H4mpy_Npm_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_h4mpy_npm');
register_deactivation_hook(__FILE__, 'deactivate_h4mpy_npm');

/**
 * The core plugin
 */
require plugin_dir_path(__FILE__) . 'includes/class-h4mpy-npm.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_h4mpy_npm() {
	$plugin = new H4mpy_Npm();
	$plugin->run();
}

run_h4mpy_npm();
