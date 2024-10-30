<?php
/**
 * Settings Functions
 *
 * @package     EMD
 * @copyright   Copyright (c) 2014,  Emarket Design
 * @since       WPAS 4.4
 */
if (!defined('ABSPATH')) exit;

add_action('emd_ext_register','emd_glob_register_settings');
add_action('emd_show_settings_page','emd_show_settings_page',1);
/**
 * Show settings page for global variables
 *
 * @param string $app
 * @since WPAS 4.4
 *
 * @return html page content
 */
if (!function_exists('emd_show_settings_page')) {
	function emd_show_settings_page($app){
		global $title;
		?>
		<div class="wrap">
		<h2><?php echo $title; ?></h2>
		<?php	
		$tabs['entity'] = __('Entities', 'emd_plugins');
		$new_tax_list = Array();
		$tax_list = get_option($app . '_tax_list');
		if(!empty($tax_list)){
			foreach($tax_list as $tax_ent => $tax){
				foreach($tax as $tax_key => $set_tax){
					if($set_tax['type'] != 'builtin'){
						$new_tax_list[$tax_ent][$tax_key] = $set_tax;			
					}
				}
			}
		}
		echo '<p>' . settings_errors($app . '_ent_map_list') . '</p>';
		if(!empty($new_tax_list)){	
			echo '<p>' . settings_errors($app . '_tax_settings') . '</p>';
			$tabs['taxonomy'] = __('Taxonomies', 'emd_plugins');
		}
		$tabs = apply_filters('emd_add_settings_tab',$tabs,$app);
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'entity';
		if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == true){
			echo '<div id="message" class="updated">' . __('Settings Saved.','emd-plugins') . '</div>';
		}
		echo '<h2 class="nav-tab-wrapper">';
		foreach ($tabs as $ktab => $mytab) {
			$tab_url[$ktab] = esc_url(add_query_arg(array(
							'tab' => $ktab
							)));
			$active = "";
			if ($active_tab == $ktab) {
				$active = "nav-tab-active";
			}
			echo '<a href="' . esc_url($tab_url[$ktab]) . '" class="nav-tab ' . $active . '" id="nav-' . $ktab . '">' . $mytab . '</a>';
		}
		echo '</h2>';
		emd_ent_map_tab($app,$active_tab);
		emd_tax_tab($app,$active_tab,$new_tax_list);
		do_action('emd_show_settings_tab',$app,$active_tab);
		echo '</div>';
	}
}
if (!function_exists('emd_glob_register_settings')) {
	function emd_glob_register_settings($app){
		register_setting($app . '_ent_map_list', $app . '_ent_map_list', 'emd_ent_map_sanitize');
		register_setting($app . '_tax_settings', $app . '_tax_settings', 'emd_tax_settings_sanitize');
	}
}
if (!function_exists('emd_ent_map_tab')) {
	function emd_ent_map_tab($app,$active_tab){
		$ent_map_list = get_option($app .'_ent_map_list',Array());
		?>
			<div class='tab-content' id='tab-entity' <?php if ( 'entity' != $active_tab ) { echo 'style="display:none;"'; } ?>>
			<?php	echo '<form method="post" action="options.php">';
		settings_fields($app .'_ent_map_list');
		//show entity rewrite url
		$ent_map_variables = Array();
		$attr_list = get_option($app . '_attr_list');
		$ent_list = get_option($app . '_ent_list');
		foreach($attr_list as $ent => $attr){
			foreach($attr as $kattr => $vattr){
				if($vattr['display_type'] == 'map'){
					$ent_map_variables[$kattr] = Array('ent'=>$ent,'label'=>$vattr['label'], 'ent_label'=>$ent_list[$ent]['label']);
				}
			}
		}
		$map_ents = Array();
		if(!empty($ent_map_variables)){
			foreach($ent_map_variables as $mkey => $mval){
				$map_ents[$mval['ent']]['label'] = $mval['ent_label'];
				$map_ents[$mval['ent']]['attrs'][] = $mkey;
			}
		}
		if(!empty($ent_list)){
			foreach($ent_list as $kent => $vent){
				if(empty($vent['rating_ent'])){
					if(empty($map_ents[$kent])){
						$map_ents[$kent]['label'] = $vent['label'];
					}
					$map_ents[$kent]['rewrite'] = '';
					if(!empty($vent['rewrite'])){
						$map_ents[$kent]['rewrite'] = $vent['rewrite'];
					}
				}
			}
		}
		echo '<input type="hidden" name="' . esc_attr($app) . '_ent_map_list[app]" id="' . esc_attr($app) . '_ent_map_list_app" value="' . $app . '">';
		echo '<div id="map-list" class="accordion-container"><ul class="outer-border">';
		foreach($map_ents as $kent => $myent){
			echo '<li id="' . esc_attr($kent) . '" class="control-section accordion-section">
				<h3 class="accordion-section-title hndle" tabindex="0">' . $myent['label'] . '</h3>';
			echo '<div class="accordion-section-content"><div class="inside">';
			echo '<table class="form-table"><tbody>';
			echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_rewrite'>";
			echo __('Base Slug','emd-plugins');
			echo '</label></th><td>';
			$rewrite = isset($ent_map_list[$kent]['rewrite']) ? $ent_map_list[$kent]['rewrite'] : $myent['rewrite'];
			echo "<input id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_rewrite' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][rewrite]' type='text' value='" . $rewrite ."'></input><p class='description'>" . __('Sets the custom base slug for single and archive records. After you update,  flush the rewrite rules by going to the Permalink Settings page. This works only if post name based permalink structure is selected.','emd-plugins') . "</p></td></tr>";
			//check if this entity supports custom fields and show all cust fields attached
			if(post_type_supports($kent,'custom-fields')){
				$ent_cname = str_replace(" ", "_",ucwords(str_replace("_"," ",$kent)));
				$ent_obj = new $ent_cname;
				if(!empty($ent_obj)){
					$cust_fields = $ent_obj->get_cust_fields(Array() , $kent);
					if(!empty($cust_fields)){
						echo "<tr><th scope='row'><label for='ent_map_list_" . $kent . "_cust_fields'>";
						echo __('Hide Custom Fields','emd-plugins');
						echo '</label></th><td>';
						foreach($cust_fields as $kcust => $ent_cust_field){
							echo "<input id='" . esc_attr($app) . "_ent_map_list_" . $kent . "_cust_fields' name='" . esc_attr($app) . "_ent_map_list[" . $kent . "][cust_fields][" . $kcust . "]' type='checkbox' value=1";
							if(isset($ent_map_list[$kent]['cust_fields'][$kcust])){
								echo " checked";
							}
							echo ">" . $ent_cust_field . "</input></br>";
						}
						echo "<p class='description'>" . __('Check the custom fields you would like to hide on the frontend.','emd-plugins') . "</p></td></tr>";
					}
				}
			}
			if(!empty($myent['attrs'])){
				emd_show_map_attrs($app,$myent,$ent_map_variables,$ent_map_list);	
			}
			echo '</tbody></table>';
			echo '</div></div></li>';
		}
		echo '</ul></div>';
		submit_button(); 
		echo '</form></div>';
	}
}
if (!function_exists('emd_show_map_attrs')) {
	function emd_show_map_attrs($app,$myent,$ent_map_variables,$ent_map_list){
		foreach($myent['attrs'] as $mattr){
			$mattr_key = $mattr;
			$mattr_val = $ent_map_variables[$mattr_key];
			echo '<tr>
				<th scope="row">
				<label for="' . $mattr_key . '">';
			echo $mattr_val['label']; 
			echo '</label>
				</th>
				<td>';
			$width = isset($ent_map_list[$mattr_key]['width']) ? $ent_map_list[$mattr_key]['width'] : '';
			$height = isset($ent_map_list[$mattr_key]['height']) ? $ent_map_list[$mattr_key]['height'] : '';
			$zoom = isset($ent_map_list[$mattr_key]['zoom']) ? $ent_map_list[$mattr_key]['zoom'] : '14';
			$marker = isset($ent_map_list[$mattr_key]['marker']) ? 'checked' : '';
			$load_info = isset($ent_map_list[$mattr_key]['load_info']) ? 'checked' : '';
			$map_type = isset($ent_map_list[$mattr_key]['map_type']) ? $ent_map_list[$mattr_key]['map_type'] : '';
			echo "<tr><th scope='row'></th><td><table><th scope='row'><label>" . __('Frontend Map Settings','emd-plugins') . "</th><td></td></tr>
				<th scope='row'><label for='ent_map_list_" . $mattr_key . "_width'>" . __('Width','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_width' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][width]' type='text' value='" . $width . "'></input><p class='description'>" . __('Sets the map width.You can use \'%\' or \'px\'. Default is 100%.','emd-plugins') . "</p></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_height'>" . __('Height','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_height' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][height]' type='text' value='" . $height ."'></input><p class='description'>" . __('Sets the map height. You can use \'px\'. Default is 480px.','emd-plugins') . "</p></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_zoom'>" . __('Zoom','emd-plugins') . "</th><td><select id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_zoom' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][zoom]'>";
			for($i=20;$i >=1;$i--){
				echo "<option value='" . $i . "'";
				if($zoom == $i){
					echo " selected";
				}
				echo ">" . $i . "</option>";
			}
			echo "</select></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_map_type'>" . __('Type','emd-plugins') . "</th><td><select id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_map_type' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][map_type]'>";
			$map_types = Array("ROADMAP","SATELLITE","HYBRID","TERRAIN");
			foreach($map_types as $mtype){
				echo "<option value='" . $mtype . "'";
				if($map_type == $mtype){
					echo " selected";
				}
				echo ">" . $mtype . "</option>";
			}
			echo "</select></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_marker'>" . __('Marker','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_marker' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][marker]' type='checkbox' value=1 $marker></input></td></tr>";
			echo "<tr><th scope='row'><label for='ent_map_list_" . $mattr_key . "_load_info'>" . __('Display Info Window on Page Load','emd-plugins') . "</th><td><input id='" . esc_attr($app) . "_ent_map_list_" . $mattr_key . "_load_info' name='" . esc_attr($app) . "_ent_map_list[" . $mattr_key . "][load_info]' type='checkbox' value=1 $load_info></input></td></tr>";
			echo "</div></td></tr></table></td></tr>";
			echo '</td>
				</tr>';
		}
	}
}
if (!function_exists('emd_ent_map_sanitize')) {
	function emd_ent_map_sanitize($input){
		$ent_map_list = get_option($input['app'] . '_ent_map_list');
		$map_keys = Array('rewrite','cust_fields','width','height','zoom','map_type','marker','load_info');
		foreach($input as $ikey => $vkey){
			if($ikey != 'app'){
				foreach($map_keys as $mkey){
					if(isset($vkey[$mkey])){
						$ent_map_list[$ikey][$mkey] = $vkey[$mkey];
					}
					elseif(!empty($ent_map_list[$ikey][$mkey])){
						unset($ent_map_list[$ikey][$mkey]);    
					}
				}
			}
		}
		return $ent_map_list;
	}
}
if (!function_exists('emd_get_attr_map')) {
	function emd_get_attr_map($app,$key,$marker_title,$info_window,$post_id=''){
		$ent_map_list = get_option(str_replace("-","_",$app) . '_ent_map_list');
		$args = Array();
		$marker = (!empty($ent_map_list[$key]['marker'])) ? true : false;
		$load_info = (!empty($ent_map_list[$key]['load_info'])) ? true : false;
		$zoom = (!empty($ent_map_list[$key]['zoom'])) ? (int) $ent_map_list[$key]['zoom'] : 14;
		$map_type = (!empty($ent_map_list[$key]['map_type'])) ? $ent_map_list[$key]['map_type'] : 'ROADMAP';
		$width = (!empty($ent_map_list[$key]['width'])) ? $ent_map_list[$key]['width'] : '100%'; // Map width, default is 640px. You can use '%' or 'px'
		$height = (!empty($ent_map_list[$key]['height'])) ? $ent_map_list[$key]['height'] : '480px'; // Map height, default is 480px. You can use '%' or 'px'
		
		$args = array(
				'type'	       => 'map',
				'zoom'         => $zoom,  // Map zoom, default is the value set in admin, and if it's omitted - 14
				'width'        => $width,
				'height'       => $height,
				// Map type, see https://developers.google.com/maps/documentation/javascript/reference#MapTypeId
				'mapTypeId'    => $map_type,
				'marker'       => $marker, // Display marker? Default is 'true',
				'load_info'    => $load_info
			);
		if($marker !== false && !empty($marker_title)){
			if($marker_title == 'emd_blt_title'){
				$args['marker_title'] = get_the_title($post_id); // Marker title when hover
			}
			else {	
				$args['marker_title'] = emd_mb_meta($marker_title,'',$post_id); // Marker title when hover
			}
		}
		if($marker !== false && !empty($info_window)){
			if($info_window == 'emd_blt_title'){
				$args['info_window'] = get_the_title($post_id); // Info window content, can be anything. HTML allowed.
			}
			else {
				$args['info_window'] = emd_mb_meta($info_window,'',$post_id); // Info window content, can be anything. HTML allowed.
			}
		}
		return emd_mb_meta($key,$args,$post_id);
	}
}
if (!function_exists('emd_tax_tab')) {
	function emd_tax_tab($app,$active_tab,$tax_list){
		if(!empty($tax_list)){
			$tax_settings = get_option($app .'_tax_settings',Array());
	?>
	<div class='tab-content' id='tab-taxonomy' <?php if ( 'taxonomy' != $active_tab ) { echo 'style="display:none;"'; } ?>>
		<?php	echo '<form method="post" action="options.php">';
			settings_fields($app .'_tax_settings');
			//show taxonomy rewrite url
			if(!empty($tax_list)){
				foreach($tax_list as $tent => $vtax){
					foreach($vtax as $ktax => $valtax){
						$tax_list_vals[$ktax]['rewrite'] = $ktax;
						if(!empty($valtax['rewrite'])){
							$tax_list_vals[$ktax]['rewrite'] = $valtax['rewrite'];
						}
						$tax_list_vals[$ktax]['label'] = $valtax['label'];
					}
				}
			}
			echo '<input type="hidden" name="' . esc_attr($app) . '_tax_settings[app]" id="' . esc_attr($app) . '_tax_settings_app" value="' . $app . '">';
			echo '<div id="tax-settings" class="accordion-container"><ul class="outer-border">';
			foreach($tax_list_vals as $ktax => $mytax){
				echo '<li id="' . esc_attr($ktax) . '" class="control-section accordion-section">
					<h3 class="accordion-section-title hndle" tabindex="0">' . $mytax['label'] . '</h3>';
				echo '<div class="accordion-section-content"><div class="inside">';
				echo '<table class="form-table"><tbody>';
				echo "<tr><th scope='row'><label for='tax_settings_" . $ktax . "_rewrite'>";
				echo __('Base Slug','emd-plugins');
				echo '</label></th><td>';
				$rewrite = isset($tax_settings[$ktax]['rewrite']) ? $tax_settings[$ktax]['rewrite'] : $mytax['rewrite'];
				echo "<input id='" . esc_attr($app) . "_tax_settings_" . $ktax . "_rewrite' name='" . esc_attr($app) . "_tax_settings[" . $ktax . "][rewrite]' type='text' value='" . $rewrite ."'></input><p class='description'>" . __('Sets the custom base slug for this taxonomy. After you update,  flush the rewrite rules by going to the Permalink Settings page.','emd-plugins') . "</p></td></tr>";
				echo '</tbody></table>';
				echo '</div></div></li>';
			}
			echo '</ul></div>';
			submit_button(); 
			echo '</form></div>';
		}
	}
}
if (!function_exists('emd_tax_settings_sanitize')) {
	function emd_tax_settings_sanitize($input){
		$tax_settings = get_option($input['app'] . '_tax_settings');
		$keys = Array('rewrite');
		foreach($input as $ikey => $vkey){
			if($ikey != 'app'){
				foreach($keys as $mkey){
					if(isset($vkey[$mkey])){
						$tax_settings[$ikey][$mkey] = $vkey[$mkey];
					}
					elseif(!empty($tax_settings[$ikey][$mkey])){
						unset($tax_settings[$ikey][$mkey]);    
					}
				}
			}
		}
		return $tax_settings;
	}
}
