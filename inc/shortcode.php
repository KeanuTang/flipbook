<?php 
namespace flipbook;
use \WP_Query;


add_action('wp_footer', '\flipbook\enqueue_client_scripts');
function enqueue_client_scripts() {
    global $fbglobal;
    if(isset($fbglobal['load_client_scripts']) && !isset($fb3d['enqueued_client_scripts'])) {
        $fbglobal['enqueued_client_scripts'] = TRUE;
        register_scripts_and_styles();
        //wp_enqueue_style(POST_ID.'-client');
        
        
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
    $reporter = '';
    $the_query = new WP_Query( $args );
    $l;
    // The Loop
    while ( $the_query->have_posts() ) :
        $the_query->the_post(); ?>
    <div style="width:100%;margin:0 auto">
    <!-- BEGIN FLIPBOOK STRUCTURE -->  
    <div id="fb5-ajax">	 
    <!-- BEGIN HTML BOOK -->   
    <div data-current="book<?php echo $l;?>" class="fb5" id="fb5">    
            <!-- PRELOADER -->
            <div class="fb5-preloader"> 
                <div id="wBall_1" class="wBall">
                <div class="wInnerBall">
                </div>
                </div>
            </div>      
            <!-- BACKGROUND FOR BOOK -->  
            <div class="fb5-bcg-book"></div>      
            <!-- BEGIN CONTAINER BOOK -->
            <div id="fb5-container-book">
                <!-- BEGIN deep linking -->  
                <section id="fb5-deeplinking">
                    <ul>
                    <?php if( have_rows('flipbook_pages') ):
                    $i = 1;
                    ?> 
                <?php while( have_rows('flipbook_pages') ): the_row(); ?>
                        <li data-address="<?php echo 'page'.$i; ?>" data-page="<?php echo $i;?>"></li>
                        <?php $i++; endwhile; ?> 
                <?php endif; ?> 
                    </ul>
                </section>
                <!-- END deep linking -->   
                
                <!-- BEGIN BOOK -->
                <div id="fb5-book"> 
                <!-- BEGIN PAGE -->   
                    <?php if( have_rows('flipbook_pages') ):
                    $j = 1;
                    ?> 
                <?php while( have_rows('flipbook_pages') ): the_row(); 
                    // vars
                    $image_single = get_sub_field('flipbook_page_image'); 
                    $size ="single-image";
                    $content = get_sub_field('flipbook_page_html');  
                    if( !empty($image_single) ){?>
                    <div data-background-image="<?php echo $image_single['sizes'][ $size ] //echo $image_single['url']; ?>" class=""> 
                        <div class="fb5-cont-page-book"> 
                            <!-- number page and title -->                
                            <div class="fb5-meta">
                                <span class="fb5-description"></span>
                                <span class="fb5-num"><?php echo $j ;?></span>
                            </div>
                            <!-- end number page and title  -->  
                        </div> 
                    <!-- end cont-->
                    </div>
                    <?php } else{ ?>
                    <div data-background-image="" class="">  
                    <!-- begin container page book --> 
                    <div class="fb5-cont-page-book"> 
                                    <!-- description for page  --> 
                            <div class="fb5-page-book">
                                <div id="fb5-cover">
                                <?php echo $content; ?>	
                            </div>
                            </div> 
                            <!-- end description for page  -->    
                        <!-- number page and title -->                
                        <div class="fb5-meta">
                                <span class="fb5-description"></span>
                                <span class="fb5-num"><?php echo $j ;?></span>
                        </div>
                        <!-- end number page and title  -->  
                    </div> 
                    <!-- end container page book -->  
                    </div>
                <?php } ?>
                <?php $j++; endwhile; ?> 
                <?php endif; ?>
                <!-- END PAGE --> 
            </div>
            <!-- END BOOK -->
            <!-- arrows -->
            <a class="fb5-nav-arrow prev"></a>
            <a class="fb5-nav-arrow next"></a> 
            </div>
            <!-- END CONTAINER BOOK --> 
        <!-- BEGIN FOOTER -->
            <div id="fb5-footer"> 
                <div class="fb5-bcg-tools"></div>
                    <a id="fb5-logo" target="_blank" href="">
                    <!--<img alt="" src="img/logo.png">-->
                </a> 
                <div class="fb5-menu" id="fb5-center">
                    <ul>
                        <!-- icon download -->
                        <li>
                        <?php 

                        $pdf_link = get_field('flipbook_pdf');

                        if( $pdf_link ): ?>
                        <a title="DOWNLOAD FILE" target="_blank" class="fb5-download" href="<?php echo $pdf_link['url']; ?>"></a>
                        <?php endif; ?>
                        </li>
                        <!-- icon_zoom_in -->                              
                        <li>
                            <a title="ZOOM IN" class="fb5-zoom-in"></a>
                        </li>     
                        <!-- icon_zoom_out -->
                        <li>
                            <a title="ZOOM OUT " class="fb5-zoom-out"></a>
                        </li>    
                        <!-- icon_zoom_auto -->
                        <li>
                            <a title="ZOOM AUTO " class="fb5-zoom-auto"></a>
                        </li>                                
                        
                        <!-- icon_zoom_original -->
                        <!--<li>
                            <a title="ZOOM ORIGINAL (SCALE 1:1)" class="fb5-zoom-original"></a>
                        </li>-->
                        <!-- icon_allpages -->
                        <li>
                            <a title="SHOW ALL PAGES " class="fb5-show-all"></a>
                        </li>
                        <!-- icon_home -->
                        <li>
                            <a title="SHOW HOME PAGE " class="fb5-home"></a>
                        </li>         
                    </ul>
                </div>
                <div class="fb5-menu" id="fb5-right">
                    <ul> 
                        <!-- icon page manager -->                 
                        <li class="fb5-goto">
                            <label for="fb5-page-number" id="fb5-label-page-number">PAGE</label>
                            <input type="text" id="fb5-page-number">
                            <button type="button">GO</button>
                        </li>    
                        <!-- icon contact form -->                 
                        <!--<li>
                            <a title="SEND MESSAGE" class="contact"></a>
                        </li>    -->     
                        <!-- icon fullscreen -->                 
                        <li>
                            <a title="FULL / NORMAL SCREEN" class="fb5-fullscreen"></a>
                        </li>           
                    </ul>
                </div>
            </div>
        <!-- END FOOTER -->
        <!-- BEGIN CONTACT FORM -->
            <div id="fb5-contact" class="fb5-overlay">
        <form>
            <a class="fb5-close">X</a>
            <fieldset>
                <h3>CONTACT</h3>
                <p>
                    <input type="text" class="req" id="fb5-form-name" value="name...">
                </p>
                <p>
                    <input type="text" class="req" id="fb5-form-email" value="email...">
                </p>
                <p>
                    <textarea class="req" id="fb5-form-message">message...</textarea>
                </p>
                <p>
                    <button type="submit">SEND MESSAGE</button>
                </p>
            </fieldset>
            
            <fieldset class="fb5-thanks">
                <h1>Thanks for your email</h1>
                <p>Lorem ipsum dolor sit amet, vel ad sint fugit, velit nostro pertinax ex qui, no ceteros civibus explicari est. Eleifend electram ea mea, omittam reprehendunt nam at. Putant argumentum cum ex. At soluta principes dissentias nam, elit voluptatum vel ex.</p>		</fieldset>
        </form>
        </div>
        <!-- END CONTACT FORM -->
        <!-- BEGIN ALL PAGES -->
        <div id="fb5-all-pages" class="fb5-overlay">
        <section class="fb5-container-pages">
            <div id="fb5-menu-holder">
                <ul id="fb5-slider">
                <?php if( have_rows('flipbook_pages') ):
                    $k = 1;
                    ?> 
                <?php while( have_rows('flipbook_pages') ): the_row();
                $image_thumbnail = get_sub_field('flipbook_page_thumbnail');
                $image_single_thumbnail = get_sub_field('flipbook_page_image');
                $size = 'thumbnail';
                $thumbnail_url = wp_get_attachment_image_src( get_post_thumbnail_id($post_ID) );
                if($image_thumbnail != ""){
                ?> 
                    <li class="<?php echo $k; ?>">
                        <img alt=""   data-src="<?php echo $thumb = $image_thumbnail['sizes'][ $size ];?>">
                    <div class="1">
                        <span class="fb5-num1" ><?php echo 'Page '.$k ;?></span>
                        </div>
                    </li>
                <?php
                    }
                elseif($image_single_thumbnail != ""){
                ?> 
                        <li class="<?php echo $k; ?>">
                            <img alt=""   data-src="<?php echo $thumb = $image_single_thumbnail['sizes'][ $size ];?>">
                        <div class="1">
                            <span class="fb5-num1" ><?php echo 'Page '.$k ;?></span>
                            </div>
                        </li>
                <?php
                    }
                elseif($thumbnail_url != ""){ ?>
                    
                    <li class="<?php echo $k; ?>">					 
                        <img alt=""  data-src="<?php  echo $thumbnail_url[0];?>">
                        <div class="1">
                            <span class="fb5-num1" ><?php echo 'Page '.$k ;?></span>
                        </div>
                    </li>
                <?php }
                else{ ?>
                    <li class="<?php echo $k; ?>">					 
                        <img alt="" data-src="<?php echo $theme_uri; ?>images/flipbook-150x150.jpg">
                        <div class="1">
                            <span class="fb5-num1"><?php echo 'Page '. $k ;?></span>
                        </div> 
                    </li>
                <?php };
                $k++; endwhile;
                endif;	?>
                </ul>
            </div>
        </section>
        </div>
        <!-- END ALL PAGES -->
    </div>
    <!-- END HTML BOOK -->
    <!-- CONFIGURATION FLIPBOOK -->
    <script>    
    function addEvent(el, eventType, handler) {
        if (el.addEventListener) { // DOM Level 2 browsers
            el.addEventListener(eventType, handler, false);
        } else if (el.attachEvent) { // IE <= 8
            el.attachEvent('on' + eventType, handler);
        } else { // ancient browsers
            el['on' + eventType] = handler;
        }
    }

    addEvent(window, 'load', function() {
        jQuery('#fb5').data('config',
        {
            "page_width":"550",
            "page_height":"500",
            "email_form":"mahesh.yadav@sanmita.com",
            "zoom_double_click":"1",
            "zoom_step":"0.06",
            "double_click_enabled":"true",
            "tooltip_visible":"true",
            "toolbar_visible":"true",
            "gotopage_width":"30",
            "deeplinking_enabled":"true",
            "rtl":"false",
            'full_area':'false',
            'lazy_loading_thumbs':'false',
            'lazy_loading_pages':'false'
        })
    });
        </script>
    </div>
    <!-- END FLIPBOOK STRUCTURE -->    

    </div> 
    <!-- END DIV YOUR WEBSITE --> 
    <div class="col-md-12"> 
    <h1> <?php the_title(); ?></h1>
    <?php the_content();?> </div>
    <hr />
    <?php $l++;	endwhile;
    // Restore original Query & Post Data
    wp_reset_query();
    wp_reset_postdata();

    //for use in the loop, list 5 post titles related to first tag on current post
    $tags = wp_get_post_tags($post_ID);

    if ($tags) {
        echo '<div class="col-md-12"><h3>Related Flip Books </h3><hr /> </div>';
        $first_tag = $tags[0]->term_id;
        $args=array(
        'post_type' => 'aw_flipbook',
        'meta_key' => 'status',
        'meta_value' => 'Active',
        'tag__in' => array($first_tag),
        'post__not_in' => array($post_ID),
        'posts_per_page'=>5, 
        );
        $my_query = new WP_Query($args);
        if( $my_query->have_posts() ) {
            echo '<div class="col-md-9">';
        while ($my_query->have_posts()) : $my_query->the_post(); ?>
        <div class="col-md-3">
        <a class="relative-title" href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a>
        <?php 
        $url = wp_get_attachment_image_src( get_post_thumbnail_id($post_ID) );
        if($url != ""){
        ?> 
            <span class="<?php echo $k; ?>">
                <a  href="<?php the_permalink() ?>">  <img alt=""  width="150" src="<?php echo $url[0];?>"></a>
            </span>
            
        <?php }else{ ?>
            <span class="<?php echo $k; ?>">
                <a  href="<?php the_permalink() ?>">  <img alt=""  width="150" src="<?php echo $theme_uri; ?>images/flipbook-150x150.jpg"></a>
            </span>
        <?php } ?>
        </div>
        <?php
        endwhile;
        echo '</div>';
        }
        wp_reset_query();
    }

    $res = ob_get_contents();
    ob_end_clean();

    return $res;
}

function to_single_quotes($s) {
    return str_replace('"', '\'', $s);
}