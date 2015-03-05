<?php 
	/************** SETTINGS ***********************/
	$num_photos_between_promos = 3;
	$seconds_per_slide = 2;

?>
<html>
<head>
<script src="/jquery.min.js"></script>
<script src="/jquery.cycle2.min.js"></script>
<script src="/jquery.cycle2.scrollVert.min.js"></script>
<!-- script to add more images at a later time -->
<script>
jQuery('document').ready(function($){
		var images = [];
		var slides_shown = 0;
		var promo_count = 0;
		var next_slide = 0;
		var slides_since_promo = 1;		
		var add_image_running = 0;

		function resume_show(){
			//$('.cycle-slideshow').cycle('reinit') //delay for 10,000 miliseconds before resuming
			$('.cycle-slideshow').cycle('resume') //delay for 10,000 miliseconds before resuming
		}

		/**
		 *  * Returns a random integer between min and max
		 *   * Using Math.round() will give you a non-uniform distribution!
		 *    */
		 function getRandomInt (min, max) {
		 	return Math.floor(Math.random() * (max - min + 1)) + min;
		 }

		 function add_images(img_list_url, callback) {
		 	if(add_image_running) return;
		 	add_image_running = 1;
			img_list_url = img_list_url || '/images.php';
			$.get(img_list_url, function(data){
				all_images = data.split(',');
				var new_images = $(all_images).not(images).get();	
				//console.log("all images, old list, diff ")
				//console.log(all_images);
				//console.log(images);
				//console.log(new_images);
				if(new_images.length > 0){
					images = images.concat(new_images);
					for (var i=0; i < new_images.length; i++) {
						console.log('Adding ' + new_images[i] + ' to slideshow;');
						$('.cycle-slideshow').cycle('add', '<img src="' + new_images[i] +'"/>' );
					}
					$(this).prop('disabled', true)


					//extra delay after adding new photos, except for the first time.
					if(slides_shown > 1){
						//console.log(images);
						//console.log('going to slide'+ images.length -1);
						$('.cycle-slideshow').cycle('goto', images.length-1); //show new slide

						console.log('pausing for 10s since a new photo was added');
						$('.cycle-slideshow').cycle('pause');
						timeoutID = window.setTimeout(resume_show, 10000); // this is the extra time delay a newly added photo is displayed
					} 
					if(img_list_url.search('promo') > 0){
						promo_count = new_images.length;
						console.log('promo_count = ' + promo_count);

					}

				}
			});
			add_image_running = 0;
			if(typeof callback !== 'undefined'){
				callback();
			}
		} //end add_images fn


		//setInterval(function(){ add_images() }, 5000);
		//setInterval(function(){ show_promo() }, 20000); //show promo every 20000 miliseconds
/*

		$('.cycle-slideshow').on('cycle-before', function(event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag){
			slides_shown +=1;
			console.log(incomingSlideEl);
			console.log('this slide= '+  optionHash.currSlide +'; slides_since_promo = ' + slides_since_promo + '; next_slide = '+next_slide);
			if(forwardFlag){
				add_images();
				if(slides_since_promo == 0) {
			        slides_since_promo += 1;
					//$('.cycle-slideshow').cycle('goto', next_slide+1); //show new slide
				} else if(slides_since_promo > <?php echo $num_photos_between_promos; ?>){
					next_slide = optionHash.currSlide;
					promo_num = getRandomInt(0, promo_count-1);	
					//optionHash.nextSlide = promo_num;
					console.log('showing promo index #'+promo_num +' of ' + promo_count +' next.');
					slides_since_promo = 0;
					$('.cycle-slideshow').cycle('goto', promo_num); 
				} else {
			        //optionHash.nextSlide = optionHash.currSlide + val;
			        slides_since_promo += 1;
				}
			}

		})
		*/

		
		$(document).on( 'cycle-bootstrap', function( e, optionHash, API ) {
			console.log('setting up our custom bootstrap');
		    // replace "advanceSlide" method with custom impl
		    //console.log(API);

		    var origJump = API.jump;
		    API.jump = function(n) {
		    	console.log('api jump override called!');
		    }

		    API.advanceSlide = function( numberOfPositions ) {
		    	console.log('custom api advancing slide');
	    	    var optionHash = this.optionHash();
		        clearTimeout(optionHash.timeoutId);
		        optionHash.timeoutId = 0;
				console.log('slides_since_promo = ' + slides_since_promo);
				if(slides_since_promo > <?php echo $num_photos_between_promos; ?>){
					promo_num = getRandomInt(0, promo_count-1);	
					optionHash.nextSlide = promo_num;
					console.log('showing promo index #'+promo_num +' of ' + promo_count +' next.');
					slides_since_promo = 0;
				} else {
			        optionHash.nextSlide = optionHash.currSlide + val;
			        slides_since_promo += 1;
				}

		        if (optionHash.nextSlide < 0)
		            optionHash.nextSlide = optionHash.slides.length - 1;
		        else if (optionHash.nextSlide >= optionHash.slides.length)
		            optionHash.nextSlide = 0;

		        optionHash.API.prepareTx( true,  val >= 0 );
		        return false;
		    }
		    console.log(API);

		})
		
			   
		$('#slideshow').addClass('cycle-slideshow').cycle();
		add_images('/images.php?promos=true', add_images);  //this is the initial load of images, with a callback to make it synchronous
	
	});
