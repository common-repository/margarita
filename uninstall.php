<?php
/**
 *  Uninstall Margarita
 *
 * Uninstalling deletes notifications and terms initializations
 *
 * @package MARGARITA
 * @since WPAS 4.0
 */
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
if (!current_user_can('activate_plugins')) return;
function margarita_uninstall() {
	//delete options
	$options_to_delete = Array(
		'margarita_notify_list',
		'margarita_ent_list',
		'margarita_attr_list',
		'margarita_shc_list',
		'margarita_tax_list',
		'margarita_rel_list',
		'margarita_license_key',
		'margarita_license_status',
		'margarita_comment_list',
		'margarita_notify_init_list',
		'margarita_glob_forms_init_list',
		'margarita_glob_forms_list',
		'margarita_access_views',
		'margarita_limitby_auth_caps',
		'margarita_limitby_caps',
		'margarita_has_limitby_cap',
		'margarita_setup_pages',
		'margarita_emd_contact_terms_init'
	);
	if (!empty($options_to_delete)) {
		foreach ($options_to_delete as $option) {
			delete_option($option);
		}
	}
}
if (is_multisite()) {
	global $wpdb;
	$blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
	if ($blogs) {
		foreach ($blogs as $blog) {
			switch_to_blog($blog['blog_id']);
			margarita_uninstall();
		}
		restore_current_blog();
	}
} else {
	margarita_uninstall();
}
