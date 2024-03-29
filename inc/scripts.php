<?php
namespace flipbook;

function register_scripts() {
  wp_register_script('jquery', 'https://code.jquery.com/jquery-3.7.0.min.js');
  wp_register_script('modernizr', JS.'modernizr.2.5.3.min.js');
  wp_register_script('pdfjs', JS.'pdf.js');
  wp_register_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js');
  wp_register_script('html2canvas', 'https://html2canvas.hertzen.com/dist/html2canvas.min.js');
  wp_register_script('dompurify', 'https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.0.5/purify.min.js');
  wp_register_script(POST_TYPE.'-client', JS.'turn.min.js', array('jquery', 'modernizr', 'pdfjs', 'jspdf', 'html2canvas', 'dompurify'));
}

function register_styles() {
  wp_register_style('font-awesome', 'https://use.fontawesome.com/releases/v5.15.4/css/all.css');
  wp_register_style(POST_TYPE.'-client', CSS.'turn.css', array('font-awesome'));
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