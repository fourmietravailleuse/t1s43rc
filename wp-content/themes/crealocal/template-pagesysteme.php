<?php
/*
Template Name Posts: Page Systeme
*/
?>


<?php get_header(); ?>

<div id="main-content" class="main-content">

	<div id="content" class="site-content" role="main">

		  <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<?php /* the_thumbnail(); */ ?>

				<header class="entry-header">
					<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
					<?php 

					// Boutons de partage 
					get_template_part('parts/partage'); 
					?>
				</header>

				<?php if (get_field('introduction')){ ?>
					<div class="introduction">
						<p><?php the_field('introduction'); ?></p>
					</div>
				<?php } ?>

				<div class="entry-content">
					<?php
						the_content();
					?>

				</div>

			</article>
	</div>

</div>

<?php get_footer(); ?>