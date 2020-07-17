<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 *
 * @package    H4mpy_Npm
 * @subpackage H4mpy_Npm/admin
 */

class H4mpy_Npm_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $h4mpy_npm The ID of this plugin.
	 */
	private $h4mpy_npm;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $h4mpy_npm The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($h4mpy_npm, $version) {

		$this->h4mpy_npm = $h4mpy_npm;
		$this->version   = $version;

	}

	public function menu() {
		add_menu_page(
			__('NPM client (Quick installation and updating of JS packages)', 'h4mpy-npm'),
			__('NPM client', 'h4mpy-npm'),
			'edit_themes',
			'h4mpy_npm',
			array($this, 'admin_index'),
			'dashicons-download',
			110
		);
	}

	public function admin_index() {
		require_once plugin_dir_path(__FILE__) . 'partials/h4mpy-npm-admin-display.php';
	}
}
