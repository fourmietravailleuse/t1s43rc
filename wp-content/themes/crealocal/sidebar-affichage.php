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
			</ul>
		</div>

	
</div>
