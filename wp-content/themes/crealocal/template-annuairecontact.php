<?php
/*
Template Name: Annuaire de contact
*/

get_header(); ?>

<div id="main-content" class="main-content">

	<div id="primary" style="width: auto;">
		<div id="content" class="site-content" role="main">
			
			<h1><?php the_title(); ?></h1>

			<div class="entry-content"><?php the_content(); ?></div>
			
			<div class="form-annuaire">
				<?php 
				// args_tax de requête pour les taxo's
				$args_tax = array('orderby' => 'name', 'order' => 'ASC','hide_empty' => false); 
				// Tableaux pour vérifié les recherches des utilisateurs
				$tab_type = array();
				$tab_categories = array();
				// Récupèrer tout les types et toutes les catégories
				$all_type = get_terms('type', $args_tax);
				$all_categorie = get_terms('categories', $args_tax);

				// Check si vide et init variable de type et catégoerie
				$type = (empty($_REQUEST['type'])) ? '' : $_REQUEST['type'];
				$categories = (empty($_REQUEST['categories'])) ? '' : $_REQUEST['categories'];
				?>
				<form action="" methode="GET">
					<p>
						<label for="type"><?php _e('Type', 'solution'); ?></label>
						<select name="type" id="type">
							<option value=""></option>
							<?php 
							foreach ($all_type as $t) { 
								$selected_type = '';
								if($type == $t->slug) $selected_type = 'selected="selected"';
								$tab_type[] = $t->slug; ?>
								<option value="<?php echo $t->slug; ?>"<?php echo $selected_type; ?>><?php echo $t->name; ?></option>
							<?php
							} ?>
						</select>
					</p>
					<p>
						<label for="categories"><?php _e('Catégorie', 'solution'); ?></label>
						<select name="categories" id="categories">
							<option value=""></option>
							<?php 
							foreach ($all_categorie as $cat) { 
								$selected_categories = '';
								if($categories == $cat->slug) $selected_categories = 'selected="selected"';
								$tab_categories[] = $cat->slug; ?>
								<option value="<?php echo $cat->slug; ?>"<?php echo $selected_categories; ?>><?php echo $cat->name; ?></option>
							<?php
							} ?>
						</select>
					</p>

					<input type="submit" value="Valider">

				</form>
			</div>
			
			<?php
			// Tri avec le type et la catégorie
			if(isset($type) && !empty($type) && isset($categories) && !empty($categories)) {
				$args = array(
					'post_type' => 'contacts',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'tax_query' => array(
									'relation' => 'AND',
									array(
										'taxonomy' => 'type',
										'field' => 'slug',
										'terms' => $type
									),
									array(
										'taxonomy' => 'categories',
										'field' => 'slug',
										'terms' => $categories
									)
								)
				);
			} 

			// Tri avec le type
			else if(in_array($type, $tab_type) && empty($categories)) {
				$args = array(
					'post_type' => 'contacts',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'tax_query' => array(
									array(
										'taxonomy' => 'type',
										'field' => 'slug',
										'terms' => $type
									)
								)
				);
			}

			// Tri avec la catégorie
			else if(in_array($categories, $tab_categories) && empty($type)) {
				$args = array(
					'post_type' => 'contacts',
					'post_status' => 'publish',
					'posts_per_page' => -1,
					'tax_query' => array(
									array(
										'taxonomy' => 'categories',
										'field' => 'slug',
										'terms' => $categories
									)
								)
				);
			}

			// Aucun tri
			else {
				$args = array(
					'post_type' => 'contacts',
					'post_status' => 'publish',
					'posts_per_page' => -1,
	            );
			}
            $contacts = new WP_Query( $args );
            if ($contacts->have_posts()) {
				$class = '';
				$modulo = 0; ?>
				
				<ul class="annuaire-contacts">
					<?php
					while ($contacts->have_posts()) {
						$contacts->the_post(); 

						// Affichage alterné grâce au modulo
						$modulo % 2 == 1 ? $class = 'class="item-odd"' : $class = 'class="item-even"';
						$modulo++; 

						// Récup des infos
						$img_contact = get_post_meta(get_the_ID(), 'img_contact', true );
						$nom = get_the_title();
						$adresse_contact = get_post_meta(get_the_ID(), 'adresse_contact', true );
						$code_postal_contact = get_post_meta(get_the_ID(), 'code_postal_contact', true );
						$commune_contact = get_post_meta(get_the_ID(), 'commune_contact', true );
						$telephone_contact = get_post_meta(get_the_ID(), 'telephone_contact', true );
						$portable_contact = get_post_meta(get_the_ID(), 'portable_contact', true );
						$telecopie_contact = get_post_meta(get_the_ID(), 'telecopie_contact', true );
						$email_contact = get_post_meta(get_the_ID(), 'email_contact', true );
						$anonymat_contact = get_post_meta(get_the_ID(), 'anonymat_contact', true );
						$site_contact = get_post_meta(get_the_ID(), 'site_contact', true );
						$type_contact = wp_get_post_terms(get_the_ID(), 'type' );
						$categories_contact = wp_get_post_terms(get_the_ID(), 'categories' );
						?>
						
						<li <?php echo $class; ?>>
							<div>
								<?php if(!empty($nom)) echo '<p>'.$nom.'</p>'; ?>

								<?php    
							    if(!empty($img_contact)) {
							        $image = wp_get_attachment_image_src($img_contact, 'little_preview'); 
							        if(!empty($nom)) $alt = 'alt="'.__("Image du contact", "solution").' '.$nom.'"';

							        echo '<img src="'.$image[0].'" '.$alt.'>';
							    } ?>
							</div>

							
							<div class="type">
								<?php 
								echo '<span>'.__('Type', 'solution').' :</span> ';
								if(!empty($type_contact)) {
									foreach ($type_contact as $key => $t) {
										$key != 0 ? $virgule = ', ': $virgule = '';
										echo $virgule.$t->name;
									}
								} ?>
							</div>


							<div class="categorie">
								<?php 
								echo '<span>'.__('Catégories', 'solution').' :</span> ';
								if(!empty($categories_contact)) {
									foreach ($categories_contact as $key => $cat) {
										$key != 0 ? $virgule = ', ': $virgule = '';
										echo $virgule.$cat->name;							
									}
								} ?>
							</div>


							<div class="adresse">
								<?php
								if(!empty($adresse_contact)) 
									echo '<p>'.$adresse_contact.'</p>';

								if(!empty($code_postal_contact) && !empty($commune_contact)) 
									echo '<p>'.$code_postal_contact.' '.$commune_contact.'</p>';

								if(!empty($telephone_contact)) 
									echo '<p>'.__('Tél', 'solution').' :'.$telephone_contact.'</p>';

								if(!empty($portable_contact)) 
									echo '<p>'.__('Portable', 'solution').' :'.$portable_contact.'</p>';

								if(!empty($telecopie_contact)) 
									echo '<p>'.__('Fax', 'solution').' :'.$telecopie_contact.'</p>';

								if(!empty($email_contact) && empty($anonymat_contact)) 
									echo '<p><a href="mailto:'.$email_contact.'" title="Ecrire à '.$email_contact.'">'.$email_contact.'</a></p>';
								// TODO : else envoyer vers un formulaire pour contact avec $email_contact en destinataire


								if(!empty($site_contact)) 
									echo '<a href="'.check_http($site_contact).'" target="_blank" title="Ouvrir la page : '.$site_contact.' (site externe : nouvelle fenêtre)">'.$site_contact.'</a>';
								?>
							</div>


							<div class="fiche">
								<?php
								echo '<a href="'.get_permalink().'">'.__('Voir la fiche complète', 'solution').'</a>';
								?>
							</div>
						</li>
					<?php
					} ?>
				</ul>
			<?php 
			}  
			else { ?>
				<h3><?php _e('Aucun résultat, essayez une nouvelle recherche', 'solution'); ?></h3>
			<?php
			} ?>

		</div>
	</div>


</div>

<?php get_footer(); ?>