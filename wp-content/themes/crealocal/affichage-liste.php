<?php if (get_field('contextualites_ou_affichage_liste_page') == 'affichage-liste' && get_field('en_savoir_plus_page')){
		echo '<div id="tertiary" class="affichage-savoir-plus">';
	} else {
		echo '<div id="tertiary">';
	}
?>

	<div class="entry-content">
		<?php
		// Relations entre les post types
		$retrouvez_aussi = '';
		global $relations_names;

		foreach ($relations_names as $relation_name) {
			
			$connected = new WP_Query( array(
				'connected_type' => $relation_name,
				'connected_items' => get_queried_object(),
				'nopaging' => true,
			) );

			if($connected->have_posts()) {
				while ($connected->have_posts()) : $connected->the_post(); 
					$post_thumbnail_id = get_post_thumbnail_id($post->ID);
				    $retrouvez_aussi .= '
				    <section class="item-affichage-liste">
				    	<a href="'.get_the_permalink().'">
				    		'.get_the_post_thumbnail($post->ID, 'little_preview').'
					    	<h4>'.get_the_title().'</h4>
					    	<p class="introduction-affichage">'.get_field('introduction').'</p>
					    	<div class="clearfix"></div>
					    	<p class="resume">'.substr(get_the_excerpt(), 0, 92).'...</p>
				    	</a>
				    </section>';
				endwhile;

				wp_reset_postdata();
			} 

		} ?>

		<?php 
		if(!empty($retrouvez_aussi)) { ?>
			

			<?php
			echo $retrouvez_aussi;
		} ?>
	</div>
</div>
