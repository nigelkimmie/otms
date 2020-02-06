jQuery(function ($) {
   /** ******************************
	 * Lightbox
	 ****************************** **/
	$('#slider2').flexslider({
		animation: "fade",			// String: Select your animation type, "fade" or "slide"
		direction: "horizontal",	// String: Select the sliding direction, "horizontal" or "vertical"
		smoothHeight: true,			// Boolean: Allow height of the slider to animate smoothly in horizontal mode
		controlNav: true,			// Boolean: Create navigation for paging control of each slide
		directionNav: false,		// Boolean: Create navigation for previous/next navigation (true/false)
		animationLoop: true,		// Boolean: Should the animation loop?
		slideshowSpeed: 5000,		// Integer: Set the speed of the slideshow cycling, in milliseconds
		slideshow: true,			// Boolean: Animate slider automatically
		pauseOnAction: true,		// Boolean: Pause the slideshow when interacting with control elements
		pauseOnHover: true,			// Boolean: Pause the slideshow when hovering over slider, then resume when no longer hovering
		touch: true					// Boolean: Allow touch swipe navigation of the slider on touch-enabled devices
	}); 
});