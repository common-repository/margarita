<?php
/**
 * Calculate formulas
 * @package MARGARITA
 * @since WPAS 4.6
 */
if (!defined('ABSPATH')) exit;
add_action('wp_ajax_margarita_emd_calc_formula', 'margarita_emd_calc_formula');
add_action('wp_ajax_nopriv_margarita_emd_calc_formula', 'margarita_emd_calc_formula');
function margarita_emd_calc_formula() {
	$result = '';
	switch ($_GET['function']) {
		case 'EMD_BC_TOTAL':
			$A10 = floatval($_GET["params"][1]);
			$A11 = floatval($_GET["params"][2]);
			$A12 = $_GET["params"][3];
			$result = $A10 + $A11 + $A12;
		break;
		case 'EMD_BC_DEDUCT':
			$A10 = floatval($_GET["params"][1]);
			$A11 = floatval($_GET["params"][2]);
			$A12 = $_GET["params"][3];
			$result = $A10 + $A11 - $A12;
		break;
		case 'EMD_BC_MULTIPLY':
			$A10 = floatval($_GET["params"][1]);
			$A11 = floatval($_GET["params"][2]);
			$A12 = $_GET["params"][3];
			$result = $A10 * $A11 * $A12;
		break;
		case 'EMD_BC_DIVISION':
			$A10 = floatval($_GET["params"][1]);
			$A11 = floatval($_GET["params"][2]);
			$A12 = $_GET["params"][3];
			$result = $A10 * $A11 / $A12;
		break;
	}
	echo $result;
	die();
}
