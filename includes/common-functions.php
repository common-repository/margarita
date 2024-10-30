<?php
/**
 * Common Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       1.0
 */
if (!defined('ABSPATH')) exit;
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
global $wp_version;
/**
 * Generates hidden func value
 *
 * @since WPAS 4.0
 * @param string $hidden_func
 *
 * @return string $val
 */
if (!function_exists('emd_get_hidden_func')) {
	function emd_get_hidden_func($hidden_func,$cstring='',$pid=0) {
		$current_user = wp_get_current_user();
		$val = "";
		switch ($hidden_func) {
			case 'user_login':
				$val = $current_user->user_login;
			break;
			case 'user_email':
				$val = $current_user->user_email;
			break;
			case 'user_firstname':
				$val = $current_user->user_firstname;
			break;
			case 'user_lastname':
				$val = $current_user->user_lastname;
			break;
			case 'user_displayname':
				$val = $current_user->display_name;
			break;
			case 'user_id':
				$val = $current_user->ID;
			break;
			case 'date_mm_dd_yyyy':
				$val = date("Y-m-d");
			break;
			case 'date_dd_mm_yyyy':
				$val = date("Y-m-d");
			break;
			case 'current_year':
				$val = date("Y");
			break;
			case 'current_month':
				$val = date("F");
			break;
			case 'current_month_num':
				$val = date("m");
			break;
			case 'current_day':
				$val = date("d");
			break;
			case 'now':
				$val = date("Y-m-d H:i:s");
			break;
			case 'current_time':
				$val = date("H:i:s");
			break;
			case 'user_ip':
				$val = $_SERVER['REMOTE_ADDR'];
			break;
			case 'unique_id':
				$val = 'emd_uid';
			break;
			case 'autoinc':
				$val = 'emd_autoinc';
			break;
			case 'concat':
				if($pid != 0){
					$mypost = get_post($pid);
					$builtins = Array(
						'entity_id' => $mypost->ID,
						'title' => $mypost->post_title,
						'permalink' => get_permalink($mypost->ID),
						'edit_link' => get_edit_post_link($mypost->ID),
						'delete_link' => esc_url(add_query_arg('frontend', 'true', get_delete_post_link($mypost->ID))),
						'excerpt' => $mypost->post_excerpt,
						'content' => $mypost->post_content,
						'author_dispname' => get_the_author_meta('display_name',$mypost->post_author),
						'author_nickname' => get_the_author_meta('nickname',$mypost->post_author),
						'author_fname' => get_the_author_meta('first_name',$mypost->post_author),
						'author_lname' => get_the_author_meta('last_name',$mypost->post_author),
						'author_login' => get_the_author_meta('user_login',$mypost->post_author),
						'modified_datetime' => $mypost->post_modified,
						'created_datetime' => $mypost->post_date,
					);
					if (preg_match_all('/\!#([^}#]*)\#/', $cstring, $matches)) {
						foreach ($matches[1] as $match_tag) {
							if (preg_match('/^ent_/', $match_tag)) {
								$rmatch_tag = preg_replace('/^ent_/','emd_',$match_tag);
								$new = emd_mb_meta($rmatch_tag, array() , $mypost->ID);
								$cstring = str_replace('!#' . $match_tag . '#', $new, $cstring);
							}
							elseif (in_array($match_tag, array_keys($builtins))) {
								$cstring = str_replace('!#'.$match_tag.  '#',$builtins[$match_tag],$cstring);
							}
						}
					}
					$val = esc_attr($cstring);
				}
				elseif(!empty($cstring)){
					$val = esc_attr($cstring);
				}
				else {
					$val = 'emd_concat';
				}
			break;
		}
		if ($current_user->ID == 0 && $val == '') {
			$val = "Visitor";
		}
		return $val;
	}
}
/**
 * Sets wp query request for author and search on frontend
 * @since WPAS 4.8
 * @param string $app
 * @param string $input
 * @param object $query
 * @param string $type
 *
 * @return string $input
 */
