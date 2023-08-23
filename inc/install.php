<?php
namespace flipbook;

add_action('plugins_loaded', 'flipbook\check_flipbook_dependencies');
function check_flipbook_dependencies() {
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
    if (!class_exists('ACF') && !function_exists('get_field')) {
        // deactivate_plugins(DIR . 'flipbook.php');
        add_action('admin_notices', 'flipbook\missing_dependency_notice');
    }
}
function missing_dependency_notice() {
    echo '<div class="error"><p>Flipbook plugin requires the Advanced Custom Fields Plugin, which is missing.</p></div>';
}


add_action( 'init', 'flipbook\register_flipbook_type' );
function register_flipbook_type() {
    \register_post_type( POST_TYPE,
        array(
            'labels' => array(
                'name' => 'Flipbook',
                'singular_name' => 'flipbook',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Item',
                'edit' => 'Edit',
                'edit_item' => 'Edit Item',
                'new_item' => 'New Item',
                'view' => 'View',
                'view_item' => 'View Item',
                'search_items' => 'Search Items',
                'not_found' => 'No Items found',
                'not_found_in_trash' => 'No Items found in Trash',
                'parent' => 'Parent Item'
            ),
            'public' => true,
            //'menu_position' => 10,
            'supports' => array( 'title', 'editor','thumbnail',  ),
            array( 'title', ),
            'taxonomies' => array( 'category','post_tag'),
            register_taxonomy('team_tag', 'team', array(
                    'hierarchical' => false, 
                    'label' =>  "tags" , 
                    'singular_name' =>  "tag" , 
                    'rewrite' => true, 
                    'query_var' => true
                )
            ),
            'rewrite' => array( 'slug' => POST_TYPE ),
            'capability_type' => 'page',
            'menu_icon'=> 'dashicons-book-alt',
            'has_archive' => true
        )
    );
    flush_rewrite_rules(false);
}


function custom_template($single) {
    global $wp_query, $post;

    //if (wp_is_block_theme()) return $single;  Uncomment this later

    if($post->post_type===POST_TYPE) {
      $template = TEMPLATES.'/single-flipbook.php';
      if(file_exists($template)) {
        $single = $template;
      }
    }
    return $single;
  }
add_filter('single_template', '\flipbook\custom_template');


