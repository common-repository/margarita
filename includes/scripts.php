<?php
/**
 * Enqueue Scripts Functions
 *
 * @package MARGARITA
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('admin_enqueue_scripts', 'margarita_load_admin_enq');
/**
 * Enqueue style and js for each admin entity pages and settings
 *
 * @since WPAS 4.0
 * @param string $hook
 *
 */
function margarita_load_admin_enq($hook) {
	global $typenow;
	$dir_url = MARGARITA_PLUGIN_URL;
	do_action('emd_ext_admin_enq', 'margarita', $hook);
	$min_trigger = get_option('margarita_show_rateme_plugin_min', 0);
	if (-1 !== $min_trigger) {
		wp_enqueue_style('emd-plugin-rateme-css', $dir_url . 'assets/css/emd-plugin-rateme.css');
		wp_enqueue_script('emd-plugin-rateme-js', $dir_url . 'assets/js/emd-plugin-rateme.js');
	}
	if ($hook == 'edit-tags.php') {
		return;
	}
	if (isset($_GET['page']) && in_array($_GET['page'], Array(
		'margarita',
		'margarita_notify',
		'margarita_settings'
	))) {
		wp_enqueue_script('accordion');
		return;
	} else if (isset($_GET['page']) && in_array($_GET['page'], Array(
		'margarita_store',
		'margarita_designs',
		'margarita_support'
	))) {
		wp_enqueue_style('admin-tabs', $dir_url . 'assets/css/admin-store.css');
		return;
	}
	if (in_array($typenow, Array(
		'emd_contact',
		'emd_basic_calcs'
	))) {
		$theme_changer_enq = 1;
		$datetime_enq = 0;
		$date_enq = 0;
		$sing_enq = 0;
		$tab_enq = 0;
		if ($hook == 'post.php' || $hook == 'post-new.php') {
			$unique_vars['msg'] = __('Please enter a unique value.', 'margarita');
			$unique_vars['reqtxt'] = __('required', 'margarita');
			$unique_vars['app_name'] = 'margarita';
			$ent_list = get_option('margarita_ent_list');
			if (!empty($ent_list[$typenow])) {
				$unique_vars['keys'] = $ent_list[$typenow]['unique_keys'];
				if (!empty($ent_list[$typenow]['req_blt'])) {
					$unique_vars['req_blt_tax'] = $ent_list[$typenow]['req_blt'];
				}
			}
			$tax_list = get_option('margarita_tax_list');
			if (!empty($tax_list[$typenow])) {
				foreach ($tax_list[$typenow] as $txn_name => $txn_val) {
					if ($txn_val['required'] == 1) {
						$unique_vars['req_blt_tax'][$txn_name] = Array(
							'hier' => $txn_val['hier'],
							'type' => $txn_val['type'],
							'label' => $txn_val['label'] . ' ' . __('Taxonomy', 'margarita')
						);
					}
				}
			}
			wp_enqueue_script('unique_validate-js', $dir_url . 'assets/js/unique_validate.js', array(
				'jquery',
				'jquery-validate'
			) , MARGARITA_VERSION, true);
			wp_localize_script("unique_validate-js", 'unique_vars', $unique_vars);
		} elseif ($hook == 'edit.php') {
			wp_enqueue_style('margarita-allview-css', MARGARITA_PLUGIN_URL . '/assets/css/allview.css');
		}
		switch ($typenow) {
			case 'emd_basic_calcs':
				$sing_enq = 1;
			break;
			case 'emd_contact':
				$sing_enq = 1;
			break;
		}
		if ($datetime_enq == 1) {
			wp_enqueue_script("jquery-ui-timepicker", $dir_url . 'assets/ext/emd-meta-box/js/jqueryui/jquery-ui-timepicker-addon.js', array(
				'jquery-ui-datepicker',
				'jquery-ui-slider'
			) , MARGARITA_VERSION, true);
			$tab_enq = 1;
		} elseif ($date_enq == 1) {
			wp_enqueue_script("jquery-ui-datepicker");
			$tab_enq = 1;
		}
		if ($sing_enq == 1) {
			wp_enqueue_script('radiotax', MARGARITA_PLUGIN_URL . 'includes/admin/singletax/singletax.js', array(
				'jquery'
			) , MARGARITA_VERSION, true);
		}
	}
}
add_action('wp_enqueue_scripts', 'margarita_frontend_scripts');
/**
 * Enqueue style and js for each frontend entity pages and components
 *
 * @since WPAS 4.0
 *
 */
function margarita_frontend_scripts() {
	$dir_url = MARGARITA_PLUGIN_URL;
	wp_register_style('margarita-allview-css', $dir_url . '/assets/css/allview.css');
	$grid_vars = Array();
	$local_vars['ajax_url'] = admin_url('admin-ajax.php');
	$wpas_shc_list = get_option('margarita_shc_list');
	wp_register_style("margarita-default-single-css", MARGARITA_PLUGIN_URL . 'assets/css/margarita-default-single.css');
	if (is_single() && get_post_type() == 'emd_contact') {
		wp_enqueue_style("margarita-default-single-css");
	}
	if (is_single() && get_post_type() == 'emd_basic_calcs') {
		wp_enqueue_style("margarita-default-single-css");
	}
}
/**
 * Enqueue if allview css is not enqueued
 *
 * @since WPAS 4.5
 *
 */
function margarita_enq_allview() {
	if (!wp_style_is('margarita-allview-css', 'enqueued')) {
		wp_enqueue_style('margarita-allview-css');
	}
}