if (!function_exists('emd_author_search_results')) {
	function emd_author_search_results($app,$input,$query,$type){
		global $wpdb;
		$glob_limit = $app . '_limit';
		$glob_orderby = $app . '_orderby';
		global $$glob_limit,$$glob_orderby;
		$has_limitby = get_option($app . "_has_limitby_cap");
		if ($has_limitby == 1) {
			$ent_list = get_option($app ."_ent_list");
			if(!empty($ent_list)){
				$set_types = Array();
				foreach($ent_list as $kent => $myent){
					if($type == 'search' && isset($myent['searchable']) && $myent['searchable'] == 1){
						$set_types[] = $kent;
					}
					elseif($type == 'author'){
						$set_types[] = $kent;
					}
				}
				if(!empty($set_types)){
					$pids = Array();
					$diff_pids = Array();
					$input_add = "";
					$auth_id = $query->query_vars['author'];
					$search = $query->query_vars['s'];
					foreach (array_values($set_types) as $ptype) {
						$pids = apply_filters('emd_limit_by', $pids, $app, $ptype, 'frontend');
						$diff_pids = array_diff($pids,Array('0'));	
						if(empty($pids)){
							$input_add .= " UNION (SELECT * FROM " . $wpdb->posts . " WHERE " . $wpdb->posts . ".post_type ='" . $ptype . "' AND " . $wpdb->posts . ".post_status = 'publish' AND ";
							if($type == 'author'){
								$input_add .=  $wpdb->posts . ".post_author=" . $auth_id . ")";
							}
							elseif($type == 'search'){
								$input_add .=  "(" . $wpdb->posts . ".post_title LIKE '%" . $search . "%' OR " . $wpdb->posts . ".post_content LIKE '%" . $search . "%'))";
							}
						}
						elseif(!empty($diff_pids)) {
							$pids_arr = "(" . implode(",",$pids) . ")";
							$input_add .= " UNION (SELECT * FROM " . $wpdb->posts . " WHERE " . $wpdb->posts . ".ID IN " . $pids_arr . " AND ";
							if($type == 'author'){
								$input_add .= $wpdb->posts . ".post_author=" . $auth_id . ")";
							}
							elseif($type == 'search'){
								$input_add .=  "(" . $wpdb->posts . ".post_title LIKE '%" . $search . "%') OR (" . $wpdb->posts . ".post_content LIKE '%" . $search . "%'))";
							}

						}
					}
					if(!empty($input_add)){
						$input = str_replace($$glob_limit,"",$input);
						$input = $input . $input_add .  " ORDER BY " . $$glob_orderby . " " . $$glob_limit;
					}
				}
			}
		}
		return $input;
	}
}

/**
 * Sets query parameters for author and search on frontend
 * Not used after WPAS 4.7 , Feb-01-2016, kept for backward comp.
 * @since WPAS 4.0
 * @param string $app
 * @param object $query
 * @param bool $has_limit_by
 *
 * @return object $query
 */
if (!function_exists('emd_limit_author_search')) {
	function emd_limit_author_search($app, $query, $has_limitby = 0) {
		$current_ptype = isset($query->query_vars['post_type']) ? $query->query_vars['post_type'] : Array();
		$cap_post_types = get_post_types();
		foreach ($cap_post_types as $ptype) {
			if (!in_array($ptype, Array(
				'attachment',
				'revision',
				'nav_menu_item'
			)) && current_user_can('edit_' . $ptype . 's')) {
				$set_types[] = $ptype;
			}
		}
		if (!empty($set_types)) {
			$latest_ptypes = array_unique(array_merge($current_ptype, $set_types));
			$query->set('post_type', array_values($latest_ptypes));
			if ($has_limitby == 1) {
				$pids = Array();
				foreach (array_values($latest_ptypes) as $ptype) {
					$pids = apply_filters('emd_limit_by', $pids, $app, $ptype, 'frontend');
				}
				$query->set('post__in', $pids);
			}
		}
		return $query;
	}
}
/**
 * Has_shortcode func if wp version is < 3.6
 *
 * @since WPAS 4.0
 * @param string $content
 * @param string $shc
 *
 * @return bool
 */
