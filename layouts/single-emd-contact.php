<?php $ent_attrs = get_option('margarita_attr_list'); ?>
<div class="emd-container">
<?php
$emd_contact_first_name = emd_mb_meta('emd_contact_first_name');
if (!empty($emd_contact_first_name)) { ?>
   <div id="emd-contact-emd-contact-first-name-div" class="emd-single-div">
   <div id="emd-contact-emd-contact-first-name-key" class="emd-single-title">
<?php _e('First Name', 'margarita'); ?>
   </div>
   <div id="emd-contact-emd-contact-first-name-val" class="emd-single-val">
<?php echo $emd_contact_first_name; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_contact_last_name = emd_mb_meta('emd_contact_last_name');
if (!empty($emd_contact_last_name)) { ?>
   <div id="emd-contact-emd-contact-last-name-div" class="emd-single-div">
   <div id="emd-contact-emd-contact-last-name-key" class="emd-single-title">
<?php _e('Last Name', 'margarita'); ?>
   </div>
   <div id="emd-contact-emd-contact-last-name-val" class="emd-single-val">
<?php echo $emd_contact_last_name; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_contact_email = emd_mb_meta('emd_contact_email');
if (!empty($emd_contact_email)) { ?>
   <div id="emd-contact-emd-contact-email-div" class="emd-single-div">
   <div id="emd-contact-emd-contact-email-key" class="emd-single-title">
<?php _e('Email', 'margarita'); ?>
   </div>
   <div id="emd-contact-emd-contact-email-val" class="emd-single-val">
<?php echo $emd_contact_email; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_contact_phone = emd_mb_meta('emd_contact_phone');
if (!empty($emd_contact_phone)) { ?>
   <div id="emd-contact-emd-contact-phone-div" class="emd-single-div">
   <div id="emd-contact-emd-contact-phone-key" class="emd-single-title">
<?php _e('Phone', 'margarita'); ?>
   </div>
   <div id="emd-contact-emd-contact-phone-val" class="emd-single-val">
<?php echo $emd_contact_phone; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_contact_address = emd_mb_meta('emd_contact_address');
if (!empty($emd_contact_address)) { ?>
   <div id="emd-contact-emd-contact-address-div" class="emd-single-div">
   <div id="emd-contact-emd-contact-address-key" class="emd-single-title">
<?php _e('Address', 'margarita'); ?>
   </div>
   <div id="emd-contact-emd-contact-address-val" class="emd-single-val">
<?php echo $emd_contact_address; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_contact_time = emd_mb_meta('emd_contact_time');
if (!empty($emd_contact_time)) { ?>
   <div id="emd-contact-emd-contact-time-div" class="emd-single-div">
   <div id="emd-contact-emd-contact-time-key" class="emd-single-title">
<?php _e('Contact Preference', 'margarita'); ?>
   </div>
   <div id="emd-contact-emd-contact-time-val" class="emd-single-val">
<?php echo $emd_contact_time; ?>
   </div>
   </div>
<?php
} ?>
<?php $emd_mb_file = emd_mb_meta('emd_contact_attach', 'type=file');
if (!empty($emd_mb_file)) { ?>
  <div id="emd-contact-emd-contact-attach-div" class="emd-single-div">
  <div id="emd-contact-emd-contact-attach-key" class="emd-single-title">
  <?php _e('Contact Files', 'margarita'); ?>
  </div>
  <div id="emd-contact-emd-contact-attach-val" class="emd-single-val">
  <?php foreach ($emd_mb_file as $info) {
		$fsrc = wp_mime_type_icon($info['ID']);
?>
  <a href='<?php echo esc_url($info['url']); ?>' target='_blank' title='<?php echo esc_attr($info['title']); ?>'><img src='<?php echo $fsrc; ?>' title='<?php echo esc_html($info['name']); ?>' width='20' />
   </a>
  <?php
	} ?>
  </div>
  </div>
<?php
} ?>
<?php
$emd_contact_location = emd_mb_meta('emd_contact_location');
if (!empty($emd_contact_location)) { ?>
   <div id="emd-contact-emd-contact-location-div" class="emd-single-div">
   <div id="emd-contact-emd-contact-location-key" class="emd-single-title">
<?php _e('Residence', 'margarita'); ?>
   </div>
   <div id="emd-contact-emd-contact-location-val" class="emd-single-val">
<?php echo $emd_contact_location; ?>
   </div>
   </div>
<?php
} ?>
<?php
$taxlist = get_object_taxonomies(get_post_type() , 'objects');
foreach ($taxlist as $taxkey => $mytax) {
	$termlist = get_the_term_list(get_the_ID() , $taxkey, '', ' , ', '');
	if (!empty($termlist)) { ?>
      <div id="emd-contact-<?php echo esc_attr($taxkey); ?>-div" class="emd-single-div">
      <div id="emd-contact-<?php echo esc_attr($taxkey); ?>-key" class="emd-single-title">
      <?php echo esc_html($mytax->labels->singular_name); ?>
      </div>
      <div id="emd-contact-<?php echo esc_attr($taxkey); ?>-val" class="emd-single-val">
      <?php echo $termlist; ?>
      </div>
      </div>
   <?php
	}
} ?>
</div><!--container-end-->