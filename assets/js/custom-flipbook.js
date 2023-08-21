

// http://code.google.com/p/chromium/issues/detail?id=128488

function isChrome() {

	return navigator.userAgent.indexOf('Chrome')!=-1;

}

function disableControls(page) {
		if (page==1)
			jQuery('.previous-button').hide();
		else
			jQuery('.previous-button').show();
					
		if (page==jQuery('.flipbook').turn('pages'))
			jQuery('.next-button').hide();
		else
			jQuery('.next-button').show();
}

// Set the width and height for the viewport

function resizeViewport() {

	var options = jQuery('.flipbook').turn('options');
    var height = options.height + 200;
    var width = jQuery(window).width();
		

	jQuery('.flipbook').removeClass('animated');

	jQuery('.flipbook-viewport').css({
		//width: width,
		height: height
	}).
	zoom('resize');

	if (jQuery('.flipbook').turn('zoom')==1) {
		var bound = calculateBound({
			width: options.width,
			height: options.height,
			boundWidth: Math.min(options.width, width),
			boundHeight: Math.min(options.height, height)
		});

		if (bound.width%2!==0)
			bound.width-=1;

		if (bound.width!=jQuery('.flipbook').width() || bound.height!=jQuery('.flipbook').height()) {

			jQuery('.flipbook').turn('size', bound.width, bound.height);

			if (jQuery('.flipbook').turn('page')==1)
				jQuery('.flipbook').turn('peel', 'br');

			jQuery('.next-button').css({height: bound.height, backgroundPosition: '-38px '+(bound.height/2-32/2)+'px'});
			jQuery('.previous-button').css({height: bound.height, backgroundPosition: '-4px '+(bound.height/2-32/2)+'px'});
		}

		jQuery('.flipbook').css({top: -bound.height/2, left: -bound.width/2});
	}

	var flipbookOffset = jQuery('.flipbook').offset(),
		boundH = height - flipbookOffset.top - jQuery('.flipbook').height(),
		marginTop = (boundH - jQuery('.thumbnails > div').height()) / 2;

	if (marginTop<0) {
		jQuery('.thumbnails').css({height:1});
	} else {
		jQuery('.thumbnails').css({height: boundH});
		jQuery('.thumbnails > div').css({marginTop: marginTop});
	}

	if (flipbookOffset.top<jQuery('.made').height())
		jQuery('.made').hide();
	else
		jQuery('.made').show();

	jQuery('.flipbook').addClass('animated');
	
}

// Width of the flipbook when zoomed in

function largeFlipbookWidth() {
	
	return 2214;

}

// decode URL Parameters

function decodeParams(data) {

	var parts = data.split('&'), d, obj = {};

	for (var i =0; i<parts.length; i++) {
		d = parts[i].split('=');
		obj[decodeURIComponent(d[0])] = decodeURIComponent(d[1]);
	}

	return obj;
}

// Calculate the width and height of a square within another square

function calculateBound(d) {
	
	var bound = {width: d.width, height: d.height};

	if (bound.width>d.boundWidth || bound.height>d.boundHeight) {
		
		var rel = bound.width/bound.height;

		if (d.boundWidth/rel>d.boundHeight && d.boundHeight*rel<=d.boundWidth) {
			
			bound.width = Math.round(d.boundHeight*rel);
			bound.height = d.boundHeight;

		} else {
			
			bound.width = d.boundWidth;
			bound.height = Math.round(d.boundWidth/rel);
		
		}
	}
		
	return bound;
}