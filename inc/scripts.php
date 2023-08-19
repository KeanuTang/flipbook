<?php
namespace flipbook;

function register_scripts() {
  wp_register_script('jquery', 'https://code.jquery.com/jquery-3.7.0.min.js');
  wp_register_script('modernizr', JS.'modernizr.2.5.3.min.js');
  wp_register_script(POST_TYPE.'-client', JS.'turn.min.js', array('jquery', 'modernizr'));
}

function register_styles() {
  wp_register_style(POST_TYPE.'-client', CSS.'turn.css');
}

$fbglobal['registered_scripts_and_styles'] = false;
function register_scripts_and_styles() {
  global $fbglobal;
  if(!$fbglobal['registered_scripts_and_styles']) {
    register_styles();
    register_scripts();
    $fbglobal['registered_scripts_and_styles'] = true;
  }
}