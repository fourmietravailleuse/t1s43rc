<div id="secondary">

		<div id="primary-sidebar" class="primary-sidebar" role="complementary">
			<ul>
				<?php $texte_libre = get_field('en_savoir_plus_page');
				if(!empty($texte_libre)) { ?>
					<li>
						<h2><?php _e('En savoir +', 'solution'); ?></h2>
						
						<div><?php echo $texte_libre; ?></div>

					</li>
				<?php
				} ?> 

				
				

				<?php
				// Relaions entre les post types
				$retrouvez_aussi = '';
				global $relations_names;

				foreach ($relations_names as $relation_name) {
					
					$connected = new WP_Query( array(
						'connected_type' => $relation_name,
						'connected_items' => get_queried_object(),
						'nopaging' => true,
					) );

					if($connected->have_posts()) {
						switch ($connected->post->post_type) {
							case 'post':
								$h3 = 'Actualiés';
								break;
							
							case 'page':
								$h3 = 'Pages';
								break;
							
							case 'albums':
								$h3 = 'Albums';
								break;								
						}  
						
						$retrouvez_aussi .= '
						<h3>'.__($h3, 'solution').'</h3>

						<ul>';
						while ($connected->have_posts()) : $connected->the_post(); 
						    $retrouvez_aussi .= '<li>
						    	<a href="'.get_the_permalink().'">'.get_the_title().'</a>
						    </li>';
						endwhile;
						$retrouvez_aussi .= '</ul>';

						wp_reset_postdata();
					} 

				} ?>

				<li>
					<?php 
					if(!empty($retrouvez_aussi)) { ?>
						<h2><?php _e('Retrouvez aussi', 'solution'); ?></h2>

						<?php
						echo $retrouvez_aussi;
					} ?>
				</li>

			</ul>
		</div>

	
</div>
