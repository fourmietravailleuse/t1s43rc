<?php
// Appel du js et css
wp_enqueue_script('caroufredsel', plugins_url().'/creasit-albums/js/jquery.carouFredSel-6.2.1-packed.js', array('jquery'), '6.2.1', true);
wp_enqueue_script('caroufredsel-trans', plugins_url().'/creasit-albums/js/jquery.transit.min.js', array('jquery'), '6.2.1', true);
wp_enqueue_style('styles-caroufredsel', plugins_url().'/creasit-albums/css/styles-accordeon.css', array(), '1.0', 'screen, projection');

$output .= '<div id="wrapper">
    <div id="slider">';

$width_px = '640';

$height_accordeon = 0;

foreach ( $images_carousel as $attachment ) {
    // Si le fichier n'est pas image, il n'apparait pas dans l'album
    $check_file = creasit_albums_check_not_image($attachment);
    if(empty($check_file)) break;

    
	$image = wp_get_attachment_image_src($attachment, array($width_px, $width_px));
	$image_full = wp_get_attachment_image_src($attachment, 'full');
    if($image[2] > $height_accordeon) $height_accordeon = $image[2];

    $output .= '
    <a href="javascript:void(0)" data-link="'.$image_full[0].'" rel="gallery-'.$id_carousel.'" class="slide" style="height:'.$image[2].'px; background-image:url('.$image[0].');">
    </a>
    ';
    
}

$output .= '</div></div>';



$output .= '
<script type="text/javascript" charset="utf-8">
jQuery(function($) {
    
                
    $(".slide.active").colorbox();

    $("#slider").carouFredSel({
        width: "100%",
        align: false,
        items: 3,
        items: {
            width: $("#wrapper").width() * 0.15,
            height: '.$height_accordeon.',
            visible: 1,
            minimum: 1
        },
        scroll: {
            items: 1,
            timeoutDuration : 5000,
            onBefore: function(data) {
                
                // Supprimer colorbox
                $(".slide").removeClass("cboxElement");
                $(".slide").attr("href", "javascript:void(0)"); 

                //  find current and next slide
                var currentSlide = $(".slide.active", this),
                    nextSlide = data.items.visible,
                    _width = $("#wrapper").width();

                    


                //  resize currentslide to small version
                currentSlide.stop().animate({
                    width: _width * 0.15
                });     
                currentSlide.removeClass( "active" );
          

                //  hide current block
                data.items.old.add( data.items.visible ).find( ".slide-block" ).stop().fadeOut();                   
 
                //  animate clicked slide to large size
                nextSlide.addClass( "active" );              
                nextSlide.stop().animate({
                    width: _width * 0.7
                });                     
    

            },
            onAfter: function(data) {
                //  show active slide block
                data.items.visible.last().find( ".slide-block" ).stop().fadeIn();

                // Créer le lien pour colorbox
                var currentSlide = $(".slide.active");
                var link = currentSlide.attr("data-link");
                currentSlide.attr("href", link); 
                currentSlide.colorbox(); 
            }
        },
        onCreate: function(data){
 
            //  clone images for better sliding and insert them dynamacly in slider
            var newitems = $(".slide",this).clone( true ),
                _width = $("#wrapper").width();
 
            $(this).trigger( "insertItem", [newitems, newitems.length, false] );
 
            //  show images 
            $(".slide", this).fadeIn();
            $(".slide:first-child", this).addClass( "active" );
            $(".slide", this).width( _width * 0.15 );

            
 
            //  enlarge first slide
            $(".slide:first-child", this).animate({
                width: _width * 0.7
            });

            var link = $(".slide:first-child", this).attr("data-link");
            $(".slide:first-child", this).attr("href", link); 
            $(".slide:first-child", this).colorbox(); 

 
            //  show first title block and hide the rest
            $(this).find( ".slide-block" ).hide();
            $(this).find( ".slide.active .slide-block" ).stop().fadeIn();
        }
    });
 
    //  Handle click events
    $("#slider").children().click(function() {
        $("#slider").trigger( "slideTo", [this] );
    });
 
    //  Enable code below if you want to support browser resizing
    $(window).resize(function(){

        var widthtWindow = $(window).width();
        var slider = $("#slider"),
            _width = $("#wrapper").width();

        if(widthtWindow < _width) {
            $("#wrapper").css("width", widthtWindow);
        } else {
            $("#wrapper").css("width", "100%");
        }


        //  show images
        slider.find( ".slide" ).width( $("#wrapper").width() * 0.15 );
 
        //  enlarge first slide
        slider.find( ".slide.active" ).width( $("#wrapper").width() * 0.7 );
 
        //  update item width config
        slider.trigger( "configuration", ["items.width", $("#wrapper").width() * 0.15] );
    });
 
    
 });
</script>';