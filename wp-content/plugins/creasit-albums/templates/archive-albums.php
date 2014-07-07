<?php get_header(); ?>

<?php 
wp_enqueue_style('styles-albums', plugins_url().'/creasit-albums/css/styles-albums.css', array(), '1.0', 'screen, projection'); 
wp_enqueue_script('masonry', plugins_url().'/creasit-albums/js/masonry.pkgd.min.js', array('jquery'), '1.0', true); 
?>

<div id="main-content" class="main-content">
	<div id="content" class="site-content" role="main">

		<?php if ( have_posts() ) : ?>

			<header class="page-header">
				<h1 class="page-title">
					<?php _e( 'Albums', 'solution' ); ?>
				</h1>
			</header>
			
			<div class="liste-album"><?php
				while ( have_posts() ) : the_post(); ?>
					<a href="<?php echo get_permalink(); ?>" class="item-album">
													
						<div>
							<?php
							$medias =  get_field('gallery', get_the_ID()); 
							$countMedia = 0;
							foreach ($medias as $media) {
								if($countMedia < 4) {
									echo wp_get_attachment_image($media, array('150', '150'));
									$countMedia++;
								}
							} ?>

							<p><?php the_title(); ?></p>
						</div>

					</a>
				<?php
				endwhile; ?>
			</div>

		<?php endif; ?>

		<script type="text/javascript">
        jQuery(function($) {
            var $container = $('.liste-album');
            $container.masonry({
              columnWidth: 300,
              itemSelector: '.item-album',
              gutter: 20
            });
        });
        </script>

	</div>
</div>

<?php
get_footer();
?>