if (version_compare($wp_version, "3.6", "<") && !function_exists('has_shortcode')) {
	function has_shortcode($content, $shc) {
		global $shortcode_tags;
		if (array_key_exists($shc, $shortcode_tags)) {
			preg_match_all('/' . get_shortcode_regex() . '/s', $content, $matches, PREG_SET_ORDER);
			if (empty($matches)) return false;
			foreach ($matches as $shortcode) {
				if ($shc === $shortcode[2]) return true;
			}
		}
		return false;
	}
}
/**
 * Parse and replace template tags with values in email subject and messages
 *
 * @since WPAS 4.3
 * moved from notify-actions file
 *
 * @param string $app
 * @param string $message
 * @param string $pid
 *
 * @param string $message
 */
if (!function_exists('emd_parse_template_tags')) {
	function emd_parse_template_tags($app, $message, $pid, $last=1,$type='') {
		global $wpdb;
		$rel_list = get_option($app . "_rel_list");
		$mypost = get_post($pid);
		$permlink = get_permalink($pid);
		$access_views = get_option($app . "_access_views");
		if (!empty($access_views['single'])) {
			foreach ($access_views['single'] as $single) {
				if ($single['obj'] == $mypost->post_type) {
					$permlink = wp_login_url(esc_url(add_query_arg('fr_emd_notify',1,get_permalink($pid))));
				}
			}
		}
		if (in_array($mypost->post_status, Array(
			'pending',
			'draft'
		))) {
			$preview_link = add_query_arg('preview', 'true', get_permalink($pid));
			$permlink = wp_login_url(add_query_arg('fr_emd_notify',1,$preview_link));
		}

		if($type == 'rel'){
			$builtins = Array(
				$mypost->post_type . '_title' => $mypost->post_title,
				$mypost->post_type . '_permalink' => $permlink,
				$mypost->post_type . '_edit_link' => get_edit_post_link($pid) ,
				$mypost->post_type . '_delete_link' => esc_url(add_query_arg('frontend', 'true', get_delete_post_link($pid))) ,
				$mypost->post_type . '_excerpt' => $mypost->post_excerpt,
				$mypost->post_type . '_content' => $mypost->post_content,
				$mypost->post_type . '_author_dispname' => get_the_author_meta('display_name',$mypost->post_author),
				$mypost->post_type . '_author_nickname' => get_the_author_meta('nickname',$mypost->post_author),
				$mypost->post_type . '_author_fname' => get_the_author_meta('first_name',$mypost->post_author),
				$mypost->post_type . '_author_lname' => get_the_author_meta('last_name',$mypost->post_author),
				$mypost->post_type . '_author_login' => get_the_author_meta('user_login',$mypost->post_author),
				$mypost->post_type . '_author_bio' => get_the_author_meta('description',$mypost->post_author),
				$mypost->post_type . '_author_googleplus' => get_the_author_meta('googleplus',$mypost->post_author),
				$mypost->post_type . '_author_twitter' => get_the_author_meta('twitter',$mypost->post_author),
			);
		}
		else {
			$builtins = Array(
				'title' => $mypost->post_title,
				'permalink' => $permlink,
				'edit_link' => get_edit_post_link($pid) ,
				'delete_link' => esc_url(add_query_arg('frontend', 'true', get_delete_post_link($pid))) ,
				'excerpt' => $mypost->post_excerpt,
				'content' => $mypost->post_content,
				'author_dispname' => get_the_author_meta('display_name',$mypost->post_author),
				'author_nickname' => get_the_author_meta('nickname',$mypost->post_author),
				'author_fname' => get_the_author_meta('first_name',$mypost->post_author),
				'author_lname' => get_the_author_meta('last_name',$mypost->post_author),
				'author_login' => get_the_author_meta('user_login',$mypost->post_author),
				'author_bio' => get_the_author_meta('description',$mypost->post_author),
				'author_googleplus' => get_the_author_meta('googleplus',$mypost->post_author),
				'author_twitter' => get_the_author_meta('twitter',$mypost->post_author),
			);
		}

		$glob_list = get_option($app . "_glob_list");
		if(!empty($glob_list)){
			foreach($glob_list as $kglob => $vglob){
				$globs[$kglob] = emd_global_val($app,$kglob);
			}
		}
		
		$message = apply_filters('emd_ext_parse_tags',$message,$pid,$app);
		//first get each template tag
		if (preg_match_all('/\{([^}]*)\}/', $message, $matches)) {
			foreach ($matches[1] as $match_tag) {
				//replace if builtin
				if (in_array($match_tag, array_keys($builtins))) {
					$message = str_replace('{' . $match_tag . '}', $builtins[$match_tag], $message);
				} elseif (!empty($globs) && in_array($match_tag, array_keys($globs))) {
					$message = str_replace('{' . $match_tag . '}', $globs[$match_tag], $message);
				} elseif (preg_match('/^wpas_/', $match_tag)) {
					$message = str_replace('{' . $match_tag . '}', emd_mb_meta($match_tag, array() , $pid) , $message);
				} elseif (preg_match('/^emd_/', $match_tag)) {
					$new = emd_get_attr_val($app, $pid, $mypost->post_type, $match_tag,'notify');
					if($last == 1 || !empty($new)){
						$message = str_replace('{' . $match_tag . '}', $new, $message);
					}
				} elseif (preg_match('/^com_/', $match_tag)) {
					$new_match_tag = preg_replace('/^com_/', '', $match_tag);
					$mycomments = get_comments(array(
						'post_id' => $pid,
						'type' => $new_match_tag,
						'status'=> 'approve',
					));
					$new = '<ul class="commentlist">';
					foreach($mycomments as $mycomm){
						$new .= "<li><div>" . __("Author:","emd-plugins") . $mycomm->comment_author . "&nbsp;&nbsp;";
						$new .= get_comment_date(sprintf(__('%s \a\t %s', 'emd-plugins'),get_option('date_format'),get_option('time_format')),$mycomm->comment_ID) . "</div>";
						$com_content = str_replace("\n", "<br>", $mycomm->comment_content);	
						$new .= "<p>" . $com_content . "</p>";
						$new .= "</li>";
					}
					$new .= '</ul>';
					$message = str_replace('{' . $match_tag . '}', $new, $message);
				} elseif (preg_match('/^rel_/', $match_tag)) {
					$new_rel = "";
					$has_no_link = 0;
					$new_match_tag = preg_replace('/^rel_/', '', $match_tag);
					if(preg_match('/_nl$/', $match_tag)){
						$new_match_tag = preg_replace('/_nl$/', '', $new_match_tag);
						$has_no_link = 1;
					}
					$myrel = $rel_list['rel_' . $new_match_tag];
					$from_to = "from";
					$other = "to";
					if ($myrel['from'] == $mypost->post_type) {
						$from_to = "to";
						$other = "from";
					}
					$conns = $wpdb->get_results("SELECT p2p_" . $from_to . " as pid FROM {$wpdb->p2p} WHERE p2p_type='" . $new_match_tag . "' AND p2p_" . $other . "='" . $pid . "'", ARRAY_A);	
					if(!empty($conns)){
						foreach ($conns as $mycon) {
							$rpost = get_post($mycon['pid']);
							if($has_no_link == 1){
								$new_rel.= $rpost->post_title . ",";
							}
							else {
								$new_rel.= "<a href='" . get_permalink($mycon['pid']) . "'>" . $rpost->post_title . "</a>,";
							}
						}
						$new_rel = rtrim($new_rel, ",");
					}
					$message = str_replace('{' . $match_tag . '}', $new_rel, $message);
				} else {
					if (preg_match('/_nl$/', $match_tag)) {
						$new_match_tag = preg_replace('/_nl$/', '', $match_tag);
						$new = strip_tags(get_the_term_list($pid, $new_match_tag, '', ' ', ''));
					} else {
						$new = get_the_term_list($pid, $match_tag, '', ' ', '');
					}
					if (!is_wp_error($new)) {
						$message = str_replace('{' . $match_tag . '}', $new, $message);
					}
				}
			}
		}
		return $message;
	}
}
/**
 * Get attribute value by attribute type
 *
 * @since WPAS 4.3
 * moved from notify-actions file
 *
 * @param string $app
 * @param string $pid
 * @param string $ptype
 * @param string $attr_id
 *
 * @return string $val
 *
 */
