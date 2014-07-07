<?php
get_header(); ?>


	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

			<header class="page-header">
				<h1 class="page-title"><?php _e( 'Page introuvable', 'crealocal' ); ?></h1>
			</header>

			<div class="page-content">
				<p><?php _e( 'Cette page n\'existe pas, vous pouvez utiliser le moteur de recherche.', 'crealocal' ); ?></p>

				<?php get_search_form(); ?>
			</div>

		</div>
	</div>

<?php
get_footer();
