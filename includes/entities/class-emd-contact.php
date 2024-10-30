<?php
/**
 * Entity Class
 *
 * @package MARGARITA
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
/**
 * Emd_Contact Class
 * @since WPAS 4.0
 */
class Emd_Contact extends Emd_Entity {
	protected $post_type = 'emd_contact';
	protected $textdomain = 'margarita';
	protected $sing_label;
	protected $plural_label;
	protected $menu_entity;
	/**
	 * Initialize entity class
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function __construct() {
		add_action('init', array(
			$this,
			'set_filters'
		) , 1);
		add_action('admin_init', array(
			$this,
			'set_metabox'
		));
		add_action('save_post', array(
			$this,
			'change_title'
		) , 99, 2);
		add_filter('post_updated_messages', array(
			$this,
			'updated_messages'
		));
		add_action('manage_emd_contact_posts_custom_column', array(
			$this,
			'custom_columns'
		) , 10, 2);
		add_filter('manage_emd_contact_posts_columns', array(
			$this,
			'column_headers'
		));
		do_action('emd_ext_class_init', $this);
	}
	/**
	 * Get column header list in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param array $columns
	 *
	 * @return array $columns
	 */
	public function column_headers($columns) {
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if (!in_array($fkey, Array(
					'wpas_form_name',
					'wpas_form_submitted_by',
					'wpas_form_submitted_ip'
				)) && !in_array($mybox_field['type'], Array(
					'textarea',
					'wysiwyg'
				)) && $mybox_field['list_visible'] == 1) {
					$columns[$fkey] = $mybox_field['name'];
				}
			}
		}
		$taxonomies = get_object_taxonomies($this->post_type, 'objects');
		if (!empty($taxonomies)) {
			foreach ($taxonomies as $taxonomy) {
				$columns[$taxonomy->name] = $taxonomy->label;
			}
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list)) {
			foreach ($rel_list as $krel => $rel) {
				if ($rel['from'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'from'
				))) {
					$columns[$krel] = $rel['from_title'];
				} elseif ($rel['to'] == $this->post_type && in_array($rel['show'], Array(
					'any',
					'to'
				))) {
					$columns[$krel] = $rel['to_title'];
				}
			}
		}
		return $columns;
	}
	/**
	 * Get custom column values in admin list pages
	 * @since WPAS 4.0
	 *
	 * @param int $column_id
	 * @param int $post_id
	 *
	 * @return string $value
	 */
	public function custom_columns($column_id, $post_id) {
		if (taxonomy_exists($column_id) == true) {
			$terms = get_the_terms($post_id, $column_id);
			$ret = array();
			if (!empty($terms)) {
				foreach ($terms as $term) {
					$url = add_query_arg(array(
						'post_type' => $this->post_type,
						'term' => $term->slug,
						'taxonomy' => $column_id
					) , admin_url('edit.php'));
					$a_class = preg_replace('/^emd_/', '', $this->post_type);
					$ret[] = sprintf('<a href="%s"  class="' . $a_class . '-tax ' . $term->slug . '">%s</a>', $url, $term->name);
				}
			}
			echo implode(', ', $ret);
			return;
		}
		$rel_list = get_option(str_replace("-", "_", $this->textdomain) . '_rel_list');
		if (!empty($rel_list) && !empty($rel_list[$column_id])) {
			$rel_arr = $rel_list[$column_id];
			if ($rel_arr['from'] == $this->post_type) {
				$other_ptype = $rel_arr['to'];
			} elseif ($rel_arr['to'] == $this->post_type) {
				$other_ptype = $rel_arr['from'];
			}
			$column_id = str_replace('rel_', '', $column_id);
			if (function_exists('p2p_type') && p2p_type($column_id)) {
				$rel_args = apply_filters('emd_ext_p2p_add_query_vars', array(
					'posts_per_page' => - 1
				) , Array(
					$other_ptype
				));
				$connected = p2p_type($column_id)->get_connected($post_id, $rel_args);
				$ptype_obj = get_post_type_object($this->post_type);
				$edit_cap = $ptype_obj->cap->edit_posts;
				$ret = array();
				if (empty($connected->posts)) return '&ndash;';
				foreach ($connected->posts as $myrelpost) {
					$rel_title = get_the_title($myrelpost->ID);
					$rel_title = apply_filters('emd_ext_p2p_connect_title', $rel_title, $myrelpost, '');
					$url = get_permalink($myrelpost->ID);
					$url = apply_filters('emd_ext_connected_ptype_url', $url, $myrelpost, $edit_cap);
					$ret[] = sprintf('<a href="%s" title="%s" target="_blank">%s</a>', $url, $rel_title, $rel_title);
				}
				echo implode(', ', $ret);
				return;
			}
		}
		$value = get_post_meta($post_id, $column_id, true);
		$type = "";
		foreach ($this->boxes as $mybox) {
			foreach ($mybox['fields'] as $fkey => $mybox_field) {
				if ($fkey == $column_id) {
					$type = $mybox_field['type'];
					break;
				}
			}
		}
		switch ($type) {
			case 'plupload_image':
			case 'image':
			case 'thickbox_image':
				$image_list = emd_mb_meta($column_id, 'type=image');
				$value = "";
				if (!empty($image_list)) {
					$myimage = current($image_list);
					$value = "<img style='max-width:100%;height:auto;' src='" . $myimage['url'] . "' >";
				}
			break;
			case 'user':
			case 'user-adv':
				$user_id = emd_mb_meta($column_id);
				if (!empty($user_id)) {
					$user_info = get_userdata($user_id);
					$value = $user_info->display_name;
				}
			break;
			case 'file':
				$file_list = emd_mb_meta($column_id, 'type=file');
				if (!empty($file_list)) {
					$value = "";
					foreach ($file_list as $myfile) {
						$fsrc = wp_mime_type_icon($myfile['ID']);
						$value.= "<a href='" . $myfile['url'] . "' target='_blank'><img src='" . $fsrc . "' title='" . $myfile['name'] . "' width='20' /></a>";
					}
				}
			break;
			case 'checkbox_list':
				$checkbox_list = emd_mb_meta($column_id, 'type=checkbox_list');
				if (!empty($checkbox_list)) {
					$value = implode(', ', $checkbox_list);
				}
			break;
			case 'select':
			case 'select_advanced':
				$select_list = get_post_meta($post_id, $column_id, false);
				if (!empty($select_list)) {
					$value = implode(', ', $select_list);
				}
			break;
			case 'checkbox':
				if ($value == 1) {
					$value = '<span class="dashicons dashicons-yes"></span>';
				} elseif ($value == 0) {
					$value = '<span class="dashicons dashicons-no-alt"></span>';
				}
			break;
			case 'rating':
				$value = apply_filters('emd_get_rating_value', $value, Array(
					'meta' => $column_id
				) , $post_id);
			break;
		}
		if (is_array($value)) {
			$value = "<div class='clonelink'>" . implode("</div><div class='clonelink'>", $value) . "</div>";
		}
		echo $value;
	}
	/**
	 * Register post type and taxonomies and set initial values for taxs
	 *
	 * @since WPAS 4.0
	 *
	 */
	public static function register() {
		$labels = array(
			'name' => __('Contacts', 'margarita') ,
			'singular_name' => __('Contact', 'margarita') ,
			'add_new' => __('Add New', 'margarita') ,
			'add_new_item' => __('Add New Contact', 'margarita') ,
			'edit_item' => __('Edit Contact', 'margarita') ,
			'new_item' => __('New Contact', 'margarita') ,
			'all_items' => __('All Contacts', 'margarita') ,
			'view_item' => __('View Contact', 'margarita') ,
			'search_items' => __('Search Contacts', 'margarita') ,
			'not_found' => __('No Contacts Found', 'margarita') ,
			'not_found_in_trash' => __('No Contacts Found In Trash', 'margarita') ,
			'menu_name' => __('Contacts', 'margarita') ,
		);
		$ent_map_list = get_option('margarita_ent_map_list', Array());
		if (!empty($ent_map_list['emd_contact']['rewrite'])) {
			$rewrite = $ent_map_list['emd_contact']['rewrite'];
		} else {
			$rewrite = 'emd_contact';
		}
		register_post_type('emd_contact', array(
			'labels' => $labels,
			'public' => true,
			'publicly_queryable' => true,
			'show_ui' => true,
			'description' => __('', 'margarita') ,
			'show_in_menu' => true,
			'menu_position' => 7,
			'has_archive' => true,
			'exclude_from_search' => false,
			'rewrite' => array(
				'slug' => $rewrite
			) ,
			'can_export' => true,
			'show_in_rest' => false,
			'hierarchical' => false,
			'menu_icon' => 'dashicons-groups',
			'map_meta_cap' => 'false',
			'taxonomies' => array() ,
			'capability_type' => 'post',
			'supports' => array(
				'nothingselected'
			)
		));
		$contact_tag_nohr_labels = array(
			'name' => __('Contact Tags', 'margarita') ,
			'singular_name' => __('Contact Tag', 'margarita') ,
			'search_items' => __('Search Contact Tags', 'margarita') ,
			'popular_items' => __('Popular Contact Tags', 'margarita') ,
			'all_items' => __('All', 'margarita') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Contact Tag', 'margarita') ,
			'update_item' => __('Update Contact Tag', 'margarita') ,
			'add_new_item' => __('Add New Contact Tag', 'margarita') ,
			'new_item_name' => __('Add New Contact Tag Name', 'margarita') ,
			'separate_items_with_commas' => __('Seperate Contact Tags with commas', 'margarita') ,
			'add_or_remove_items' => __('Add or Remove Contact Tags', 'margarita') ,
			'choose_from_most_used' => __('Choose from the most used Contact Tags', 'margarita') ,
			'menu_name' => __('Contact Tags', 'margarita') ,
		);
		$tax_settings = get_option('margarita_tax_settings', Array());
		if (!empty($tax_settings['contact_tag']['rewrite'])) {
			$rewrite = $tax_settings['contact_tag']['rewrite'];
		} else {
			$rewrite = 'contact_tag';
		}
		register_taxonomy('contact_tag', array(
			'emd_contact'
		) , array(
			'hierarchical' => false,
			'labels' => $contact_tag_nohr_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => $rewrite,
			) ,
			'capabilities' => array(
				'manage_terms' => 'manage_contact_tag',
				'edit_terms' => 'edit_contact_tag',
				'delete_terms' => 'delete_contact_tag',
				'assign_terms' => 'assign_contact_tag'
			) ,
		));
		$contact_category_nohr_labels = array(
			'name' => __('Contact Category', 'margarita') ,
			'singular_name' => __('Contact Category', 'margarita') ,
			'search_items' => __('Search Contact Category', 'margarita') ,
			'popular_items' => __('Popular Contact Category', 'margarita') ,
			'all_items' => __('All', 'margarita') ,
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __('Edit Contact Category', 'margarita') ,
			'update_item' => __('Update Contact Category', 'margarita') ,
			'add_new_item' => __('Add New Contact Category', 'margarita') ,
			'new_item_name' => __('Add New Contact Category Name', 'margarita') ,
			'separate_items_with_commas' => __('Seperate Contact Category with commas', 'margarita') ,
			'add_or_remove_items' => __('Add or Remove Contact Category', 'margarita') ,
			'choose_from_most_used' => __('Choose from the most used Contact Category', 'margarita') ,
			'menu_name' => __('Contact Category', 'margarita') ,
		);
		$tax_settings = get_option('margarita_tax_settings', Array());
		if (!empty($tax_settings['contact_category']['rewrite'])) {
			$rewrite = $tax_settings['contact_category']['rewrite'];
		} else {
			$rewrite = 'contact_category';
		}
		register_taxonomy('contact_category', array(
			'emd_contact'
		) , array(
			'hierarchical' => false,
			'labels' => $contact_category_nohr_labels,
			'public' => true,
			'show_ui' => true,
			'show_in_nav_menus' => true,
			'show_in_menu' => true,
			'show_tagcloud' => true,
			'update_count_callback' => '_update_post_term_count',
			'query_var' => true,
			'rewrite' => array(
				'slug' => $rewrite,
			) ,
			'capabilities' => array(
				'manage_terms' => 'manage_contact_category',
				'edit_terms' => 'edit_contact_category',
				'delete_terms' => 'delete_contact_category',
				'assign_terms' => 'assign_contact_category'
			) ,
		));
		if (!get_option('margarita_emd_contact_terms_init')) {
			$set_tax_terms = Array(
				Array(
					'name' => __('friend', 'margarita') ,
					'slug' => sanitize_title('friend')
				) ,
				Array(
					'name' => __('business', 'margarita') ,
					'slug' => sanitize_title('business')
				) ,
				Array(
					'name' => __('family', 'margarita') ,
					'slug' => sanitize_title('family')
				) ,
				Array(
					'name' => __('spouse', 'margarita') ,
					'slug' => sanitize_title('spouse')
				) ,
				Array(
					'name' => __('child', 'margarita') ,
					'slug' => sanitize_title('child')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'contact_tag');
			$set_tax_terms = Array(
				Array(
					'name' => __('internal', 'margarita') ,
					'slug' => sanitize_title('internal')
				) ,
				Array(
					'name' => __('external', 'margarita') ,
					'slug' => sanitize_title('external')
				)
			);
			self::set_taxonomy_init($set_tax_terms, 'contact_category');
			update_option('margarita_emd_contact_terms_init', true);
		}
	}
	/**
	 * Set metabox fields,labels,filters, comments, relationships if exists
	 *
	 * @since WPAS 4.0
	 *
	 */
	public function set_filters() {
		$search_args = Array();
		$filter_args = Array();
		$this->sing_label = __('Contact', 'margarita');
		$this->plural_label = __('Contacts', 'margarita');
		$this->menu_entity = 'emd_contact';
		$this->boxes['emd_contact_info_emd_contact_0'] = array(
			'id' => 'emd_contact_info_emd_contact_0',
			'title' => __('Contact Info', 'margarita') ,
			'app_name' => 'margarita',
			'pages' => array(
				'emd_contact'
			) ,
			'context' => 'normal',
		);
		list($search_args, $filter_args) = $this->set_args_boxes();
		if (!post_type_exists($this->post_type) || in_array($this->post_type, Array(
			'post',
			'page'
		))) {
			self::register();
		}
	}
	/**
	 * Initialize metaboxes
	 * @since WPAS 4.5
	 *
	 */
	public function set_metabox() {
		if (class_exists('EMD_Meta_Box') && is_array($this->boxes)) {
			foreach ($this->boxes as $meta_box) {
				new EMD_Meta_Box($meta_box);
			}
		}
	}
	/**
	 * Change content for created frontend views
	 * @since WPAS 4.0
	 * @param string $content
	 *
	 * @return string $content
	 */
	public function change_content($content) {
		global $post;
		$layout = "";
		if (get_post_type() == $this->post_type && is_single()) {
			ob_start();
			emd_get_template_part($this->textdomain, 'single', 'emd-contact');
			$layout = ob_get_clean();
		}
		if ($layout != "") {
			$content = $layout;
		}
		return $content;
	}
	/**
	 * Add operations and add new submenu hook
	 * @since WPAS 4.4
	 */
	public function add_menu_link() {
		add_submenu_page(null, __('Operations', 'margarita') , __('Operations', 'margarita') , 'manage_operations_emd_contacts', 'operations_emd_contact', array(
			$this,
			'get_operations'
		));
	}
	/**
	 * Display operations page
	 * @since WPAS 4.0
	 */
	public function get_operations() {
		if (current_user_can('manage_operations_emd_contacts')) {
			$myapp = str_replace("-", "_", $this->textdomain);
			do_action('emd_operations_entity', $this->post_type, $this->plural_label, $this->sing_label, $myapp, $this->menu_entity);
		}
	}
}
new Emd_Contact;
