<?php 
	/************** SETTINGS ***********************/
	$num_photos_between_promos = 3 ;
	$seconds_per_slide = 5;
	$num_seconds_to_show_new = 10;
	$DEBUG =  false;

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
		var promo_prev = -1;
		var add_image_running = 0;

		function mylog(stuff){
			<?php if($DEBUG){ 
				echo 'console.log(stuff);';
			} ?>
		}

		function resume_show(){
			//$('.cycle-slideshow').cycle('reinit') //delay for 10,000 miliseconds before resuming
			$('.cycle-slideshow').cycle('resume') //delay for 10,000 miliseconds before resuming
		}

		/**
		 *  * Returns a random integer between min and max
		 *   * Using Math.round() will give you a non-uniform distribution!
		 *    */
		 function getRandomInt (min, max) {
		 	return parseInt(Math.floor(Math.random() * (max - min + 1)) + min);
		 }

		 function add_images(img_list_url, callback) {
		 	if(add_image_running) return;
		 	add_image_running = 1;
			img_list_url = img_list_url || '/images.php';
			$.get(img_list_url, function(data){
				all_images = data.split(',');
				var new_images = $(all_images).not(images).get();	
				//mylog("all images, old list, diff ")
				//mylog(all_images);
				//mylog(images);
				//mylog(new_images);
				if(new_images.length > 0){
					images = images.concat(new_images);
					for (var i=0; i < new_images.length; i++) {
						//mylog('Adding ' + new_images[i] + ' to slideshow;');
						$('.cycle-slideshow').cycle('add', '<img src="' + new_images[i] +'"/>' );
					}
					$(this).prop('disabled', true)
					mylog(images);


					//extra delay after adding new photos, except for the first time.
					if(slides_shown > 1){
						//mylog(images);
						//mylog('going to slide'+ images.length -1);
						$('.cycle-slideshow').cycle('goto', images.length-1); //show new slide

						mylog('pausing for 10s since a new photo was added');
						$('.cycle-slideshow').cycle('pause');
						timeoutID = window.setTimeout(resume_show, (1000* <?php echo $num_seconds_to_show_new; ?>)); // this is the extra time delay a newly added photo is displayed
					} 
					if(img_list_url.search('promo') > 0){
						promo_count = new_images.length;
						mylog('promo_count = ' + promo_count);
					}
				}
			});
			add_image_running = 0;
			if(typeof callback !== 'undefined'){
				//$('.cycle-slideshow').cycle('goto',(promo_count+1)); //skip promos first time
				callback();
			}
		} //end add_images fn


		$(document).on('cycle-after', function(event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag){
			add_images();
			slides_shown +=1;			
		})
		
		$(document).on('cycle-before', function(event, optionHash, outgoingSlideEl, incomingSlideEl, forwardFlag){
			//mylog(incomingSlideEl);
		})
	
		$(document).on( 'cycle-bootstrap', function( e, optionHash, API ) {
			API.log('setting up our custom bootstrap');

			var origCalcNextSlide = API.calcNextSlide;
			API.calcNextSlide = function(){
				//mylog('called calcNextSlide');
				var opts = this.opts();
				var roll;
				var roll2;
				var i=0;
				//origCalcNextSlide.call(API); //do the normal stuff
				
					roll = (opts.nextSlide + 1) == opts.slides.length;
					opts.nextSlide = roll ? 0 : opts.nextSlide+1;
					opts.currSlide = roll ? opts.slides.length-1 : opts.nextSlide-1;
					
					do
						if(slides_shown % <?php echo 1+$num_photos_between_promos; ?> == 0){
							//opts.nextSlide = getRandomInt(0, promo_count-1);	//get random promo slide zero-index num
							roll2 = (promo_prev +1) == promo_count;
							//mylog('promo_prev = '+promo_prev+'; roll2 = '+ roll2);
							opts.nextSlide = roll2 ? 0 : promo_prev+1;
							promo_prev = opts.nextSlide;
							mylog('showing promo '+ opts.nextSlide);
						} else {
							opts.nextSlide = getRandomInt(promo_count, opts.slideCount -1);	//get random promo slide zero-index num
							//opts.nextSlide = getRandomInt(0, opts.slideCount -1);	//get random promo slide zero-index num
						}
					while(opts.nextSlide == opts.currSlide && i++ < 10); //so we don't get stuck on the same slide

				mylog(slides_shown +'. after : '+ (1+opts.currSlide) + ' -> ' + (1+opts.nextSlide));

			}
		})
  
		$('#slideshow').addClass('cycle-slideshow').cycle().animate({},2000, function(){
				add_images('/images.php?promos=true', add_images);  //this is the initial load of images, with a callback to make it synchronous
		});

		$('h1').click(function(){
			if($('.cycle-paused').length){
				$('.cycle-slideshow').cycle('resume');
			} else {
				$('.cycle-slideshow').cycle('pause');
			}
		})
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
	    font-family: arial; font-size: 15px; width: 18%; height: 16px; 
		display: block; float:left; color: #eee; cursor: pointer; 
		     background-color: #333;
			border-radius: 4px;
			margin: 0 0 1% 1%;
			text-indent: 100%; 
			opacity: 0.3;
			text-align: center;
			overflow: hidden;
			white-space: nowrap;
			
	}
	.debug .cycle-pager span { 
		text-indent: 0;
		opacity: 1;
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
<body class="<?php if($DEBUG){ echo debug; }?>">

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
		data-cycle-log="<?php echo $DEBUG; ?>"
		disable-data-cycle-loader="true"
		data-cycle-pager-template="<span>{{slideNum}}</span>"
    >
    <div class="cycle-pager"></div>
</div>



<div style="" id="sidebar">
<h1>RAISE YOUR VOICE!</h1>
<div id="footer">ryv-rbc.com</div>
</div>

</body>
</head>
