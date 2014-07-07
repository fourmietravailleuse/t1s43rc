<?php 
wp_enqueue_style('styles-albums', plugins_url().'/creasit-albums/css/styles-albums.css', array(), '1.0', 'screen, projection'); 
wp_enqueue_script('masonry', plugins_url().'/creasit-albums/js/masonry.pkgd.min.js', array('jquery'), '1.0', true); 
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('content-album'); ?>>

	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header>

	<div class="entry-content">

		<?php
		// Afficher les images de l'album
		$images =  get_field('gallery', get_the_ID()); 
		if(!empty($images)) { ?>

			<ul>
				<?php
				foreach ($images as $image) {
					$link_img = wp_get_attachment_image_src($image);
					$link_img_full = wp_get_attachment_image_src($image, 'full'); 

					// Si le fichier n'est pas image, il n'apparait pas dans l'album
					$check_file = creasit_check_not_image($image);
					if(!empty($check_file)) { ?>

						<li>
							<a href="<?php echo $link_img_full[0]; ?>" rel="album-name">
								<img src="<?php echo $link_img[0]; ?>" />
							</a>
						</li>

				<?php
					}
				} ?>
			</ul>

		<?php 
		} ?>

	</div>

</article>
