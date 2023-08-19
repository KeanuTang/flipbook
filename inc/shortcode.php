<?php 
namespace flipbook;
use \WP_Query;

//Ideally we only load when we know there's a flipbook, but we need the jQuery early...
//add_action('wp_footer', '\flipbook\enqueue_client_scripts');
add_action('wp_enqueue_scripts', '\flipbook\enqueue_client_scripts');
function enqueue_client_scripts() {
    global $fbglobal;
    //isset($fbglobal['load_client_scripts']) && 
    if(!isset($fbglobal['enqueued_client_scripts'])) {
        $fbglobal['enqueued_client_scripts'] = TRUE;
        register_scripts_and_styles();
        wp_enqueue_style(POST_TYPE.'-client');
        wp_enqueue_script(POST_TYPE.'-client');
    }
}



add_shortcode(POST_TYPE, '\flipbook\shortcode_handler');
function shortcode_handler($atts, $content='') {
    global $fbglobal;
    $fbglobal['load_client_scripts'] = TRUE;

    $atts = shortcode_atts([
        'id'=> '0',
        'mode'=> 'fullscreen',
        'title'=> 'false',
        'template'=> 'default',
        'lightbox'=> 'default',
        'classes'=> '',
        'urlparam'=> 'fb3d-page',
        'page-n'=>'0',
        'pdf'=> '',
        'tax'=> 'null',
        'thumbnail'=> '',
        'cols'=> '3',
        'style'=> '',
        'query'=> '',
        'book-template'=> 'default'
    ], $atts);

    ob_start();

    $post_ID = $atts['id'];
    if ( is_user_logged_in() ) {
        $args = array(
            'post_type' => POST_TYPE,
            'meta_key' => 'flipbook_status',
            'meta_value' => 'Active',
            'p' =>$post_ID,
            'post_status' => array( 'any' )
        );
    }
    else {
        $args = array(
            'post_type' => POST_TYPE,
            'meta_key' => 'flipbook_status',
            'meta_value' => 'Active',
            'p' => $post_ID,
        );
    }




    // The Query
    $the_query = new WP_Query( $args );
    $l=0;
    // The Loop
    while ( $the_query->have_posts() ) :
        $the_query->the_post(); ?>
    <!-- BEGIN BOOK -->
    <div class="page-wrapper">
        <div class="flipbook-viewport">
            <div class="container">
                <div class="flipbook">
                    <!-- Next button -->
                    <div ignore="1" class="next-button"></div>
                    <!-- Previous button -->
                    <div ignore="1" class="previous-button"></div>
                    <!-- BEGIN PAGES -->   
                    <?php if( have_rows('flipbook_pages') ):
                    $j = 1;
                    ?> 
                    <?php while( have_rows('flipbook_pages') ): the_row(); 
                        // vars
                        $image_single = get_sub_field('flipbook_page_image'); 
                        $size ="large";
                        $html = get_sub_field('flipbook_page_html');
                        $url = get_sub_field('flipbook_page_url');
                        if ( !empty($url) ){?>
                            <div><iframe title="Page <?php echo $j; ?>" src="<?php echo $url ?>" style="position: absolute; height: 100%; width:100%" frameborder="0"></iframe></div>
                        <?php } elseif( !empty($html) ){?>
                            <div><?php echo $html; ?></div>
                        <?php } else { ?>
                            <div style="background-image: url(<?php echo $image_single['sizes'][ $size ] ?>)"></div>
                        <?php } ?>
                    <?php $j++; endwhile; ?> 
                    <?php endif; ?>
                    <!-- END PAGES --> 
                </div>
            </div>
        </div>
    </div>
    <!-- END BOOK -->

    <script type="text/javascript">
    function loadApp() {
        // Create the flipbook
        jQuery('.flipbook').turn({
            // Width
            width:922,
            
            // Height
            height:600,
            
            // Elevation
            elevation: 50,
            
            // Enable gradients
            gradients: true,
            
            // Auto center this flipbook
            autoCenter: true,

        });

        // Events for the next button
        jQuery('.next-button').bind(jQuery.mouseEvents.over, function() {
            jQuery(this).addClass('next-button-hover');
        }).bind(jQuery.mouseEvents.out, function() {
            jQuery(this).removeClass('next-button-hover');
        }).bind(jQuery.mouseEvents.down, function() {
            jQuery(this).addClass('next-button-down');
        }).bind(jQuery.mouseEvents.up, function() {
            jQuery(this).removeClass('next-button-down');
        }).click(function() {
            jQuery('.flipbook').turn('next');
        });

        // Events for the previous button
        jQuery('.previous-button').bind(jQuery.mouseEvents.over, function() {
            jQuery(this).addClass('previous-button-hover');
        }).bind(jQuery.mouseEvents.out, function() {
            jQuery(this).removeClass('previous-button-hover');
        }).bind(jQuery.mouseEvents.down, function() {
            jQuery(this).addClass('previous-button-down');
        }).bind(jQuery.mouseEvents.up, function() {
            jQuery(this).removeClass('previous-button-down');
        }).click(function() {
            jQuery('.flipbook').turn('previous');
        });


    }
    // Load the HTML4 version if there's not CSS transform
    yepnope({
        test : Modernizr.csstransforms,
        yep: [ '<?php echo JS.'turn.js' ?>' ],
        nope: [ '<?php echo JS.'turn.html4.min.js' ?>' ],
        both: [ '<?php echo JS.'zoom.js' ?>', '<?php echo CSS.'turn.css' ?>' ],
        complete: loadApp
    });

    

    </script>
    

    <?php $l++;	endwhile;
    // Restore original Query & Post Data
    wp_reset_query();
    wp_reset_postdata();


    $res = ob_get_contents();
    ob_end_clean();

    return $res;
}

function to_single_quotes($s) {
    return str_replace('"', '\'', $s);
}