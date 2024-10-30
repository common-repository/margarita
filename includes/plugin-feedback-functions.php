<?php
/**
 * Plugin Page Feedback Functions
 *
 * @package MARGARITA
 * @since WPAS 5.3
 */
if (!defined('ABSPATH')) exit;
add_filter('plugin_row_meta', 'margarita_plugin_row_meta', 10, 2);
add_filter('plugin_action_links', 'margarita_plugin_action_links', 10, 2);
add_action('wp_ajax_margarita_send_deactivate_reason', 'margarita_send_deactivate_reason');
global $pagenow;
if ('plugins.php' === $pagenow) {
	add_action('admin_footer', 'margarita_deactivation_feedback_box');
}
add_action('wp_ajax_margarita_show_rateme', 'margarita_show_rateme_action');
//check min entity count if its not -1 then show notice
$min_trigger = get_option('margarita_show_rateme_plugin_min', 5);
if ($min_trigger != - 1) {
	add_action('admin_notices', 'margarita_show_rateme_notice');
}
function margarita_show_rateme_action() {
	if (!wp_verify_nonce($_POST['rateme_nonce'], 'margarita_rateme_nonce')) {
		exit;
	}
	$min_trigger = get_option('margarita_show_rateme_plugin_min', 5);
	if ($min_trigger == - 1) {
		die;
	}
	if (5 === $min_trigger) {
		$min_trigger = 10;
	} else {
		$min_trigger = - 1;
	}
	update_option('margarita_show_rateme_plugin_min', $min_trigger);
	echo 1;
	die;
}
function margarita_show_rateme_notice() {
	if (!current_user_can('manage_options')) {
		return;
	}
	$min_count = 0;
	$ent_list = get_option('margarita_ent_list');
	$min_trigger = get_option('margarita_show_rateme_plugin_min', 5);
	$count_posts = wp_count_posts('emd_contact');
	if ($count_posts->publish > $min_trigger) {
		$min_count = $count_posts->publish;
		$label = $ent_list['emd_contact']['label'];
	}
	if ($min_count > 5) {
		$message_start = '<div class="emd-show-rateme update-nag success">
                        <span class=""><b>Margarita</b></span>
                        <div>';
		$message_start.= sprintf(__("Hi, I noticed you just crossed the %d %s on Margarita - that's awesome!", "margarita") , $min_trigger, $label);
		$message_level1 = __("Could you please do me a BIG favor and give it a 5-star rating on WordPress? Just to help us spread the word and boost our motivation.", "margarita");
		$message_level2 = sprintf(__("Would you like to upgrade now to get more out of your %s?", "margarita") , $label);
		$message_end = '<br/><br/>
                        <strong><em>Safiye Duman</em></strong>
                        </div>
                        <ul data-nonce="' . wp_create_nonce('margarita_rateme_nonce') . '">';
		$message_end1 = '<li><a data-rate-action="do-rate" data-plugin="margarita" href="https://wordpress.org/support/plugin/margarita/reviews/#postform">' . __('Ok, you deserve it', 'margarita') . '</a>
       </li>
        <li><a data-rate-action="done-rating" data-plugin="margarita" href="#">' . __('I already did', 'margarita') . '</a></li>
        <li><a data-rate-action="not-enough" data-plugin="margarita" href="#">' . __('Maybe later', 'margarita') . '</a></li>';
		$message_end2 = '<li><a data-rate-action="upgrade-now" data-plugin="margarita" href="https://wpappstudio.com/wp-app-studio-pricing/">' . __('I want to upgrade', 'margarita') . '</a>
       </li>
        <li><a data-rate-action="not-enough" data-plugin="margarita" href="#">' . __('Maybe later', 'margarita') . '</a></li>';
	}
	if ($min_count > 10 && $min_trigger == 10) {
		echo $message_start . ' ' . $message_level2 . ' ' . $message_end . ' ' . $message_end2 . '</ul></div>';
	} elseif ($min_count > 5) {
		echo $message_start . ' ' . $message_level1 . ' ' . $message_end . ' ' . $message_end1 . '</ul></div>';
	}
}
/**
 * Adds links under plugin description
 *
 * @since WPAS 5.3
 * @param array $input
 * @param string $file
 * @return array $input
 */
function margarita_plugin_row_meta($input, $file) {
	if ($file != 'margarita/margarita.php') return $input;
	$links = array(
		'<a href="https://wpappstudio.com/features">' . __('Docs', 'margarita') . '</a>',
		'<a href="https://wpappstudio.com/wp-app-studio-pricing/">' . __('Pro Version', 'margarita') . '</a>'
	);
	$input = array_merge($input, $links);
	return $input;
}
/**
 * Adds links under plugin description
 *
 * @since WPAS 5.3
 * @param array $input
 * @param string $file
 * @return array $input
 */
