<?php

/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    H4mpy_Npm
 * @subpackage H4mpy_Npm/includes
 */
class H4mpy_Npm {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      H4mpy_Npm_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $h4mpy_npm The string used to uniquely identify this plugin.
	 */
	protected $h4mpy_npm;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if (defined('H4MPY_NPM_VERSION')) {
			$this->version = H4MPY_NPM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->h4mpy_npm = 'h4mpy-npm';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-h4mpy-npm-loader.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-h4mpy-npm-i18n.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-h4mpy-npm-admin.php';
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-h4mpy-npm-public.php';

		$this->loader = new H4mpy_Npm_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new H4mpy_Npm_i18n();

		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new H4mpy_Npm_Admin($this->get_h4mpy_npm(), $this->get_version());

		$this->loader->add_action('admin_menu', $plugin_admin, 'menu');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new H4mpy_Npm_Public($this->get_h4mpy_npm(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_h4mpy_npm() {
		return $this->h4mpy_npm;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    H4mpy_Npm_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}
}
