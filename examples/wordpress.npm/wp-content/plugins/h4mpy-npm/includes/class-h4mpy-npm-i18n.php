<?php

/**
 * Define the internationalization functionality
 *
 * @since      1.0.0
 *
 * @package    H4mpy_Npm
 * @subpackage H4mpy_Npm/includes
 */

class H4mpy_Npm_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'h4mpy-npm',
			false,
			dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
		);

	}
}
