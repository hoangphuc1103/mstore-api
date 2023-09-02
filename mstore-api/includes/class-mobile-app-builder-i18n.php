<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://inspireui.com
 * @since      1.0.0
 *
 * @package    Mobile_App_Builder
 * @subpackage Mobile_App_Builder/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Mobile_App_Builder
 * @subpackage Mobile_App_Builder/includes
 * @author     InspireUI <support@inspireui.com>
 */
class Mobile_App_Builder_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'mobile-app-builder',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