add_action( 'acf/include_fields', function() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
	'key' => 'group_64df9dbba3fce',
	'title' => 'Flipbook',
	'fields' => array(
		array(
			'key' => 'field_64df9dbb2563b',
			'label' => 'Status',
			'name' => 'flipbook_status',
			'aria-label' => '',
			'type' => 'select',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_save_meta' => 0,
			'choices' => array(
				'Active' => 'Active',
				'Inactive' => 'Inactive',
			),
			'default_value' => false,
			'return_format' => 'value',
			'multiple' => 0,
			'allow_null' => 0,
			'ui' => 0,
			'ajax' => 0,
			'placeholder' => '',
			'allow_custom' => 0,
			'search_placeholder' => '',
		),
		array(
			'key' => 'field_64df9eaa9b3a7',
			'label' => 'Upload PDF and Convert it to Flipbook',
			'name' => 'flipbook_convert_PDF',
			'aria-label' => '',
			'type' => 'checkbox',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => 0,
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_save_meta' => 0,
			'choices' => array(
				'Upload PDF and Convert it to Flipbook' => 'Upload PDF and Convert it to Flipbook',
			),
			'default_value' => array(
			),
			'return_format' => 'value',
			'allow_custom' => 0,
			'layout' => 'vertical',
			'toggle' => 0,
			'save_custom' => 0,
			'custom_choice_button_text' => 'Add new choice',
		),
		array(
			'key' => 'field_64df9ee1688ab',
			'label' => 'Upload PDF',
			'name' => 'flipbook_pdf',
			'aria-label' => '',
			'type' => 'file',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_64df9eaa9b3a7',
						'operator' => '!=empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_save_meta' => 0,
			'uploader' => '',
			'return_format' => 'array',
			'min_size' => '',
			'max_size' => '',
			'mime_types' => 'pdf,PDF',
			'library' => 'all',
		),
		array(
			'key' => 'field_64df9f07c0057',
			'label' => 'Create Flipbook',
			'name' => 'flipbook_pages',
			'aria-label' => '',
			'type' => 'repeater',
			'instructions' => '',
			'required' => 0,
			'conditional_logic' => array(
				array(
					array(
						'field' => 'field_64df9eaa9b3a7',
						'operator' => '==empty',
					),
				),
			),
			'wrapper' => array(
				'width' => '',
				'class' => '',
				'id' => '',
			),
			'acfe_save_meta' => 0,
			'acfe_repeater_stylised_button' => 0,
			'layout' => 'row',
			'pagination' => 0,
			'min' => 1,
			'max' => 0,
			'collapsed' => '',
			'button_label' => 'Add Row',
			'rows_per_page' => 20,
			'sub_fields' => array(
				array(
					'key' => 'field_64df9f4830dbc',
					'label' => 'Webpage',
					'name' => 'flipbook_page_url',
					'aria-label' => '',
					'type' => 'url',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'acfe_save_meta' => 0,
					'default_value' => '',
					'placeholder' => '',
					'parent_repeater' => 'field_64df9f07c0057',
				),
				array(
					'key' => 'field_64e628fe2ecec',
					'label' => 'Webpage Content DIV ID',
					'name' => 'flipbook_page_div_id',
					'aria-label' => '',
					'type' => 'text',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'acfe_save_meta' => 0,
					'default_value' => '',
					'maxlength' => '',
					'placeholder' => '',
					'prepend' => '',
					'append' => '',
					'parent_repeater' => 'field_64df9f07c0057',
				),
				array(
					'key' => 'field_64df9fa6ff37b',
					'label' => 'HTML Content',
					'name' => 'flipbook_page_html',
					'aria-label' => '',
					'type' => 'wysiwyg',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'acfe_save_meta' => 0,
					'default_value' => '',
					'tabs' => 'all',
					'toolbar' => 'full',
					'media_upload' => 1,
					'delay' => 0,
					'parent_repeater' => 'field_64df9f07c0057',
				),
				array(
					'key' => 'field_64df9fb8ff37c',
					'label' => 'Page Image',
					'name' => 'flipbook_page_image',
					'aria-label' => '',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'acfe_save_meta' => 0,
					'uploader' => '',
					'return_format' => 'array',
					'acfe_thumbnail' => 0,
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
					'preview_size' => 'medium',
					'library' => 'all',
					'parent_repeater' => 'field_64df9f07c0057',
				),
				array(
					'key' => 'field_64df9fd5ff37d',
					'label' => 'Thumbnail',
					'name' => 'flipbook_page_thumbnail',
					'aria-label' => '',
					'type' => 'image',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'acfe_save_meta' => 0,
					'uploader' => '',
					'return_format' => 'array',
					'acfe_thumbnail' => 0,
					'min_width' => '',
					'min_height' => '',
					'min_size' => '',
					'max_width' => '',
					'max_height' => '',
					'max_size' => '',
					'mime_types' => '',
					'preview_size' => 'medium',
					'library' => 'all',
					'parent_repeater' => 'field_64df9f07c0057',
				),
			),
		),
	),
	'location' => array(
		array(
			array(
				'param' => 'post_type',
				'operator' => '==',
				'value' => 'flipbook',
			),
		),
	),
	'menu_order' => 0,
	'position' => 'normal',
	'style' => 'default',
	'label_placement' => 'left',
	'instruction_placement' => 'label',
	'hide_on_screen' => '',
	'active' => true,
	'description' => '',
	'show_in_rest' => 0,
	'acfe_display_title' => '',
	'acfe_autosync' => '',
	'acfe_form' => 0,
	'acfe_meta' => '',
	'acfe_note' => '',
) );
} );