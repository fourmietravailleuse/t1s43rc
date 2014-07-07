<?php
// Appel du js et css
wp_enqueue_script('perspective', plugins_url().'/creasit-albums/js/coverflow.min.js', array('jquery'), '6.2.1', true);
wp_enqueue_script('smartresize', plugins_url().'/creasit-albums/js/jquery.smartresize.js', array('jquery'), '6.2.1', true);
wp_enqueue_style('coverflow', plugins_url().'/creasit-albums/css/coverflow.css', array(), '1.0', 'screen, projection');
wp_enqueue_style('styles-perspective', plugins_url().'/creasit-albums/css/styles-perspective.css', array(), '1.0', 'screen, projection');

$output .= '<div class="perspective-main container"><div class="perspective-wrapper"><div id="coverflow">';

$width_px = '300';
$count_img = 0;

foreach ( $images_carousel as $attachment ) {
	// Si le fichier n'est pas image, il n'apparait pas dans l'album
    $check_file = creasit_albums_check_not_image($attachment);
    if(empty($check_file)) break;

    
	$image = wp_get_attachment_image($attachment, array($width_px, $width_px));
	$image_full = wp_get_attachment_image_src($attachment, 'full');

	$output .= '<a href="'.$image_full[0].'" rel="gallery-'.$id_carousel.'">'.$image.'</a>';

	$count_img++;
}

$output .= '</div></div>';

$output .= '<div class="perspective-direction-nav">
                <a class="perspective-prev" href="javascript:void(0)"></a>
                <a class="perspective-next" href="javascript:void(0)"></a>
            </div>
        </div>';



$output .= '
<script type="text/javascript" charset="utf-8">
jQuery(function($) {
	var height_perspective = 0;
	setTimeout(function() {
	    $("#coverflow a").each(function(i) {
			var height = $(this).height();
			if(height > height_perspective) {
				height_perspective = height;
			}
		});
	
		$(".perspective-wrapper").css("height", height_perspective);         
    }, 100);


	$coverflow = $("#coverflow").coverflow(); 

    $(window).smartresize(function() {
		$coverflow.coverflow();
	});



	i = 0;
	var nb_img = ('.$count_img.' - 1);
	$(".perspective-next").click(function() {
        i++; 
		
		if(i > nb_img) { i = 0; }
        $("#coverflow").coverflow("select", i);           
    });

    $(".perspective-prev").click(function() {
        i--; 
        if(i < 0) { i = nb_img; }
        $("#coverflow").coverflow("select", i);           
    });

        
 });
</script>';


