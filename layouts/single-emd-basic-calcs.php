<?php $ent_attrs = get_option('margarita_attr_list'); ?>
<div class="emd-container">
<?php
$emd_bc_id = emd_mb_meta('emd_bc_id');
if (!empty($emd_bc_id)) { ?>
   <div id="emd-basic-calcs-emd-bc-id-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-id-key" class="emd-single-title">
<?php _e('ID', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-id-val" class="emd-single-val">
<?php echo $emd_bc_id; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_bc_value1 = emd_mb_meta('emd_bc_value1');
if (!empty($emd_bc_value1)) { ?>
   <div id="emd-basic-calcs-emd-bc-value1-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-value1-key" class="emd-single-title">
<?php _e('Value 1', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-value1-val" class="emd-single-val">
<?php echo $emd_bc_value1; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_bc_value2 = emd_mb_meta('emd_bc_value2');
if (!empty($emd_bc_value2)) { ?>
   <div id="emd-basic-calcs-emd-bc-value2-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-value2-key" class="emd-single-title">
<?php _e('Value 2', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-value2-val" class="emd-single-val">
<?php echo $emd_bc_value2; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_bc_value3 = emd_mb_meta('emd_bc_value3');
if (!empty($emd_bc_value3)) { ?>
   <div id="emd-basic-calcs-emd-bc-value3-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-value3-key" class="emd-single-title">
<?php _e('Value 3', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-value3-val" class="emd-single-val">
<?php echo $emd_bc_value3; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_bc_total = emd_mb_meta('emd_bc_total');
if (!empty($emd_bc_total)) { ?>
   <div id="emd-basic-calcs-emd-bc-total-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-total-key" class="emd-single-title">
<?php _e('Total', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-total-val" class="emd-single-val">
<?php echo $emd_bc_total; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_bc_deduct = emd_mb_meta('emd_bc_deduct');
if (!empty($emd_bc_deduct)) { ?>
   <div id="emd-basic-calcs-emd-bc-deduct-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-deduct-key" class="emd-single-title">
<?php _e('Deduction', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-deduct-val" class="emd-single-val">
<?php echo $emd_bc_deduct; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_bc_multiply = emd_mb_meta('emd_bc_multiply');
if (!empty($emd_bc_multiply)) { ?>
   <div id="emd-basic-calcs-emd-bc-multiply-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-multiply-key" class="emd-single-title">
<?php _e('Multiplication', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-multiply-val" class="emd-single-val">
<?php echo $emd_bc_multiply; ?>
   </div>
   </div>
<?php
} ?>
<?php
$emd_bc_division = emd_mb_meta('emd_bc_division');
if (!empty($emd_bc_division)) { ?>
   <div id="emd-basic-calcs-emd-bc-division-div" class="emd-single-div">
   <div id="emd-basic-calcs-emd-bc-division-key" class="emd-single-title">
<?php _e('Division', 'margarita'); ?>
   </div>
   <div id="emd-basic-calcs-emd-bc-division-val" class="emd-single-val">
<?php echo $emd_bc_division; ?>
   </div>
   </div>
<?php
} ?>
</div><!--container-end-->