</script>
<style>
	* { box-sizing: border-box; }
	body { margin:0; padding:0; color: black; background: yellow; }
	img { max-width: 100%; height: 100%; margin:0; }
    #sidebar { width: 25%;  padding: 1em 50px; box-sizing:border-box; font-family: "HelveticaNeue-CondensedBold", Helvetica, sans-serif; text-align: center; position:absolute; right:0; top:0; height: 100%; font-stretch: ultra-condensed;  }
	#sidebar h1 { font-weight: 700; font-size: 160px; line-height: 1em; z-index:99999; position:relative;}
	#sidebar #footer { font-size: 40px; width: 100%; right: 0; text-align:center; z-index:99999; position:relative;}
	/* pager */
	.cycle-pager { 
		display: block; z-index: 89999; z-index: 500; position: absolute; bottom: 0px; right:0px; width: 24%; overflow: hidden;
	}
	.cycle-pager span { 
	    font-family: arial; font-size: 50px; width: 19%; height: 16px; 
		display: block; float:left; color: #333; cursor: pointer; 
		     background-color: #333;
			border-radius: 4px;
			margin: 0 0 1% 1%;
			text-indent: 100%;
			overflow: hidden;
			white-space: nowrap;
			opacity: 0.3;
	}
	.cycle-pager span.cycle-pager-active {background-color: red; color: #D69746;}
	.cycle-pager > * { cursor: pointer;}


	/* caption */
	.cycle-caption { position: absolute; color: white; bottom: 15px; right: 15px; z-index: 700; }

	/* prev / next links */
	.cycle-prev, .cycle-next { position: absolute; top: 0; width: 30%; opacity: 0; filter: alpha(opacity=0); z-index: 800; height: 100%; cursor: pointer; }
	.cycle-prev { left: 0;  background: url(http://malsup.github.com/images/left.png) 50% 50% no-repeat;}
	.cycle-next { right: 0; background: url(http://malsup.github.com/images/right.png) 50% 50% no-repeat;}
	.cycle-prev:hover, .cycle-next:hover { opacity: .7; filter: alpha(opacity=70) }

	.disabled { opacity: .5; filter:alpha(opacity=50); }


	/* display paused text on top of paused slideshow */
	.cycle-paused:after {
	    content: 'Paused'; color: white; background: black; padding: 10px;
	    z-index: 500; position: absolute; top: 10px; right: 10px;
	    border-radius: 10px;
	    opacity: .5; filter: alpha(opacity=50);
	}
	@media screen and (min-width : 3400px) and (device-aspect-ratio: 16/9){
		#sidebar h1 { font-size:320px; line-height: 1.5em; }
	}

</style>
</head>
<body>

<?php 
/*****************************************************************************
 * THERE ARE A FEW VARIABLES YOU MIGHT WANT TO TWEAK
 * data-cycle-timeout is the number of milliseconds to show each slide
 * and setInterval 5000 means 5 seconds between filesystem refreshes
 ******************************************************************************/ 
?>
<div id="slideshow" class="dis-cycle-slideshow" style="max-width: 100%; max-height: 100%;"
        data-cycle-fx="scrollVert" 
	   	data-cycle-timeout="<?php echo ($seconds_per_slide*1000); ?>"
		data-cycle-pager-event="mouseover"
		data-cycle-pause-on-hover="false"
		disable-data-cycle-random="true"
		data-cycle-reverse="false"
		data-cycle-log="true"
		disable-data-cycle-loader="true"
    >
    <div class="cycle-pager"></div>
</div>



<div style="" id="sidebar">
<h1>RAISE YOUR VOICE!</h1>
<div id="footer">ryv-rbc.com</div>
</div>

</body>
</head>