function margarita_plugin_action_links($links, $file) {
	if ($file != 'margarita/margarita.php') return $links;
	foreach ($links as $key => $link) {
		if ('deactivate' === $key) {
			$links[$key] = $link . '<i class="margarita-deactivate-slug" data-slug="margarita-deactivate-slug"></i>';
		}
	}
	$new_links['settings'] = '<a href="' . admin_url('admin.php?page=margarita_settings') . '">' . __('Settings', 'margarita') . '</a>';
	$links = array_merge($new_links, $links);
	return $links;
}
function margarita_deactivation_feedback_box() {
	wp_enqueue_style("emd-plugin-modal", MARGARITA_PLUGIN_URL . 'assets/css/emd-plugin-modal.css');
	$feedback_vars['submit'] = __('Submit & Deactivate', 'margarita');
	$feedback_vars['skip'] = __('Skip & Deactivate', 'margarita');
	$feedback_vars['cancel'] = __('Cancel', 'margarita');
	$feedback_vars['ask_reason'] = __('Kindly tell us the reason so we can improve', 'margarita');
	$feedback_vars['nonce'] = wp_create_nonce('margarita_deactivate_nonce');
	$reasons[1] = __('I no longer need the plugin', 'margarita');
	$reasons[2] = __('I found a better plugin', 'margarita');
	$reasons[8] = __('I haven\'t found a feature that I need', 'margarita');
	$reasons[3] = __('I only needed the plugin for a short period', 'margarita');
	$reasons[4] = __('The plugin broke my site', 'margarita');
	$reasons[5] = __('The plugin suddenly stopped working', 'margarita');
	$reasons[6] = __('It\'s a temporary deactivation. I\'m just debugging an issue', 'margarita');
	$reasons[7] = __('Other', 'margarita');
	$feedback_vars['msg'] = __('If you have a moment, please let us know why you are deactivating', 'margarita');
	$feedback_vars['disclaimer'] = __('No private information is sent during your submission. Thank you very much for your help improving our plugin.', 'margarita');
	$feedback_vars['reasons'] = '';
	foreach ($reasons as $key => $reason) {
		$feedback_vars['reasons'].= '<li class="reason';
		if ($key == 2 || $key == 7 || $key == 8) {
			$feedback_vars['reasons'].= ' has-input';
		}
		$feedback_vars['reasons'].= '"';
		if ($key == 2 || $key == 7 || $key == 8) {
			$feedback_vars['reasons'].= 'data-input-type="textfield"';
			if ($key == 2) {
				$feedback_vars['reasons'].= 'data-input-placeholder="' . __('What\'s the plugin\'s name?', 'margarita') . '"';
			} elseif ($key == 8) {
				$feedback_vars['reasons'].= 'data-input-placeholder="' . __('What feature do you need?', 'margarita') . '"';
			}
		}
		$feedback_vars['reasons'].= '><label><span>
                                        <input type="radio" name="selected-reason" value="' . $key . '"/>
                                        </span><span>' . $reason . '</span></label></li>';
	}
	wp_enqueue_script('emd-plugin-feedback', MARGARITA_PLUGIN_URL . 'assets/js/emd-plugin-feedback.js');
	wp_localize_script("emd-plugin-feedback", 'plugin_feedback_vars', $feedback_vars);
	wp_enqueue_script('margarita-feedback', MARGARITA_PLUGIN_URL . 'assets/js/margarita-feedback.js');
	$margarita_vars['plugin'] = 'margarita';
	wp_localize_script("margarita-feedback", 'margarita_vars', $margarita_vars);
}
function margarita_send_deactivate_reason() {
	if (empty($_POST['deactivate_nonce']) || !isset($_POST['reason_id'])) {
		exit;
	}
	if (!wp_verify_nonce($_POST['deactivate_nonce'], 'margarita_deactivate_nonce')) {
		exit;
	}
	$reason_info = isset($_POST['reason_info']) ? sanitize_text_field($_POST['reason_info']) : '';
	$postfields['reason_id'] = intval($_POST['reason_id']);
	$postfields['plugin_name'] = sanitize_text_field($_POST['plugin_name']);
	if (!empty($reason_info)) {
		$postfields['reason_info'] = $reason_info;
	}
	$args = array(
		'body' => $postfields,
		'sslverify' => false,
		'timeout' => 15,
	);
	$resp = wp_remote_post('https://api.emarketdesign.com/deactivate_info.php', $args);
	echo 1;
	exit;
}
