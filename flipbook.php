<?php
/*
  Plugin Name: Flipbook
  Description: Flipbook plugin that supports PDF, images, HTML, and embedded pages
  Author: Ted Tang
  Version: 0.1
  License: GPLv2 or later
  Text Domain: flipbook
*/

namespace flipbook;


define( 'flipbook\URL', plugin_dir_url( __FILE__ ) );
define( 'flipbook\DIR', plugin_dir_path( __FILE__ ) );
define( 'flipbook\DIR_NAME', plugin_basename( __FILE__ ) );
define( 'flipbook\INC', DIR.'inc/');
define( 'flipbook\LIB', DIR.'lib/');
define( 'flipbook\POST_TYPE', 'flipbook');
define( 'flipbook\TEMPLATES', DIR.'assets/templates/');
define( 'flipbook\JS', URL.'assets/js/');
define( 'flipbook\CSS', URL.'assets/css/');
define( 'flipbook\IMAGES', URL.'assets/images/');


$fbglobal = array( 
  'registered_scripts_and_styles' => false
);

require_once(INC.'utility-functions.php');
require_once(INC.'install.php');
require_once(INC.'scripts.php');
require_once(INC.'shortcode.php');
