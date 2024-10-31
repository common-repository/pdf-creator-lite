<?php
/*
Plugin Name: PDF Creator LITE
Plugin URI: http://www.cite.soton.ac.uk
Description: Create a PDF of your entire site (pages only!)
Version: 1.1
Author: Alex Furr, Simon Ward
Author URI: http://www.cite.soton.ac.uk
License: GPL
*/

date_default_timezone_set('UTC');

define( 'SSAPDF_PLUGIN_URL', plugins_url( 'pdf-creator-lite' , dirname( __FILE__ ) ) );

define('EXPORT_AS_PDF_PATH', plugin_dir_path(__FILE__));
require_once EXPORT_AS_PDF_PATH.'functions.php'; 

add_action('admin_menu', 'SSAPDF_Export_as_PDF'); // hook into WP admin menus
?>