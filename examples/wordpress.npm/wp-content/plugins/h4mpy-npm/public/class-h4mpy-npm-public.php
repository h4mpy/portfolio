<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    H4mpy_Npm
 * @subpackage H4mpy_Npm/public
 */
class H4mpy_Npm_Public {

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
	 * @param string $h4mpy_npm The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct($h4mpy_npm, $version) {

		$this->h4mpy_npm = $h4mpy_npm;
		$this->version   = $version;
		$installed       = array();
		$js              = array();
		$css             = array();
		$installedFile   = $_SERVER['DOCUMENT_ROOT'] . '/npm/wp-lock.json';
		if (is_file($installedFile)) {
			$installed = json_decode(file_get_contents($installedFile), true);
		}
		$config = get_template_directory() . '/template-lock.json';
		if (is_file($config) && $arJson = json_decode(file_get_contents($config), true)) {
			if (is_array($arJson) && count($arJson) > 0) {
				foreach ($arJson as $arJsonLink) {
					if (isset($arJsonLink['link']) && $arJsonLink['link'] !== '' && is_file(
							$_SERVER["DOCUMENT_ROOT"] . $arJsonLink['link']
						)) {
						$arJsonLink['version'] = (isset($installed[$arJsonLink['package']]['version'])) ? $installed[$arJsonLink['package']]['version'] : $this->version;
						if (substr($arJsonLink['link'], - 3) === ".js") {
							$js[] = $arJsonLink;
						} elseif (substr($arJsonLink['link'], - 4) === ".css") {
							$css[] = $arJsonLink;
						}
					}
				}
			}
		}
		$this->js = (count($js) > 0) ? $js : false;
		$this->css = (count($css) > 0) ? $css : false;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if ($this->css) {
			foreach ($this->css as $css) {
				$fileTitle = basename($css['link']);
				$fileTitle = str_replace('.css', '', $fileTitle);
				$fileTitle = preg_replace(
					'/[^0-9a-zA-Z]/',
					"-",
					str_replace('@', '', $css['package']) . '-' . $fileTitle
				);
				wp_enqueue_style($fileTitle, $css['link'], array(), $css['version'], 'all');
			}
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		if ($this->js) {
			foreach ($this->js as $js) {
				$fileTitle = basename($js['link']);
				$fileTitle = str_replace('.js', '', $fileTitle);
				$fileTitle = preg_replace(
					'/[^0-9a-zA-Z]/',
					"-",
					str_replace('@', '', $js['package']) . '-' . $fileTitle
				);
				wp_enqueue_script($fileTitle, $js['link'], array(), $js['version']);
			}
		}
	}

}
