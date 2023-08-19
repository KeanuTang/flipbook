<?php
namespace flipbook;

get_header();

echo(shortcode_handler([
  'id'=> get_the_ID(),
  'classes'=> 'default-page'
]));

get_footer();

