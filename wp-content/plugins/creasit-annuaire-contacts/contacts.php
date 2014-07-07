<?php
/*
Plugin Name: Creasit Annuaire de contacts
Description: Gestion de l'annuaire de contacts
Author: Creasit
Version: 1.0
*/

// Appel du css
add_action( 'wp_enqueue_scripts', 'add_css_annuaire_contacts' );
function add_css_annuaire_contacts() {
	wp_enqueue_style('annuaire-contaxts', plugins_url().'/creasit-annuaire-contacts/css/style-annuaire-contacts.css', array(), '1.0', 'screen, projection');
}


// Création du cunstom post type
add_action( 'init', 'create_post_type_contacts' );

function create_post_type_contacts() {
	$labels = array(
		'name' => __( 'Contacts', 'solution' ),
		'singular_name' => __( 'Contacts', 'solution' ),
		'search_items'      => __('Rechercher des contact', 'solution'),
		'all_items'         => __('Tous les contacts', 'solution'),
		'edit_item'         => __('Editer un contact', 'solution'),
		'update_item'       => __('Mise à jour d\'un contact', 'solution'),
		'add_new_item'      => __('Ajouter un nouveau contact', 'solution'),
		'new_item_name'     => __('Nouveau titre d\'un contact', 'solution'),
		'menu_name'         => __('Contacts', 'solution'),
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_ui' => true,
		'capability_type' => 'post',
		'hierarchical' => true,
		'has_archive' => false,
		'supports' => array( 'title'),
		'menu_icon' => 'dashicons-id',
	);

  register_post_type( 'contacts', $args );


  	// Ajout de la taxo "type"
  	$labels = array(
		'name'              => __('Type', 'solution'),
		'singular_name'     => __('Type', 'solution'),
		'search_items'      => __('Rechercher des types', 'solution'),
		'all_items'         => __('Tous les types', 'solution'),
		'edit_item'         => __('Editer un type', 'solution'),
		'update_item'       => __('Mise à jour d\'un type', 'solution'),
		'add_new_item'      => __('Ajouter un nouveau type', 'solution'),
		'new_item_name'     => __('Nouveau titre d\'un type', 'solution'),
		'menu_name'         => __('Type', 'solution'),
 	 );

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'query_var'         => true,
	);

 	register_taxonomy( 'type', array( 'contacts' ), $args );

 	// Insérer des types 
	$parent_term = term_exists( 'type', 'type' ); // array is returned if taxonomy is given
	$parent_term_id = $parent_term['term_id']; // get numeric term id

	wp_insert_term( //this should probably be an array, but I kept getting errors..
	        'Service Public', // the term 
	        'type', // the taxonomy
	        array(
	            'slug' => 'service_public',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Association', 
	        'type', 
	        array(
	            'slug' => 'association',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Professionnel', 
	        'type', 
	        array(
	            'slug' => 'professionnel',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Etablissement scolaire', 
	        'type', 
	        array(
	            'slug' => 'etablissement_scolaire',
	            'parent'=> $parent_term_id ));





 	// Ajout de la taxo "catégories"
  	$labels = array(
		'name'              => __('Catégories', 'solution'),
		'singular_name'     => __('Catégories', 'solution'),
		'search_items'      => __('Rechercher des catégories', 'solution'),
		'all_items'         => __('Tous les catégories', 'solution'),
		'edit_item'         => __('Editer une catégorie', 'solution'),
		'update_item'       => __('Mise à jour d\'une catégorie', 'solution'),
		'add_new_item'      => __('Ajouter une nouvelle catégorie', 'solution'),
		'new_item_name'     => __('Nouveau titre d\'une catégorie', 'solution'),
		'menu_name'         => __('Catégorie(s)', 'solution'),
 	 );

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'query_var'         => true,
	);

 	register_taxonomy( 'categories', array( 'contacts' ), $args );

 	// Insérer des types 
	$parent_term = term_exists( 'categories', 'categories' ); // array is returned if taxonomy is given
	$parent_term_id = $parent_term['term_id']; // get numeric term id

	wp_insert_term( //this should probably be an array, but I kept getting errors..
	        'Administration', // the term 
	        'categories', // the taxonomy
	        array(
	            'slug' => 'administration',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Agriculture, élevage', 
	        'categories', 
	        array(
	            'slug' => 'agriculture',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Aînés', 
	        'categories', 
	        array(
	            'slug' => 'aines',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Autre prestation', 
	        'categories', 
	        array(
	            'slug' => 'autre_prestation',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Café', 
	        'categories', 
	        array(
	            'slug' => 'cafe',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Construction et habitat', 
	        'categories', 
	        array(
	            'slug' => 'construction_habitat',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Culture', 
	        'categories', 
	        array(
	            'slug' => 'culture',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Divers', 
	        'categories', 
	        array(
	            'slug' => 'Divers',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Emploi', 
	        'categories', 
	        array(
	            'slug' => 'emploi',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Enfance et jeunesse', 
	        'categories', 
	        array(
	            'slug' => 'enfance_jeunesse',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Environnement', 
	        'categories', 
	        array(
	            'slug' => 'environnement',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Hébergement', 
	        'categories', 
	        array(
	            'slug' => 'hebergement',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Industrie', 
	        'categories', 
	        array(
	            'slug' => 'industrie',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Loisirs', 
	        'categories', 
	        array(
	            'slug' => 'loisirs',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Permanence', 
	        'categories', 
	        array(
	            'slug' => 'permanence',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'PMU', 
	        'categories', 
	        array(
	            'slug' => 'pmu',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Restauration', 
	        'categories', 
	        array(
	            'slug' => 'restauration',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Santé', 
	        'categories', 
	        array(
	            'slug' => 'sante',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Scolaire', 
	        'categories', 
	        array(
	            'slug' => 'scolaire',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Service aux entreprises', 
	        'categories', 
	        array(
	            'slug' => 'service_entreprise',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Service de proximité', 
	        'categories', 
	        array(
	            'slug' => 'service_proximite',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Service municipal administratif', 
	        'categories', 
	        array(
	            'slug' => 'service_municipal_administratif',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Service municipal scolaire', 
	        'categories', 
	        array(
	            'slug' => 'ervic_municipal_scolaire',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Service municipal technique', 
	        'categories', 
	        array(
	            'slug' => 'service_municipal_technique',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Social', 
	        'categories', 
	        array(
	            'slug' => 'social',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Sport', 
	        'categories', 
	        array(
	            'slug' => 'sport',
	            'parent'=> $parent_term_id ));

	wp_insert_term( 
	        'Transport et véhicule', 
	        'categories', 
	        array(
	            'slug' => 'transport_vehicule',
	            'parent'=> $parent_term_id ));


}




function contacts_register_meta_boxes()
{
	if ( !class_exists( 'RW_Meta_Box' ) )
	return;

	$meta_boxes = array();

	$meta_boxes[] = array(
		'id' => 'infos_contact',
		'title' => __( 'Informations', 'rwmb' ),
		'pages' => array( 'contacts' ),
		'context' => 'normal',
		'fields' => array(
			array(
				'name'             => __( 'Image', 'rwmb' ),
				'id'               => 'img_contact',
				'type'             => 'image_advanced',
				'max_file_uploads' => 1,
			),
			array(
				'id'   => 'principal_contact',
				'name' => __( 'Contact principal', 'rwmb' ),
				'type' => 'text',
			),
			array(
				'id'   => 'adresse_contact',
				'name' => __( 'Adresse', 'rwmb' ),
				'type' => 'textarea',
			),
			array(
				'id'   => 'code_postal_contact',
				'name' => __( 'Code postal', 'rwmb' ),
				'type' => 'text',
			),
			array(
				'id'   => 'commune_contact',
				'name' => __( 'Commune', 'rwmb' ),
				'type' => 'text',
			),
			array(
				'id'   => 'telephone_contact',
				'name' => __( 'Téléphone', 'rwmb' ),
				'type' => 'text',
			),
			array(
				'id'   => 'portable_contact',
				'name' => __( 'Portable', 'rwmb' ),
				'type' => 'text',
			),
			array(
				'id'   => 'telecopie_contact',
				'name' => __( 'Télécopie', 'rwmb' ),
				'type' => 'text',
			),
			array(
				'id'    => 'email_contact',
				'name'  => __( 'Courriel', 'rwmb' ),
				'type'  => 'email',
			),
			array(
				'id'   => 'anonymat_contact',
				'name' => __( 'Anonymat', 'rwmb' ),
				'type' => 'checkbox',
				'desc'  => __( 'Adresse de courrier électronique cachée (un formulaire de contact sera utilisé)', 'rwmb' ),
				'std'  => 0,
			),
			array(
				'id'   => 'site_contact',
				'name' => __( 'Site internet', 'rwmb' ),
				'type' => 'text',
			),
			array(
				'id'   => 'autres_contact',
				'name' => __( 'Autres contacts', 'rwmb' ),
				'type' => 'textarea',
			),
			array(
				'id'   => 'horaires_contact',
				'name' => __( 'Horaires', 'rwmb' ),
				'type' => 'textarea',
			),
			array(
				'id'   => 'presentation_contact',
				'name' => __( 'Présentation', 'rwmb' ),
				'type' => 'textarea',
			),
		),

    );

  	foreach ( $meta_boxes as $meta_box )
  	{
		new RW_Meta_Box( $meta_box );
  	}
}
add_action( 'admin_init', 'contacts_register_meta_boxes' );




/**
Changer le champ "title" par défaut en "Nom"
*/

add_filter( 'enter_title_here', 'custom_enter_title' );

function custom_enter_title( $input ) {
    global $post_type;

    if ( is_admin() && 'contacts' == $post_type )
        return __( 'Nom du contact', 'solution' );

    return $input;
}
