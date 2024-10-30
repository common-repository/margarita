<?php
/**
 * Install and Deactivate Plugin Functions
 * @package MARGARITA
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
if (!class_exists('Margarita_Install_Deactivate')):
	/**
	 * Margarita_Install_Deactivate Class
	 * @since WPAS 4.0
	 */
	class Margarita_Install_Deactivate {
		private $option_name;
		/**
		 * Hooks for install and deactivation and create options
		 * @since WPAS 4.0
		 */
		public function __construct() {
			$this->option_name = 'margarita';
			$curr_version = get_option($this->option_name . '_version', 1);
			$new_version = constant(strtoupper($this->option_name) . '_VERSION');
			if (version_compare($curr_version, $new_version, '<')) {
				$this->set_options();
				$this->set_roles_caps();
				update_option($this->option_name . '_version', $new_version);
			}
			register_activation_hook(MARGARITA_PLUGIN_FILE, array(
				$this,
				'install'
			));
			register_deactivation_hook(MARGARITA_PLUGIN_FILE, array(
				$this,
				'deactivate'
			));
			add_action('wp_head', array(
				$this,
				'version_in_header'
			));
			add_action('admin_init', array(
				$this,
				'register_settings'
			) , 0);
			if (is_admin()) {
				$this->stax = new Emd_Single_Taxonomy('margarita');
			}
			add_action('before_delete_post', array(
				$this,
				'delete_post_file_att'
			));
			add_action('init', array(
				$this,
				'init_extensions'
			) , 99);
		}
		public function version_in_header() {
			$version = constant(strtoupper($this->option_name) . '_VERSION');
			$name = constant(strtoupper($this->option_name) . '_NAME');
			echo '<meta name="generator" content="' . $name . ' v' . $version . ' - https://emdplugins.com" />' . "\n";
		}
		public function init_extensions() {
			do_action('emd_ext_init', $this->option_name);
		}
		/**
		 * Runs on plugin install to setup custom post types and taxonomies
		 * flushing rewrite rules, populates settings and options
		 * creates roles and assign capabilities
		 * @since WPAS 4.0
		 *
		 */
		public function install() {
			Emd_Contact::register();
			Emd_Basic_Calcs::register();
			flush_rewrite_rules();
			$this->set_options();
			$this->set_roles_caps();
			do_action('emd_ext_install_hook', $this->option_name);
		}
		/**
		 * Runs on plugin deactivate to remove options, caps and roles
		 * flushing rewrite rules
		 * @since WPAS 4.0
		 *
		 */
		public function deactivate() {
			flush_rewrite_rules();
			$this->remove_caps_roles();
			$this->reset_options();
			do_action('emd_ext_deactivate', $this->option_name);
		}
		/**
		 * Register notification and/or license settings
		 * @since WPAS 4.0
		 *
		 */
		public function register_settings() {
			do_action('emd_ext_register', $this->option_name);
		}
		/**
		 * Sets caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function set_roles_caps() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'add');
			}
		}
		/**
		 * Removes caps and roles
		 *
		 * @since WPAS 4.0
		 *
		 */
		public function remove_caps_roles() {
			global $wp_roles;
			if (class_exists('WP_Roles')) {
				if (!isset($wp_roles)) {
					$wp_roles = new WP_Roles();
				}
			}
			if (is_object($wp_roles)) {
				$this->set_reset_caps($wp_roles, 'remove');
			}
		}
		/**
		 * Set , reset capabilities
		 *
		 * @since WPAS 4.0
		 * @param object $wp_roles
		 * @param string $type
		 *
		 */
		public function set_reset_caps($wp_roles, $type) {
			$caps['enable'] = Array(
				'edit_emd_contacts' => Array(
					'administrator'
				) ,
				'edit_contact_tag' => Array(
					'administrator'
				) ,
				'manage_operations_emd_contacts' => Array(
					'administrator'
				) ,
				'delete_contact_tag' => Array(
					'administrator'
				) ,
				'edit_contact_category' => Array(
					'administrator'
				) ,
				'manage_operations_emd_basic_calcss' => Array(
					'administrator'
				) ,
				'assign_contact_category' => Array(
					'administrator'
				) ,
				'delete_contact_category' => Array(
					'administrator'
				) ,
				'edit_emd_basic_calcss' => Array(
					'administrator'
				) ,
				'view_margarita_dashboard' => Array(
					'administrator'
				) ,
				'manage_contact_category' => Array(
					'administrator'
				) ,
				'assign_contact_tag' => Array(
					'administrator'
				) ,
				'manage_contact_tag' => Array(
					'administrator'
				) ,
			);
			$caps['enable'] = apply_filters('emd_ext_get_caps', $caps['enable'], $this->option_name);
			foreach ($caps as $stat => $role_caps) {
				foreach ($role_caps as $mycap => $roles) {
					foreach ($roles as $myrole) {
						if (($type == 'add' && $stat == 'enable') || ($stat == 'disable' && $type == 'remove')) {
							$wp_roles->add_cap($myrole, $mycap);
						} else if (($type == 'remove' && $stat == 'enable') || ($type == 'add' && $stat == 'disable')) {
							$wp_roles->remove_cap($myrole, $mycap);
						}
					}
				}
			}
		}
		/**
		 * Set app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function set_options() {
			$access_views = Array();
			$ent_list = Array(
				'emd_contact' => Array(
					'label' => __('Contacts', 'margarita') ,
					'rewrite' => 'emd_contact',
					'sortable' => 0,
					'searchable' => 1,
					'unique_keys' => Array(
						'emd_contact_email'
					) ,
				) ,
				'emd_basic_calcs' => Array(
					'label' => __('Basic Calculations', 'margarita') ,
					'rewrite' => 'emd_basic_calcs',
					'sortable' => 0,
					'searchable' => 1,
					'unique_keys' => Array(
						'emd_bc_id'
					) ,
				) ,
			);
			update_option($this->option_name . '_ent_list', $ent_list);
			$shc_list['app'] = 'Margarita';
			$shc_list['has_bs'] = 0;
			if (!empty($shc_list)) {
				update_option($this->option_name . '_shc_list', $shc_list);
			}
			$attr_list['emd_contact']['emd_contact_first_name'] = Array(
				'visible' => 1,
				'label' => __('First Name', 'margarita') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'desc' => __('Contact\'s first name', 'margarita') ,
				'type' => 'char',
			);
			$attr_list['emd_contact']['emd_contact_last_name'] = Array(
				'visible' => 1,
				'label' => __('Last Name', 'margarita') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'desc' => __('Contact\'s last name', 'margarita') ,
				'type' => 'char',
			);
			$attr_list['emd_contact']['emd_contact_email'] = Array(
				'visible' => 1,
				'label' => __('Email', 'margarita') ,
				'display_type' => 'text',
				'required' => 1,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'desc' => __('Contact\'s email address', 'margarita') ,
				'type' => 'char',
				'email' => true,
				'uniqueAttr' => true,
			);
			$attr_list['emd_contact']['emd_contact_phone'] = Array(
				'visible' => 1,
				'label' => __('Phone', 'margarita') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'desc' => __('Contact\'s phone or mobile', 'margarita') ,
				'type' => 'char',
			);
			$attr_list['emd_contact']['emd_contact_address'] = Array(
				'visible' => 1,
				'label' => __('Address', 'margarita') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'desc' => __('Contact\'s mailing address.', 'margarita') ,
				'type' => 'char',
			);
			$attr_list['emd_contact']['emd_contact_time'] = Array(
				'visible' => 1,
				'label' => __('Contact Preference', 'margarita') ,
				'display_type' => 'select',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'desc' => __('When would you like to be contacted?', 'margarita') ,
				'type' => 'char',
				'options' => array(
					'' => __('Please Select', 'margarita') ,
					'morning' => __('morning', 'margarita') ,
					'afternoon' => __('afternoon', 'margarita') ,
					'evening' => __('evening', 'margarita') ,
					'night' => __('night', 'margarita')
				) ,
				'std' => 'afternoon',
			);
			$attr_list['emd_contact']['emd_contact_attach'] = Array(
				'visible' => 1,
				'label' => __('Contact Files', 'margarita') ,
				'display_type' => 'file',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'type' => 'char',
			);
			$attr_list['emd_contact']['emd_contact_location'] = Array(
				'visible' => 1,
				'label' => __('Residence', 'margarita') ,
				'display_type' => 'checkbox',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 1,
				'mid' => 'emd_contact_info_emd_contact_0',
				'desc' => __('Is this address your residence?', 'margarita') ,
				'type' => 'binary',
				'options' => array(
					1 => 1
				) ,
			);
			$attr_list['emd_basic_calcs']['emd_bc_id'] = Array(
				'visible' => 1,
				'label' => __('ID', 'margarita') ,
				'display_type' => 'hidden',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'autoinc_start' => 1,
				'autoinc_incr' => 1,
				'type' => 'char',
				'hidden_func' => 'autoinc',
				'uniqueAttr' => true,
			);
			$attr_list['emd_basic_calcs']['emd_bc_value1'] = Array(
				'visible' => 1,
				'label' => __('Value 1', 'margarita') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'type' => 'decimal',
				'number' => true,
				'data-cell' => 'A10',
			);
			$attr_list['emd_basic_calcs']['emd_bc_value2'] = Array(
				'visible' => 1,
				'label' => __('Value 2', 'margarita') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'type' => 'decimal',
				'number' => true,
				'data-cell' => 'A11',
			);
			$attr_list['emd_basic_calcs']['emd_bc_value3'] = Array(
				'visible' => 1,
				'label' => __('Value 3', 'margarita') ,
				'display_type' => 'text',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'type' => 'char',
				'data-cell' => 'A12',
			);
			$attr_list['emd_basic_calcs']['emd_bc_total'] = Array(
				'visible' => 1,
				'label' => __('Total', 'margarita') ,
				'display_type' => 'calculated',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'desc' => __('Sums up all values', 'margarita') ,
				'type' => 'char',
				'data-cell' => 'F13',
				'data-formula' => 'SERVER("EMD_BC_TOTAL",A10,A11,A12)',
			);
			$attr_list['emd_basic_calcs']['emd_bc_deduct'] = Array(
				'visible' => 1,
				'label' => __('Deduction', 'margarita') ,
				'display_type' => 'calculated',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'desc' => __('Sums up value 1 and value 2 then deducts value 3 from their total.', 'margarita') ,
				'type' => 'char',
				'data-cell' => 'F14',
				'data-formula' => 'SERVER("EMD_BC_DEDUCT",A10,A11,A12)',
			);
			$attr_list['emd_basic_calcs']['emd_bc_multiply'] = Array(
				'visible' => 1,
				'label' => __('Multiplication', 'margarita') ,
				'display_type' => 'calculated',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'desc' => __('Multiplies all 3 values', 'margarita') ,
				'type' => 'char',
				'data-cell' => 'F15',
				'data-formula' => 'SERVER("EMD_BC_MULTIPLY",A10,A11,A12)',
			);
			$attr_list['emd_basic_calcs']['emd_bc_division'] = Array(
				'visible' => 1,
				'label' => __('Division', 'margarita') ,
				'display_type' => 'calculated',
				'required' => 0,
				'srequired' => 0,
				'filterable' => 0,
				'list_visible' => 0,
				'mid' => 'emd_basic_calcs_info_emd_basic_calcs_0',
				'desc' => __('Multiplies value 1 and value 2 then divides the total by value 3', 'margarita') ,
				'type' => 'char',
				'data-cell' => 'F16',
				'data-formula' => 'SERVER("EMD_BC_DIVISION",A10,A11,A12)',
			);
			$attr_list = apply_filters('emd_ext_attr_list', $attr_list, $this->option_name);
			if (!empty($attr_list)) {
				update_option($this->option_name . '_attr_list', $attr_list);
			}
			if (!empty($glob_forms_list)) {
				update_option($this->option_name . '_glob_forms_init_list', $glob_forms_list);
				if (get_option($this->option_name . '_glob_forms_list') === false) {
					update_option($this->option_name . '_glob_forms_list', $glob_forms_list);
				}
			}
			$tax_list['emd_contact']['contact_category'] = Array(
				'label' => __('Contact Category', 'margarita') ,
				'default' => '',
				'type' => 'single',
				'hier' => 0,
				'sortable' => 0,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'contact_category'
			);
			$tax_list['emd_contact']['contact_tag'] = Array(
				'label' => __('Contact Tags', 'margarita') ,
				'default' => '',
				'type' => 'multi',
				'hier' => 0,
				'sortable' => 0,
				'required' => 0,
				'srequired' => 0,
				'rewrite' => 'contact_tag'
			);
			if (!empty($tax_list)) {
				update_option($this->option_name . '_tax_list', $tax_list);
			}
			//conf parameters for incoming email
			//conf parameters for inline entity
			//conf parameters for calendar
			//action to configure different extension conf parameters for this plugin
			do_action('emd_ext_set_conf', 'margarita');
		}
		/**
		 * Reset app specific options
		 *
		 * @since WPAS 4.0
		 *
		 */
		private function reset_options() {
			delete_option($this->option_name . '_ent_list');
			delete_option($this->option_name . '_shc_list');
			delete_option($this->option_name . '_attr_list');
			delete_option($this->option_name . '_tax_list');
			delete_option($this->option_name . '_rel_list');
			do_action('emd_ext_reset_conf', 'margarita');
		}
		/**
		 * Delete file attachments when a post is deleted
		 *
		 * @since WPAS 4.0
		 * @param $pid
		 *
		 * @return bool
		 */
		public function delete_post_file_att($pid) {
			$entity_fields = get_option($this->option_name . '_attr_list');
			$post_type = get_post_type($pid);
			if (!empty($entity_fields[$post_type])) {
				//Delete fields
				foreach (array_keys($entity_fields[$post_type]) as $myfield) {
					if (in_array($entity_fields[$post_type][$myfield]['display_type'], Array(
						'file',
						'image',
						'plupload_image',
						'thickbox_image'
					))) {
						$pmeta = get_post_meta($pid, $myfield);
						if (!empty($pmeta)) {
							foreach ($pmeta as $file_id) {
								wp_delete_attachment($file_id);
							}
						}
					}
				}
			}
			return true;
		}
	}
endif;
return new Margarita_Install_Deactivate();
