<?php
/**
 * Layout Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Retrieves a template part
 * @since WPAS 4.0
 *
 * Taken from bbPress,eaysdigitaldownloads
 *
 * @param string $app
 * @param string $slug
 * @param string $name Optional. Default null
 * @param bool   $load
 *
 * @return string
 *
 * @uses emd_locate_template()
 */
if (!function_exists('emd_get_template_part')) {
	function emd_get_template_part($app, $slug, $name = null, $load = true) {
		// Setup possible parts
		$templates = array();
		if (isset($name)) $templates[] = $slug . '-' . $name . '.php';
		$templates[] = $slug . '.php';
		// Allow template parts to be filtered
		$templates = apply_filters('emd_get_template_part', $templates, $slug, $name);
		// Return the part that is found
		return emd_locate_template($app, $templates, $load, false);
	}
}
/**
 * Retrieves a template part
 * @since WPAS 4.0
 *
 * Taken from bbPress,eaysdigitaldownloads
 *
 * @param string $app
 * @param array $template_names
 * @param bool   $load
 * @param bool   $require_once
 *
 * @return string
 *
 * @uses load_template()
 */
if (!function_exists('emd_locate_template')) {
	function emd_locate_template($app, $template_names, $load = false, $require_once = true) {
		// No file found yet
		$located = false;
		// Try to find a template file
		foreach ((array)$template_names as $template_name) {
			// Continue if template is empty
			if (empty($template_name)) continue;
			// Trim off any slashes from the template name
			$template_name = ltrim($template_name, '/');
			// try locating this template file by looping through the template paths
			foreach(emd_get_theme_template_paths($app) as $template_path) {
				if (file_exists($template_path . $template_name)) {
					$located = $template_path . $template_name;
					break;
				}
			}
			if($located) {
				break;
			}
		}
		if ((true == $load) && !empty($located)) {
			load_template($located, $require_once);
		}
		return $located;
	}
}
if (!function_exists('emd_get_theme_template_paths')) {
	function emd_get_theme_template_paths($app) {
		$template_dir = emd_get_theme_template_dir_name();
		$file_paths = array(
			1 => trailingslashit(get_stylesheet_directory()) . $template_dir,
			10 => trailingslashit(get_template_directory()) . $template_dir,
			100 => emd_get_templates_dir($app)
		);

		$file_paths = apply_filters('emd_template_paths', $file_paths);
		// sort the file paths based on priority
		ksort($file_paths, SORT_NUMERIC);
		return array_map('trailingslashit', $file_paths);
	}
}
if (!function_exists('emd_get_theme_template_dir_name')) {
	function emd_get_theme_template_dir_name() {
		return trailingslashit(apply_filters('emd_templates_dir', 'emd_templates'));
	}
}
if (!function_exists('emd_get_templates_dir')) {
	function emd_get_templates_dir($app) {
		return constant(strtoupper(str_replace("-", "_", $app)) . '_PLUGIN_DIR') . 'layouts/';
	}
}