if (!function_exists('emd_get_attr_val')) {
	function emd_get_attr_val($app, $pid, $ptype, $attr_id,$where='') {
		$attr_list = get_option($app . "_attr_list");
		$val = "";
		$mult = 0;
		if(!empty($attr_list[$ptype][$attr_id])){
			$attr = $attr_list[$ptype][$attr_id];
			$dtype = $attr['display_type'];
			if (isset($attr['multiple'])) {
				$mult = 1;
			}
			if ($dtype == 'checkbox_list' || $dtype == 'select' && $mult == 1) {
				$emd_mb_list = emd_mb_meta($attr_id, 'type=checkbox_list', $pid);
				if (!empty($emd_mb_list)) {
					$val = implode(', ', $emd_mb_list);
				}
			} elseif ($dtype == 'file') {
				$emd_mb_file = emd_mb_meta($attr_id, 'type=file', $pid);
				if (!empty($emd_mb_file)) {
					foreach ($emd_mb_file as $info) {
						$val.= "<a href='" . $info['url'] . "' title='" . $info['title'] . "'>" . $info['name'] . "</a><br/>";
					}
				}
			} elseif (in_array($dtype, Array(
				'image',
				'plupload_image',
				'thickbox_image'
			))) {
				$images = emd_mb_meta($attr_id, 'type=plupload_image', $pid);
				if (!empty($images)) {
					foreach ($images as $image) {
						$val .= "<img src='" . $image['url'] . "' width='" . $image['width'] . "' height='" . $image['height'] . "' alt='" . $image['alt'] . "'/>";
						if(!empty($where) && $where == 'notify'){
							break;
						}	
					}
				}
			} elseif (in_array($dtype, Array(
				'date',
				'datetime',
				'time'
			))) {
				$val = emd_translate_date_format($attr, emd_mb_meta($attr_id, array() , $pid) , 1);
			} else {
				$val = emd_mb_meta($attr_id, array() , $pid);
			}
		}
		return $val;
	}
}
/**
 * Operator list for search moved from form-functions
 *
 * @since WPAS 4.3 
 * @param string $opr
 *
 * @return string $operator
 */
