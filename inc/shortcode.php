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

// There are rendering methods, depending on the type of flipbook we are creating.
// 1. PDF flipbook - this has less PHP code, and instead we rely in PDF.js library
//    to load the PDF file, and then we use JavaScript to create all the HTML elements
//    and render the PDF pages.
// 2. Image/HTML/URL flipbook - this uses more PHP code to generate the HTML elements
//    we need to render the flipbook. The URL pages have some complex JavaScript that
//    goes along with it. 

add_shortcode(POST_TYPE, '\flipbook\shortcode_handler');
function shortcode_handler($atts, $content='') {
    global $fbglobal;
    global $post;
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

    $post_ID = $atts['id'];
    
    // The Query
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
    $the_query = new WP_Query( $args );

    //Set up the dimensions of everything
    if (empty($page_width)) $page_width = 461;
    if (empty($page_height)) $page_height = 600;
    if (is_array(get_field( 'flipbook_pages' ))) $num_pages = count( get_field( 'flipbook_pages' ));
    if (empty($num_pages)) $num_pages = 0;
    
    $viewport_width = $page_width * 2;
    $viewport_height = $page_height + 200;
    $background_color = "white";

    
    $pdf_link = get_field('flipbook_pdf');
    
    ob_start();

    
    $l=0;
    // The Loop. Should really only return 1 post... But I guess written to support multiple
    while ( $the_query->have_posts() ) :
    
    $the_query->the_post(); 
    ?>

    <!-- BEGIN BOOK -->
    <div class="page-wrapper">
        <div class="flipbook-viewport">
            <div class="flipbook-bar">
                <?php if (!empty($pdf_link)): ?>
                <div><i class="zoom-icon fas fa-search-plus" title="Zoom"></i></div>
                <?php endif; ?>
                <div><i class="thumbnails-icon fas fa-list" title="Thumbnails"></i></div>
                <div><i class="backward-icon fas fa-backward" title="Backward"></i></div>
                <div class="pages">
                    <input type="text" class="number inpPage" maxlength="4" value="1" title="Current Page">
                    <input type="text" class="total inpPages" readonly="" maxlength="4" title="Total Number of Pages">
                </div>
                <div><i class="forward-icon fas fa-forward" title="Forward"></i></div>
                <div><i class="fullscreen-icon fas fa-expand-arrows-alt" title="Fullscreen"></i></div>
                <div><i class="download-icon fas fa-download" title="Download"></i></div>
                <?php if (!empty($pdf_link)): ?><?php endif; ?>
            </div>
            <div class="thumbnails">
                <div class="thumbnails-inner">
                <?php if (empty($pdf_link) && have_rows('flipbook_pages')): 
                    $j = 0;
                    
                    ?><div class="thumbnail-layout-wrapper current"><?  //Wrappers around each two pages

                    while( have_rows('flipbook_pages') ): the_row(); 
                        $j++;
                        
                        if ($j % 2 == 0) {
                            ?><div class="thumbnail-layout-wrapper"><?php
                        }

                        $image_thumbnail = strip_quotes(get_sub_field('flipbook_page_thumbnail')); 
                        $image_single = strip_quotes(get_sub_field('flipbook_page_image')); 
                        $image_url = '';
                        if (!empty($image_thumbnail)) {
                            $image_url = $image_thumbnail['url'];
                        } elseif (!empty($image_single)) {
                            $image_url = $image_single['url'];
                        } 
                        $size ="large";
                        $html = strip_quotes(get_sub_field('flipbook_page_html'));
                        $url = strip_quotes(get_sub_field('flipbook_page_url'));
                        
                        if (!empty($image_url)) { ?>
                            <div onclick="jQuery('.flipbook').turn('page', <?php echo $j; ?>);" class="page-<?php echo $j; ?> thumbnail-image" style="background-image: url(<?php echo $image_url; ?>)"></div>
                        <?php } else { ?>
                            <div onclick="jQuery('.flipbook').turn('page', <?php echo $j; ?>);" class="page-<?php echo $j; ?> thumbnail-text"><p>Page <?php echo $j; ?></p></div>
                        <?php 
                        } 


                        if ($j % 2 == 1) {
                            ?></div><?php
                        }
                        
                    endwhile; 

                    
                    if ($j % 2 == 0) {
                        ?></div><?php
                    }
                    
                endif; 
                ?>
                </div>
            </div>
            <div class="container">
                <div class="flipbook">
                    <!-- Next button -->
                    <div ignore="1" class="next-button"></div>
                    <!-- Previous button -->
                    <div ignore="1" class="previous-button"></div>
                    <!-- BEGIN PAGES -->   
                    <?php 
                    
                    if ( empty($pdf_link) && have_rows('flipbook_pages') ) {
                        $j = 0;
                        while( have_rows('flipbook_pages') ): the_row(); 
                            $j++;
                            
                            $image_single = get_sub_field('flipbook_page_image'); 
                            $html = get_sub_field('flipbook_page_html');
                            $url = get_sub_field('flipbook_page_url');
                            $url_div_id = get_sub_field('flipbook_page_div_id');
                            if ( !empty($url) ){
                                if ( empty($url_div_id) ){ ?>
                                    <div>
                                        <iframe class="flipbook-page flipbook-page-iframe" title="Page <?php echo $j; ?>" src="<?php echo $url ?>" frameborder="0" scrolling="no"></iframe>
                                        <div class="flipbook-page-overlay"></div>
                                    </div>
                                <?php } else { 
                                    $targetID = uniqid() . '-div';
                                    ?>
                                    <div>
                                        <div id="<?php echo $targetID; ?>" class="flipbook-page flipbook-page-iframe" title="Page <?php echo $j; ?>"></div>
                                        <div class="flipbook-page-overlay"></div>
                                        <script>
                                            if (window.addEventListener)
                                            {
                                                window.addEventListener('load', function() {
                                                    embedIFrameContent('<?php echo $url; ?>', '<?php echo $url_div_id; ?>', '<?php echo $targetID; ?>')
                                                });
                                            }
                                        </script>
                                    </div>
                                <?php } ?>
                            <?php } elseif( !empty($html) ){?>
                                <div class="flipbook-page"><?php echo $html; ?></div>
                            <?php } else { ?>
                                <div class="flipbook-page" style="background-image: url(<?php echo $image_single['url'] ?>)"></div>
                            <?php } ?>
                        <?php endwhile; ?> 
                    <?php } ?>
                    <!-- END PAGES --> 
                </div>
            </div>
        </div>
    </div>
    <!-- END BOOK -->

    
    <script>
        /* JavaScript for Flipbook */
        var page_height = <?php echo $page_height; ?>;
        var page_width = <?php echo $page_width; ?>;
        var pdf_loading;
        var num_pages = <?php echo $num_pages; ?>;

        
        /* Set up the necessary CSS for the viewport based on the defined height and width */
        function setCSS() {
            viewport_width = page_width * 2;
            viewport_height = page_height + 200;
            console.log(viewport_width + ' ' + viewport_height + ' ' + page_width + ' ' + page_height);
            $viewport_div = jQuery('.flipbook-viewport');
            $viewport_div.height(viewport_height);
            $viewport_div.children('.flipbook').width(viewport_width).height(page_height).css({left: -page_width  + 'px', top: -page_height/2  + 'px'});
            $viewport_div.children('.page').width(page_width).height(page_height);
        }

    <?php if (!empty($pdf_link)): ?>
        /** Special JS when loading a PDF as a flipbook */
        pdf_loading = true;
        var pdf_url = '<?php echo $pdf_link['url']; ?>';

        /** This function is invoked when the user clicks to download the PDF */
        function downloadPDF() {
            var link = document.createElement("a");
            link.download = '<?php echo $pdf_link['filename']; ?>';
            link.href = '<?php echo $pdf_link['url']; ?>';
            link.click();
        }

        /** 
         * Given a PDF document object, render the specific page to the main 
         * flipbook canvas element as well as the thumbnail canvas.
         */
        function renderPage(pdfDoc, pageNum) {
            var $canvas = jQuery('<canvas style="width: 100%; height: 100%"></canvas>');
            var $newDiv = jQuery('<div style="width: 100%; height: 100%"></div>');
            $canvas.appendTo($newDiv);
            $newDiv.appendTo(jQuery('.flipbook'));
            var context = jQuery($canvas)[0].getContext('2d');
            
            pdfDoc.getPage(pageNum).then(function(page) {
                // Support HiDPI-screens.
                var outputScale = window.devicePixelRatio || 1;
                var viewport = page.getViewport({ scale: outputScale, });
                    
                $canvas[0].width = Math.floor(viewport.width * outputScale);
                $canvas[0].height = Math.floor(viewport.height * outputScale);

                var transform = outputScale !== 1
                    ? [outputScale, 0, 0, outputScale, 0, 0]
                    : null;

                var renderContext = {
                    canvasContext: context,
                    transform: transform,
                    viewport: viewport
                };

                page.render(renderContext);
                $canvas[0].style.width = "100%";
                $canvas[0].style.height =  "100%";

            });

            var $layoutWrapperThumbnail;
            if ((pageNum == 1) || ((pageNum % 2) == 0)) {
                $layoutWrapperThumbnail = jQuery('<div></div>');
                $layoutWrapperThumbnail.addClass('thumbnail-layout-wrapper');
                $layoutWrapperThumbnail.appendTo(jQuery('.thumbnails-inner'));
                if (pageNum == 1) $layoutWrapperThumbnail.addClass("current");
            } else {
                $layoutWrapperThumbnail = jQuery('.thumbnail-layout-wrapper').last();
            }
            var $canvasThumbnail = jQuery('<canvas style="width: 200px; height: 250px"></canvas>');
            var $linkThumbnail = jQuery('<a href="#page/' + pageNum + ' "></a>');
            $linkThumbnail.click(function(event){
                jQuery('.flipbook').turn('page', pageNum);
                event.stopPropagation();
            });
            var $newDivThumbnail = jQuery('<div class="thumbnail-pdf page-' + pageNum + '"></div>');
            $canvasThumbnail.appendTo($linkThumbnail);
            $linkThumbnail.appendTo($newDivThumbnail);
            $newDivThumbnail.appendTo($layoutWrapperThumbnail);
            var contextThumbnail = jQuery($canvasThumbnail)[0].getContext('2d');
            
            pdfDoc.getPage(pageNum).then(function(page) {
                // Support HiDPI-screens.
                var outputScale = window.devicePixelRatio || 1;
                var viewport = page.getViewport({ scale: 1, });
                viewport = page.getViewport({ scale: (200/viewport.width/outputScale), });
                
                $canvasThumbnail[0].width = Math.floor(viewport.width * outputScale);
                $canvasThumbnail[0].height = Math.floor(viewport.height * outputScale);

                var transform = outputScale !== 1
                    ? [outputScale, 0, 0, outputScale, 0, 0]
                    : null;

                var renderContext = {
                    canvasContext: contextThumbnail,
                    transform: transform,
                    viewport: viewport
                };

                page.render(renderContext);
                $canvasThumbnail[0].style.width = "100%";
                $canvasThumbnail[0].style.height =  "100%";

            });
        }

        /**
         * Asynchronously downloads PDF. Once loaded, triggers rendering
         * of the canvas elements that will hold the PDF pages, and also 
         * triggers the loading of the flipbook JS app.
         */
        pdfjsLib.getDocument(pdf_url).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            for (let index = 1; index <= pdfDoc.numPages; index++) {
                renderPage(pdfDoc, index);
            }
            pdf_loading = false;
            num_pages = pdfDoc.numPages;
            loadApp();
        });
    <?php else: ?>
        /** Special JS when iFrame-based flipbook */
        function embedIFrameContent(fetchurl, embedSourceDiv, embedDestinationDiv){
            let embedDivObj = document.getElementById(embedDestinationDiv);
            if (!embedDivObj) {
                LogError("Failed to get the Destination DIV object. ID: " + embedDestinationDiv);
                return;
            }
            
            fetch(fetchurl, { })
                .then(response => {
                if (!response.ok) {
                    throw new Error("Error occured with getting content of the source URL!");
                }
                return response.text();
                })
                .then(text => {
                const embedparser = new DOMParser();
                const page = embedparser.parseFromString(text, "text/html");
                const content = page.getElementById(embedSourceDiv);
                
                //Approach 1 - inject the code into the page
                //embedDivObj.innerHTML = content.innerHTML;
                //End Approach 1
                
                //Approach 2 - wrap the content with an iframe
                embedDivObj.innerHTML = '';
                const iframe = document.createElement("iframe");
                //iframe.src = fetchurl;
                page.body.innerHTML = content.innerHTML;  //Remove all the body content except for the specific source div we want to preserve
                iframe.srcdoc = page.documentElement.outerHTML;
                embedDivObj.appendChild(iframe);
                embedDivObj.height = '100%';
                embedDivObj.width = '100%';
                
                jQuery(iframe).css({
                    //transition: 'all .5s linear',
                    border: 'none',
                    width: '100%'
                });
                jQuery(iframe).load(function() {
                    jQuery(iframe).height( jQuery(iframe).contents().height() );  //Immediately resize to the document height...
                    function delayed_resize(obj, counter, delay) {
                    //Delayed resize, in case there's javascript that triggers page rendering that changes the size
                    jQuery(obj).height( jQuery(obj).contents().height() );
                    if (counter > 0) {
                        window.setTimeout( delayed_resize, delay , obj, counter-1); 
                    }
                    }
                    window.setTimeout( delayed_resize, 100 , this, 20, 100); // Retry for 2 seconds (20*100milliseconds)
                });
                //End Approach 2
                })
                .catch(error => {
                    embedDivObj.innerHTML = 'Error occured with getting content of the source URL!';
                    console.error(error);
                });
                
        }

        /** 
         * This function is invoked when the user clicks to download the PDF. Will
         * stich together the PDF based on the content that's loaded.
         */
        window.jsPDF = window.jspdf.jsPDF;
        function downloadPDF() {
            var doc = new jsPDF();
            var content;

            jQuery(".flipbook-page").each(function(index, value) {
                content = content + jQuery(value).html();
            });

            doc.html(content, {
            callback: function (doc) {
                doc.save();
            },
            x: 10,
            y: 10
            });
        }
    <?php endif; ?>

    /**
     * Core loading of the flipbook JS app.
     */
    function loadApp() {
        if (typeof pdf_loading !== 'undefined' && pdf_loading) return;

        setCSS();
        jQuery('.flipbook-bar input.total').val(num_pages);

        // Create the flipbook
        jQuery('.flipbook').turn({
            // Width of two pages
            width: page_width*2,
            
            // Height
            height: page_height,
            
            // Elevation
            elevation: 50,
            
            // Enable gradients
            gradients: true,
            
            // Auto center this flipbook
            autoCenter: true,

            // Events
			when: {
                turning: function(event, page, view) { 
                    var book = jQuery(this),
                    currentPage = book.turn('page'),
                    pages = book.turn('pages');

                    // Update the current URI
                    Hash.go('page/' + page).update();

                    // Show and hide navigation buttons
                    disableControls(page);
                    
                    jQuery('.thumbnails .page-'+currentPage).
                        parent().
                        removeClass('current');

                    jQuery('.thumbnails .page-'+page).
                        parent().
                        addClass('current');

                    jQuery('.pages input.number').val(page);
                },

                turned: function(event, page, view) {
                    disableControls(page);

                    jQuery(this).turn('center');

                    if (page==1) { 
                        jQuery(this).turn('peel', 'br');
                    }

                    
                },
            }
        });

        <?php if (!empty($pdf_link)): ?>
        // Zoom only supported by PDF
        // Zoom.js
        jQuery('.flipbook-viewport').zoom({
            flipbook: jQuery('.flipbook'),
            max: function() { 
                return largeFlipbookWidth()/jQuery('.flipbook').width();
            }, 
            when: {
                tap: function(event) {
                    if (jQuery(this).zoom('value')==1) {
                        jQuery('.flipbook').
                            removeClass('animated').
                            addClass('zoom-in');
                            jQuery(this).zoom('zoomIn', event);
                    } else {
                        jQuery(this).zoom('zoomOut');
                    }
                },
                resize: function(event, scale, page, pageElement) {
                    
                },
                zoomIn: function () {
                    //jQuery('.thumbnails').hide();
                    jQuery('.thumbnails').fadeOut(250, "linear");
                    jQuery('.made').hide();
                    jQuery('.flipbook').addClass('zoom-in');
                    jQuery('.flipbook').removeClass('animated').addClass('zoom-in');
                    jQuery('.zoom-icon').removeClass('fa-search-plus').addClass('fa-search-minus');
                    if (!window.escTip && !jQuery.isTouch) {
                        escTip = true;
                        jQuery('<div />', {'class': 'esc'}).
                            html('<div>Press ESC to exit</div>').
                                appendTo(jQuery('body')).
                                delay(2000).
                                animate({opacity:0}, 500, function() {
                                    jQuery(this).remove();
                                });
                    }
                },

               zoomOut: function () {
                    jQuery('.esc').hide();
                    //jQuery('.thumbnails').fadeIn();
                    jQuery('.made').fadeIn();
                    jQuery('.zoom-icon').removeClass('fa-search-minus').addClass('fa-search-plus');
                    setTimeout(function(){
                        jQuery('.flipbook').addClass('animated').removeClass('zoom-in');
                        resizeViewport();
                    }, 0);
                },
                swipeLeft: function() {
                    jQuery('.flipbook').turn('next');
                },
                swipeRight: function() {  
                    jQuery('.flipbook').turn('previous');
                }
            }
        });
        // Zoom icon
        jQuery('.zoom-icon').bind('mouseover', function() { 
            jQuery(this).addClass('zoom-hover');
        }).bind('mouseout', function() { 
            jQuery(this).removeClass('zoom-hover');
        }).bind('click', function() {
            if (jQuery(this).hasClass('fa-search-plus'))
                jQuery('.flipbook-viewport').zoom('zoomIn');
            else if (jQuery(this).hasClass('fa-search-minus'))	
                jQuery('.flipbook-viewport').zoom('zoomOut');
        });
        <?php endif; ?>

        // Download icon
        jQuery('.download-icon').bind('mouseover', function() { 
            jQuery(this).addClass('download-icon-hover');
        }).bind('mouseout', function() { 
            jQuery(this).removeClass('download-icon-hover');
        }).bind('click', function() {
            downloadPDF();
        });


        // Using arrow keys to turn the page
        jQuery(document).keydown(function(e){
            var previous = 37, next = 39, esc = 27;
            switch (e.keyCode) {
                case previous:
                    // left arrow
                    jQuery('.flipbook').turn('previous');
                    e.preventDefault();
                break;
                case next:
                    //right arrow
                    jQuery('.flipbook').turn('next');
                    e.preventDefault();
                break;
                case esc:
                    jQuery('.flipbook-viewport').zoom('zoomOut');	
                    e.preventDefault();
                break;
            }
        });

        // Events for toolbar
        jQuery('.forward-icon').click(function() {
            jQuery('.flipbook').turn('next');
        });
        jQuery('.backward-icon').click(function() {
            jQuery('.flipbook').turn('previous');
        });
        jQuery('.thumbnails-icon').click(function(event) {
            event.stopPropagation();
            jQuery('.thumbnails').fadeToggle(250, "linear");
        });
        jQuery('.pages input.number').change(function() {
            var page = this.value;
			jQuery('.flipbook').turn('page', page);
        });
        if (!document.fullscreenEnabled) {
            jQuery('.fullscreen-icon').parent().hide();
        }
        jQuery('.fullscreen-icon').click(function() {
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else {
                jQuery('.flipbook-viewport').get(0).requestFullscreen();
            }
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

        //Hook when exiting fullscreen
        if (document.addEventListener)
        {
            document.addEventListener('fullscreenchange', flipFullscreenIcon, false);
        }
        function flipFullscreenIcon(event) {
            if (!document.fullscreenElement) {
                jQuery('.fullscreen-icon').addClass("fa-expand-arrows-alt");
                jQuery('.fullscreen-icon').removeClass("fa-compress-arrows-alt");
                jQuery('.fullscreen-icon').attr('title', "Fullscreen");
            } else {
                jQuery('.fullscreen-icon').removeClass("fa-expand-arrows-alt");
                jQuery('.fullscreen-icon').addClass("fa-compress-arrows-alt");
                jQuery('.fullscreen-icon').attr('title', "Exit Fullscreen");
            }
        }

        //When clicking anywhere on the document (meant to catch clicking outside of the thumbnails div)
        jQuery(document).click(function() {
            jQuery('.thumbnails').fadeOut(250, "linear");
        });
        jQuery('.thumbnail-image').click(function(event) {
            event.stopPropagation();
        });
        jQuery('.thumbnail-text').click(function(event) {
            event.stopPropagation();
        });

        //Make the toolbar non-transparent if touch interface
        if (jQuery.isTouch) {
            jQuery('.flipbook-bar').css({opacity: 1});
        }

        // URIs - Format #/page/1 
        Hash.on('^page\/([0-9]*)$', {
            yep: function(path, parts) {
                var page = parts[1];
                    if (page != 1) {
                        jQuery('.thumbnails .page-'+page).
                            parent().
                            addClass('current');
                    }
                if (page!==undefined) {
                    if (jQuery('.flipbook').turn('is'))
                    jQuery('.flipbook').turn('page', page);
                }
            },
            nop: function(path) {
                if (jQuery('.flipbook').turn('is'))
                    jQuery('.flipbook').turn('page', 1);
            }
        });

    }
    // Load the HTML4 version if there's not CSS transform
    yepnope({
        test : Modernizr.csstransforms,
        yep: [ '<?php echo JS.'turn.min.js' ?>' ],
        nope: [ '<?php echo JS.'turn.html4.min.js' ?>' ],
        both: [ '<?php echo JS.'zoom.min.js' ?>', '<?php echo JS.'custom-flipbook.js' ?>', '<?php echo JS.'hash.js' ?>', '<?php echo CSS.'turn.css' ?>' ],
        complete: loadApp
    });

    </script>
    

    <?php 
    $l++;
    endwhile;
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

function strip_quotes($s) {
    return str_replace('\'', '', str_replace('"', '', $s));
}