<?php
/*
Template Name Posts: Plan du site
*/

get_header(); ?>

<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

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

						<div class="pages fleft">
							<h2><?php _e('Pages', 'solution'); ?></h2>
							<ul><?php wp_list_pages(array("title_li" => "", "post_type" => 'page' )); ?></ul>
						</div>
						
						<div class="pages fright">
							<h2><?php _e('Pages Système', 'solution'); ?></h2>
							<ul><?php wp_list_pages(array("title_li" => "", "post_type" => 'page-systeme' )); ?></ul>
						</div>
						
						<div class="pages fright">
							<h2><?php _e('Actualités', 'solution'); ?></h2>
							<ul>
								<?php $archive_query = new WP_Query('showposts=1000'); 
								while ($archive_query->have_posts()) : $archive_query->the_post(); ?>
									<li>
										<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>">
											<?php the_title(); ?>
										</a>
									</li>
								<?php endwhile; ?>
							</ul>
						</div>

					</div>

				</article>
		</div>

	</div>

<?php endwhile; endif; ?>

<?php get_footer(); ?>