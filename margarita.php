<?php
/** 
 * Plugin Name: Margarita
 * Plugin URI: https://wpappstudio.com
 * Description: Margarita is a demo app showcasing basic capabilities of WP App Studio. Margarita can be implemented using FreeDev Development plan.
 * Version: 1.2.0
 * Author: eMarket Design
 * Author URI: https://emarketdesign.com
 * Text Domain: margarita
 * Domain Path: /lang
 * @package MARGARITA
 * @since WPAS 4.0
 */
/*
 * LICENSE:
 * Margarita is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Margarita is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * Please see <http://www.gnu.org/licenses/> for details.
*/
if (!defined('ABSPATH')) exit;
if (!class_exists('Margarita')):
	/**
	 * Main class for Margarita
	 *
	 * @class Margarita
	 */
	final class Margarita {
		/**
		 * @var Margarita single instance of the class
		 */
		private static $_instance;
		public $textdomain = 'margarita';
		public $app_name = 'margarita';
		/**
		 * Main Margarita Instance
		 *
		 * Ensures only one instance of Margarita is loaded or can be loaded.
		 *
		 * @static
		 * @see MARGARITA()
		 * @return Margarita - Main instance
		 */
		public static function instance() {
			if (!isset(self::$_instance)) {
				self::$_instance = new self();
				self::$_instance->define_constants();
				self::$_instance->includes();
				self::$_instance->load_plugin_textdomain();
				add_filter('the_content', array(
					self::$_instance,
					'change_content_excerpt'
				));
				add_filter('the_excerpt', array(
					self::$_instance,
					'change_content_excerpt'
				));
				add_action('admin_menu', array(
					self::$_instance,
					'display_settings'
				));
			}
			return self::$_instance;
		}
		/**
		 * Cloning is forbidden.
		 */
		public function __clone() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', $this->textdomain) , '1.0');
		}
		/**
		 * Define Margarita Constants
		 *
		 * @access private
		 * @return void
		 */
		private function define_constants() {
			define('MARGARITA_VERSION', '1.2.0');
			define('MARGARITA_AUTHOR', 'eMarket Design');
			define('MARGARITA_NAME', 'Margarita');
			define('MARGARITA_PLUGIN_FILE', __FILE__);
			define('MARGARITA_PLUGIN_DIR', plugin_dir_path(__FILE__));
			define('MARGARITA_PLUGIN_URL', plugin_dir_url(__FILE__));
			define('PHPEXCEL_ROOT', MARGARITA_PLUGIN_DIR . '/assets/ext/calculate');
		}
		/**
		 * Include required files
		 *
		 * @access private
		 * @return void
		 */
		private function includes() {
			//these files are in all apps
			if (!function_exists('emd_mb_meta')) {
				require_once MARGARITA_PLUGIN_DIR . 'assets/ext/emd-meta-box/emd-meta-box.php';
			}
			if (!function_exists('emd_translate_date_format')) {
				require_once MARGARITA_PLUGIN_DIR . 'includes/date-functions.php';
			}
			if (!function_exists('emd_get_hidden_func')) {
				require_once MARGARITA_PLUGIN_DIR . 'includes/common-functions.php';
			}
			if (!class_exists('Emd_Entity')) {
				require_once MARGARITA_PLUGIN_DIR . 'includes/entities/class-emd-entity.php';
			}
			if (!function_exists('emd_get_template_part')) {
				require_once MARGARITA_PLUGIN_DIR . 'includes/layout-functions.php';
			}
			if (!class_exists('EDD_SL_Plugin_Updater')) {
				require_once MARGARITA_PLUGIN_DIR . 'assets/ext/edd/EDD_SL_Plugin_Updater.php';
			}
			if (!class_exists('Emd_License')) {
				require_once MARGARITA_PLUGIN_DIR . 'includes/admin/class-emd-license.php';
			}
			if (!function_exists('emd_show_license_page')) {
				require_once MARGARITA_PLUGIN_DIR . 'includes/admin/license-functions.php';
			}
			//the rest
			do_action('emd_ext_include_files');
			//app specific files
			if (!function_exists('emd_show_settings_page')) {
				require_once MARGARITA_PLUGIN_DIR . 'includes/admin/settings-functions.php';
			}
			if (is_admin()) {
				//these files are in all apps
				if (!function_exists('emd_display_store')) {
					require_once MARGARITA_PLUGIN_DIR . 'includes/admin/store-functions.php';
				}
				//the rest
				if (!class_exists('Emd_Single_Taxonomy')) {
					require_once MARGARITA_PLUGIN_DIR . 'includes/admin/singletax/class-emd-single-taxonomy.php';
					require_once MARGARITA_PLUGIN_DIR . 'includes/admin/singletax/class-emd-walker-radio.php';
				}
				require_once MARGARITA_PLUGIN_DIR . 'includes/admin/glossary.php';
			}
			require_once MARGARITA_PLUGIN_DIR . 'includes/calculate-functions.php';
			require_once MARGARITA_PLUGIN_DIR . 'includes/class-install-deactivate.php';
			require_once MARGARITA_PLUGIN_DIR . 'includes/entities/class-emd-contact.php';
			require_once MARGARITA_PLUGIN_DIR . 'includes/entities/class-emd-basic-calcs.php';
			require_once MARGARITA_PLUGIN_DIR . 'includes/scripts.php';
			require_once MARGARITA_PLUGIN_DIR . 'includes/plugin-feedback-functions.php';
		}
		/**
		 * Loads plugin language files
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			$locale = apply_filters('plugin_locale', get_locale() , 'margarita');
			$mofile = sprintf('%1$s-%2$s.mo', 'margarita', $locale);
			$mofile_shared = sprintf('%1$s-emd-plugins-%2$s.mo', 'margarita', $locale);
			$lang_file_list = Array(
				'emd-plugins' => $mofile_shared,
				'margarita' => $mofile
			);
			foreach ($lang_file_list as $lang_key => $lang_file) {
				$localmo = MARGARITA_PLUGIN_DIR . '/lang/' . $lang_file;
				$globalmo = WP_LANG_DIR . '/margarita/' . $lang_file;
				if (file_exists($globalmo)) {
					load_textdomain($lang_key, $globalmo);
				} elseif (file_exists($localmo)) {
					load_textdomain($lang_key, $localmo);
				} else {
					load_plugin_textdomain($lang_key, false, MARGARITA_PLUGIN_DIR . '/lang/');
				}
			}
		}
		/**
		 * Changes content and excerpt on frontend views
		 *
		 * @access public
		 * @param string $content
		 *
		 * @return string $content , content or excerpt
		 */
		public function change_content_excerpt($content) {
			if (!is_admin()) {
				if (post_password_required()) {
					$content = get_the_password_form();
				} else {
					$mypost_type = get_post_type();
					if ($mypost_type == 'post' || $mypost_type == 'page') {
						$mypost_type = "emd_" . $mypost_type;
					}
					$ent_list = get_option($this->app_name . '_ent_list');
					if (in_array($mypost_type, array_keys($ent_list)) && class_exists($mypost_type)) {
						$func = "change_content";
						$obj = new $mypost_type;
						$content = $obj->$func($content);
					}
				}
			}
			return $content;
		}
		/**
		 * Creates plugin page in menu with submenus
		 *
		 * @access public
		 * @return void
		 */
		public function display_settings() {
			add_menu_page(__('Margarita', $this->textdomain) , __('Margarita', $this->textdomain) , 'manage_options', $this->app_name, array(
				$this,
				'display_glossary_page'
			));
			add_submenu_page($this->app_name, __('Glossary', $this->textdomain) , __('Glossary', $this->textdomain) , 'manage_options', $this->app_name);
			add_submenu_page($this->app_name, __('Settings', $this->textdomain) , __('Settings', $this->textdomain) , 'manage_options', $this->app_name . '_settings', array(
				$this,
				'display_settings_page'
			));
			add_submenu_page($this->app_name, __('Add-Ons', $this->textdomain) , __('Add-Ons', $this->textdomain) , 'manage_options', $this->app_name . '_store', array(
				$this,
				'display_store_page'
			));
			add_submenu_page($this->app_name, __('Designs', $this->textdomain) , __('Designs', $this->textdomain) , 'manage_options', $this->app_name . '_designs', array(
				$this,
				'display_design_page'
			));
			add_submenu_page($this->app_name, __('Support', $this->textdomain) , __('Support', $this->textdomain) , 'manage_options', $this->app_name . '_support', array(
				$this,
				'display_support_page'
			));
			$emd_lic_settings = get_option('emd_license_settings', Array());
			$show_lic_page = 0;
			if (!empty($emd_lic_settings)) {
				foreach ($emd_lic_settings as $key => $val) {
					if ($key == $this->app_name) {
						$show_lic_page = 1;
						break;
					} else if ($val['type'] == 'ext') {
						$show_lic_page = 1;
						break;
					}
				}
				if ($show_lic_page == 1) {
					add_submenu_page($this->app_name, __('Licenses', $this->textdomain) , __('Licenses', $this->textdomain) , 'manage_options', $this->app_name . '_licenses', array(
						$this,
						'display_licenses_page'
					));
				}
			}
			//add submenu page under app settings page
			do_action('emd_ext_add_menu_pages', $this->app_name);
		}
		/**
		 * Calls settings function to display glossary page
		 *
		 * @access public
		 * @return void
		 */
		public function display_glossary_page() {
			do_action($this->app_name . '_settings_glossary');
		}
		public function display_store_page() {
			emd_display_store($this->textdomain);
		}
		public function display_design_page() {
			emd_display_design($this->textdomain);
		}
		public function display_support_page() {
			emd_display_support($this->textdomain, 2, 'margarita');
		}
		public function display_licenses_page() {
			do_action('emd_show_license_page', $this->app_name);
		}
		public function display_settings_page() {
			do_action('emd_show_settings_page', $this->app_name);
		}
	}
endif;
/**
 * Returns the main instance of Margarita
 *
 * @return Margarita
 */
function MARGARITA() {
	return Margarita::instance();
}
// Get the Margarita instance
MARGARITA();