if (!function_exists('emd_get_meta_operator')) {
	function emd_get_meta_operator($opr) {
		$operators['is'] = '=';
		$operators['is_not'] = '!=';
		$operators['like'] = 'LIKE';
		$operators['not_like'] = 'NOT LIKE';
		$operators['less_than'] = '<';
		$operators['greater_than'] = '>';
		$operators['less_than_eq'] = '<=';
		$operators['greater_than_eq'] = '>=';
		$operators['begins'] = 'REGEXP';
		$operators['ends'] = 'REGEXP';
		$operators['word'] = 'REGEXP';
		return $operators[$opr];
	}
}
if (!function_exists('emd_check_unique')) {
	add_action('wp_ajax_emd_check_unique','emd_check_unique');
	/**
	 * Check unique keys
	 *
	 * @since WPAS 4.0
	 *
	 * @return bool $response
	 */
	function emd_check_unique() {
		$response = false;
		$post_id = '';
		$form_data = isset($_GET['data_input']) ? $_GET['data_input'] : '';
		$post_type = isset($_GET['ptype']) ? $_GET['ptype'] : '';
		$myapp = isset($_GET['myapp']) ? $_GET['myapp'] : '';
		$ent_list = get_option($myapp . "_ent_list");
		$ent_attrs = get_option($myapp . "_attr_list");
		$uniq_fields = $ent_list[$post_type]['unique_keys'];

		if(!is_array($form_data)){
			parse_str(stripslashes($form_data),$form_arr);
		}
		else {
			$form_arr = $form_data;
		}
		$title_set = 0;
		foreach ($form_arr as $fkey => $myform_field) {
			if($fkey == 'blt_title'){
				$title_set = 1;
				$data['blt_title'] = $myform_field;
			}	
			elseif($fkey == 'post_ID'){
				$post_id = $myform_field;
			}
			elseif(in_array($fkey, $uniq_fields)) {
				$data[$fkey] = emd_translate_date_format($ent_attrs[$post_type][$fkey], $myform_field, 0);
			}
		}
		if (!empty($data) && !empty($post_type)) {
			$response = emd_check_uniq_from_wpdb($data, $post_id, $post_type, $title_set);
		}
		echo $response;
		die();
	}
}
/**
 * Sql query to check unique keys
 *
 * @since WPAS 4.0
 *
 * @return bool $response
 */
