<?php get_header(); ?>

<?php if ((get_field('contextualites_ou_affichage_liste_page') == 'affichage-liste' && get_field('en_savoir_plus_page')) || get_field('contextualites_ou_affichage_liste_page') == 'contextualites'){
		echo '<div id="main-content" class="main-content affichage-savoir-plus fleft">';
	} else {
		echo '<div id="main-content" class="main-content">';
	}
?>

	<div id="content" class="site-content" role="main">

		<?php if ( have_posts() ) : the_post(); ?>

		  	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

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
					<?php if(get_the_content()){
						the_content();
					} else {
						
						$children = get_pages('child_of='.$post->ID);
						if( count( $children ) != 0 ){
							echo __('N\'hésiter pas à allez voir ces autres pages :');
							echo '<ul class="show-page-child">';
								wp_list_pages('title_li=&sort_column=menu_order&child_of='.$post->ID);
							echo '</ul>';
						}
					}
					?>

				</div>

			</article>

		<?php endif; ?>

	</div>

</div>

<?php 

$content = get_the_content();
if(!trim($content) == "") {

	if (get_field('contextualites_ou_affichage_liste_page') == 'contextualites'){

		get_sidebar();

	} else if (get_field('contextualites_ou_affichage_liste_page') == 'affichage-liste'){

		if(get_field('en_savoir_plus_page')){

			get_sidebar('affichage');

		}

		get_template_part( 'affichage-liste' );

	}
}

?>



<?php get_footer(); ?>