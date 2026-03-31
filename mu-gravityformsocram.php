<?php
/**
 * MU Gravity Forms Ocram
 *
 * Sends Gravity Forms submissions to Ocram to create kanban board cards.
 *
 * @package MU_GRAVITYFORMSOCRAM
 *
 * Plugin Name:  MU Gravity Forms Ocram
 * Plugin URI:   https://github.com/marshallu/mu-gravityformsocram
 * Description:  Sends Gravity Forms submissions to Ocram to create kanban board cards.
 * Version:      1.0.0
 * Author:       Christopher McComas
 * Requires PHP: 8.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MU_GRAVITYFORMSOCRAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

require_once MU_GRAVITYFORMSOCRAM_PLUGIN_DIR . 'includes/class-mu-gravityformsocram.php';

/**
 * Returns the main instance of MU_GRAVITYFORMSOCRAM.
 *
 * @return MU_GRAVITYFORMSOCRAM
 */
function mu_gravityformsocram() {
	return MU_GRAVITYFORMSOCRAM::instance();
}

mu_gravityformsocram();