if (!function_exists('emd_check_uniq_from_wpdb')) {
	function emd_check_uniq_from_wpdb($data, $post_id, $post_type, $title_set= 0) {
		global $wpdb;
		$where = "";
		$where_last = "";
		$join = "";
		$count = 1;
		foreach ($data as $key => $val) {
			if($key == 'blt_title'){
				$where_last = " AND p.post_title='" . $val . "'";
			}
			else {
				$join.= " LEFT JOIN " . $wpdb->postmeta . " pm" . $count . " ON p.ID = pm" . $count . ".post_id";
				$where.= " pm" . $count . ".meta_key='" . $key . "' AND pm" . $count . ".meta_value='" . $val . "' AND ";
				$count++;
			}
		}
		$where = rtrim($where, "AND");
		if(empty($join)){
			$where_last .= " AND p.ID != '" . $post_id . "'";
		}
		$result_arr = $wpdb->get_results("SELECT p.ID FROM " . $wpdb->posts . " p " . $join . " WHERE " . $where . " p.post_type = '" . $post_type . "'" . $where_last, ARRAY_A);
		if (empty($result_arr)) {
			return true;
		} elseif (!empty($post_id) && $result_arr[0]['ID'] == $post_id) {
			return true;
		}
		return false;
	}
}
/**
 * Show insert into post button for media uploads
 *
 * @since WPAS 4.5
 * @param array $args
 * @return array $args
 *
 */
if (!function_exists('emd_media_item_args')) {
	function emd_media_item_args($args){
		$args['send'] = true;
		return $args;
	}
}
/**
 * Parse set filter for form shortcodes
 *
 * @since WPAS 4.10
 * @param string $set
 * @return array $ret_sets
 *
 */
if (!function_exists('emd_parse_set_filter')) {
	function emd_parse_set_filter($set){
		$set_list = explode(";",$set);
		$set_rels = Array();
		$set_attrs = Array();
		$set_taxs = Array();

		foreach($set_list as $aset){
			$aset_arr = explode("::",$aset);
			if($aset_arr[0] == 'rel'){
				$set_rels[$aset_arr[1]] = $aset_arr[3];
			}
			if($aset_arr[0] == 'attr'){
				$set_attrs[$aset_arr[1]] = $aset_arr[3];
			}
			if($aset_arr[0] == 'tax'){
				$set_taxs[$aset_arr[1]] = $aset_arr[3];
			}
		}
		$ret_sets['rel'] = $set_rels;
		$ret_sets['attr'] = $set_attrs;
		$ret_sets['tax'] = $set_taxs;
		return $ret_sets;
	}
}
if (!function_exists('emd_get_tax_vals')) {
	function emd_get_tax_vals($pid,$txn_name,$nolink = 0){
		$term_list = get_the_term_list($pid,$txn_name,'',' ','');
		if(empty($term_list)){
			return '';
		}	
		if($nolink == 1){
			return strip_tags($term_list);
		}	
		return $term_list;		
	}
}
if (!function_exists('emd_get_tax_slugs')) {
	function emd_get_tax_slugs($pid,$txn_name){
		$slugs = '';
		$term_list = get_the_terms($pid,$txn_name);
		if(!empty($term_list)){
			foreach($term_list as $term){
				 $slugs .= esc_attr($term->slug);
			}
		}
		return $slugs;
	}
}
if (!function_exists('emd_post_exists')) {
	function emd_post_exists($title, $type) {
		global $wpdb;

		$post_title = wp_unslash(sanitize_post_field('post_title', $title, 0, 'db'));
		$post_type = wp_unslash(sanitize_post_field('post_type', $type, 0, 'db'));

		$query = "SELECT ID FROM $wpdb->posts WHERE 1=1";
		$args = array();
		
		if(!empty($title)) {
			$query .= ' AND post_title = %s';
			$args[] = $post_title;
		}
	 
		if(!empty($type)) {
		     $query .= ' AND post_type = %s ';
		     $args[] = $post_type;
		}

		if(!empty($args))
			return (int) $wpdb->get_var($wpdb->prepare($query, $args));

		return 0;
	}
}
