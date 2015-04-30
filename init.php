<?php

/*
Plugin Name: Thumbnails
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.
Author: Matt Mullenweg
Version: 1.6
Author URI: http://ma.tt/
*/

/* Sets the path to the plugin directory. */
define( 'SA_DT_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );

/* Sets the path to the plugin directory URI. */
define( 'SA_DT_DIR_URI', trailingslashit( plugin_dir_url( __FILE__ ) ) );

include_once SA_DT_DIR . 'inc/class-thumbnail.php';
include_once SA_DT_DIR . 'inc/functions.php';
include_once SA_DT_DIR . 'inc/shortcodes.php';
include_once SA_DT_DIR . 'inc/scripts.php';
include_once SA_DT_DIR . 'inc/meta-box.php';









