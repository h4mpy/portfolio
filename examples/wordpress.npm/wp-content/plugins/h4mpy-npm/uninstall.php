<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 *
 * @package    H4mpy_Npm
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

$fileSystem = new WP_Filesystem_Direct('');
$fileSystem->rmdir($_SERVER["DOCUMENT_ROOT"] . '/npm', true);

$themes = wp_get_themes();

foreach ($themes as $theme) {
	$path = $theme->get_template_directory() . '/template-lock.json';
	if (is_file($path)) {
		unlink($path);
	}
}

if (is_file($_SERVER["DOCUMENT_ROOT"] . '/package.json')) {
	unlink($_SERVER["DOCUMENT_ROOT"] . '/package.json');
}