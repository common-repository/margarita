<?php
/**
 * Settings Glossary Functions
 *
 * @package MARGARITA
 * @since WPAS 4.0
 */
if (!defined('ABSPATH')) exit;
add_action('margarita_settings_glossary', 'margarita_settings_glossary');
/**
 * Display glossary information
 * @since WPAS 4.0
 *
 * @return html
 */
function margarita_settings_glossary() {
	global $title;
?>
<div class="wrap">
<h2><?php echo $title; ?></h2>
<p><?php _e('Margarita is a demo app showcasing basic capabilities of WP App Studio. Margarita can be implemented using FreeDev Development plan.', 'margarita'); ?></p>
<p><?php _e('The below are the definitions of entities, attributes, and terms included in Margarita.', 'margarita'); ?></p>
<div id="glossary" class="accordion-container">
<ul class="outer-border">
<li id="emd_basic_calcs" class="control-section accordion-section">
<h3 class="accordion-section-title hndle" tabindex="2"><?php _e('Basic Calculations', 'margarita'); ?></h3>
<div class="accordion-section-content">
<div class="inside">
<table class="form-table"><p class"lead"><?php _e('Demonstrates basic calculations which can be used in FreeDev', 'margarita'); ?></p><tr>
<th><?php _e('ID', 'margarita'); ?></th>
<td><?php _e(' Being a unique identifier, it uniquely distinguishes each instance of Basic Calculation entity. ID does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Value 1', 'margarita'); ?></th>
<td><?php _e(' Value 1 does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Value 2', 'margarita'); ?></th>
<td><?php _e(' Value 2 does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Value 3', 'margarita'); ?></th>
<td><?php _e(' Value 3 does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Total', 'margarita'); ?></th>
<td><?php _e('Sums up all values Total does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Deduction', 'margarita'); ?></th>
<td><?php _e('Sums up value 1 and value 2 then deducts value 3 from their total. Deduction does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Multiplication', 'margarita'); ?></th>
<td><?php _e('Multiplies all 3 values Multiplication does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Division', 'margarita'); ?></th>
<td><?php _e('Multiplies value 1 and value 2 then divides the total by value 3 Division does not have a default value. ', 'margarita'); ?></td>
</tr></table>
</div>
</div>
</li><li id="emd_contact" class="control-section accordion-section">
<h3 class="accordion-section-title hndle" tabindex="1"><?php _e('Contacts', 'margarita'); ?></h3>
<div class="accordion-section-content">
<div class="inside">
<table class="form-table"><p class"lead"><?php _e('', 'margarita'); ?></p><tr>
<th><?php _e('First Name', 'margarita'); ?></th>
<td><?php _e('Contact\'s first name First Name is a required field. First Name does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Last Name', 'margarita'); ?></th>
<td><?php _e('Contact\'s last name Last Name is a required field. Last Name does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Email', 'margarita'); ?></th>
<td><?php _e('Contact\'s email address Email is a required field. Being a unique identifier, it uniquely distinguishes each instance of Contact entity. Email does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Phone', 'margarita'); ?></th>
<td><?php _e('Contact\'s phone or mobile Phone does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Address', 'margarita'); ?></th>
<td><?php _e('Contact\'s mailing address. Address does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Contact Preference', 'margarita'); ?></th>
<td><?php _e('When would you like to be contacted? Contact Preference has a default value of <b>\'afternoon\'</b>.Contact Preference is displayed as a dropdown and has predefined values of: morning, afternoon, evening, night.', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Contact Files', 'margarita'); ?></th>
<td><?php _e(' Contact Files does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Residence', 'margarita'); ?></th>
<td><?php _e('Is this address your residence? Residence does not have a default value. ', 'margarita'); ?></td>
</tr><tr>
<th><?php _e('Contact Category', 'margarita'); ?></th>

<td><?php _e(' Contact Category accepts multiple values like tags', 'margarita'); ?>. <?php _e('Contact Category does not have a default value', 'margarita'); ?>.<div class="taxdef-block"><p><?php _e('The following are the preset values for <b>Contact Category:</b>', 'margarita'); ?></p><p class="taxdef-values"><?php _e('internal', 'margarita'); ?>, <?php _e('external', 'margarita'); ?></p></div></td>
</tr>
<tr>
<th><?php _e('Contact Tag', 'margarita'); ?></th>

<td><?php _e(' Contact Tag accepts multiple values like tags', 'margarita'); ?>. <?php _e('Contact Tag does not have a default value', 'margarita'); ?>.<div class="taxdef-block"><p><?php _e('The following are the preset values for <b>Contact Tag:</b>', 'margarita'); ?></p><p class="taxdef-values"><?php _e('friend', 'margarita'); ?>, <?php _e('business', 'margarita'); ?>, <?php _e('family', 'margarita'); ?>, <?php _e('spouse', 'margarita'); ?>, <?php _e('child', 'margarita'); ?></p></div></td>
</tr>
</table>
</div>
</div>
</li>
</ul>
</div>
</div>
<?php
}
