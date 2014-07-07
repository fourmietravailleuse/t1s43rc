<?php
// Appel du js et css
wp_enqueue_style('styles-caroufredsel', plugins_url().'/creasit-albums/css/styles-polaroid.css', array(), '1.0', 'screen, projection');


$i=0;

$output .= '<div class="polaroid-main">';

foreach ( $images_carousel as $attachment ) {// Si le fichier n'est pas image, il n'apparait pas dans l'album
    $check_file = creasit_albums_check_not_image($attachment);
    if(empty($check_file)) break;

    
	$image = wp_get_attachment_image_src($attachment, array('180', '180'));
	$image_full = wp_get_attachment_image_src($attachment, 'full');
       
    /* Generating random values for the rotation: */
    $rot = rand(-40,40);

    /* Outputting each image: */
    $output .= '
    <div id="pic-'.($i++).'" class="pic" style="background:url('.$image[0].') no-repeat 50% 50% #EEEEEE; -moz-transform:rotate('.$rot.'deg); -webkit-transform:rotate('.$rot.'deg);">

    	<a href="'.$image_full[0].'" rel="gallery-'.$id_carousel.'" class="cboxElement"></a>
    
    </div>
	
	<script type="text/javascript" charset="utf-8">
	jQuery(function($) {

        var widthtWindow;
        var widthtPolaroid;
        var heightPolaroid;

        setTimeout(function() {
            widthtWindow = $(window).width();
            widthtPolaroid = $(".polaroid-main").width();
            heightPolaroid = $(".polaroid-main").height();
        
            /* Generating random values for the position: */
            var leftPic=Math.floor(Math.random()*(widthtPolaroid-100));
            var topPic=Math.floor(Math.random()*(heightPolaroid-100));
            $("#pic-'.$i.'").css({"left":leftPic, "top":topPic});
        }, 100);


	});
	</script>

    ';
    
}

$output .= '</div>';



$output .= '
<script type="text/javascript" charset="utf-8">
jQuery(function($) {
    var widthtWindow = $(window).width();
    var widthtPolaroid = $(".polaroid-main").width();
    $(".polaroid-main").css("height", $(".polaroid-main").width()/2);
    resizePolaroid(widthtWindow, widthtPolaroid)
    
    if(widthtWindow > 480) {
        $(".pic a").colorbox();
    }

    var resize;
    $(window).resize(function() {
        clearTimeout(resize);
        resize = setTimeout(function() {
            widthtWindow = $(window).width();
            window.location.reload();
            resizePolaroid(widthtWindow, widthtPolaroid)

          }, 100);
    });
    

    function resizePolaroid(widthtWindow, widthtPolaroid) {
        if(widthtWindow < widthtPolaroid) {
            $(".polaroid-main").css("width", widthtWindow);
            $(".polaroid-main").css("height", $(".polaroid-main").width());

        } else {
            $(".polaroid-main").css("width", "100%");
            $(".polaroid-main").css("height", $(".polaroid-main").width());
        }
    }
	

	


	


    // Executed once all the page elements are loaded
    var preventClick=false;
    $(".pic a").bind("click",function(e){

        /* This function stops the drag from firing a click event and showing the lightbox */
        if(preventClick)
        {
            e.stopImmediatePropagation();
            e.preventDefault();
        }
    });

    $(".pic").draggable({

        /* Converting the images into draggable objects */
        containment: "parent",
        start: function(e,ui){
            /* This will stop clicks from occuring while dragging */
            preventClick=true;
        },
        stop: function(e, ui) {
            /* Wait for 250 milliseconds before re-enabling the clicks */
            setTimeout(function(){ preventClick=false; }, 250);
        }
    });

    $(".pic").mousedown(function(e){
        /* Executed on image click */
        var maxZ = 0;

        /* Find the max z-index property: */
        $(".pic").each(function(){
            var thisZ = parseInt($(this).css("zIndex"))
            if(thisZ>maxZ) maxZ=thisZ;
        });

        /* Clicks can occur in the picture container (with class pic) and in the link inside it */
        if($(e.target).hasClass("pic"))
        {
            /* Show the clicked image on top of all the others: */
            $(e.target).css({zIndex:maxZ+1});
        }
        else $(e.target).closest(".pic").css({zIndex:maxZ+1});
    });

 });
</script>';