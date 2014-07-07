<?php

add_action('admin_menu', 'creasit_add_option_dev');

function creasit_add_option_dev() {
  add_theme_page( 'Option dev', 'Option dev', 'manage_options', 'creasit-theme-option', 'creasit_option_page_dev');
  //add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function);
}

//Le contenu de la page d'option
function creasit_option_page_dev () {
  ?>
  <div class="wrap">
    <?php screen_icon(); ?>
    <h2>Option du site</h2>

    <form action="options.php" method="post">
      <?php
      //Output Error
      settings_errors();

      //Output nonce, action, and creasit_option_page_dev
      settings_fields('creasit_option_group');

      //Prints out all settings sections added to a particular settings page
      do_settings_sections('creasit_dev_potions_page');

      submit_button();
      ?>
    </form>

  </div>
  <?php
}


//On défini nos options
add_action('admin_init', 'creasit_dev_admin_init');

function creasit_dev_admin_init() {

    //register_setting( $option_group, $option_name, $sanitize_callback );
    register_setting('creasit_option_group', 'creasit_dev_informations', 'creasit_theme_option_validate');

    //Register a settings field to a settings page and section.
    $content = get_option('creasit_dev_informations');

    $user = wp_get_current_user();
    $allowed_roles = array('administrator');

    if ( array_intersect($allowed_roles, $user->roles ) ) {

        //On créer une section dans nos options
        //add_settings_section( $id, $title, $callback, $page );
        add_settings_section('creasit_theme_section_un', 'Addthis', 'creasit_section_un_text', 'creasit_dev_potions_page');

        //On créer une section dans nos options
        //add_settings_section( $id, $title, $callback, $page );
        add_settings_section('creasit_theme_section_deux', 'Météo et Marée', 'creasit_section_deux_text', 'creasit_dev_potions_page');

        

        add_settings_field('creasit_theme_option_addthis', 'Profile ID', 'creasit_addthis_ID', 'creasit_dev_potions_page', 'creasit_theme_section_un', array('label_for' => 'profil_id'));


        // génération des options pour la météo / Marée
        add_settings_field('creasit_theme_option_meteo_ville', 'Code météo ville', 'creasit_meteo_ville', 'creasit_dev_potions_page', 'creasit_theme_section_deux', array('label_for' => 'meteo_ville'));

        add_settings_field('creasit_theme_option_maree_ville', 'Code marée', 'creasit_maree_ville', 'creasit_dev_potions_page', 'creasit_theme_section_deux', array('label_for' => 'maree_ville'));

        add_settings_field('creasit_theme_option_meteo_national_aujourdhui', 'URL météo national d\'aujourd\'hui', 'creasit_meteo_national_aujourdhui', 'creasit_dev_potions_page', 'creasit_theme_section_deux', array('label_for' => 'meteo_national_aujourdhui'));

        add_settings_field('creasit_theme_option_meteo_national_demain', 'URL météo national de demain', 'creasit_meteo_national_demain', 'creasit_dev_potions_page', 'creasit_theme_section_deux', array('label_for' => 'meteo_national_demain'));
    
    } 

        //On créer une section dans nos options
        add_settings_section('creasit_theme_section_trois', 'Adresse', 'creasit_section_trois_text', 'creasit_dev_potions_page');

        add_settings_field('creasit_theme_option_adresse_postale', 'Adresse postale', 'creasit_adresse_postale', 'creasit_dev_potions_page', 'creasit_theme_section_trois', array('label_for' => 'adresse_postale'));

        add_settings_field('creasit_theme_option_telephone', 'Numéro de téléphone', 'creasit_numero_telephone', 'creasit_dev_potions_page', 'creasit_theme_section_trois', array('label_for' => 'numero_telephone'));

        add_settings_field('creasit_theme_option_fax', 'Numéro de fax', 'creasit_numero_fax', 'creasit_dev_potions_page', 'creasit_theme_section_trois', array('label_for' => 'numero_fax'));

    

}

//Le text au dessus des options
function creasit_section_un_text() {}

function creasit_section_deux_text() {}

function creasit_section_trois_text() {}

function creasit_addthis_ID() {
  $content = get_option('creasit_dev_informations');
  echo '<input type="text" name="creasit_dev_informations[profil_id]" id="num" value="'.$content['profil_id'].'" />';
}

function creasit_meteo_ville(){
  $content = get_option('creasit_dev_informations');
  echo '<textarea name="creasit_dev_informations[meteo_ville]" id="meteo_ville_val" style="width:500px;height:200px;">'.$content['meteo_ville'].'</textarea>';
}

function creasit_maree_ville(){
  $content = get_option('creasit_dev_informations');
  echo '<textarea name="creasit_dev_informations[maree_ville]" id="meteo_ville_val" style="width:500px;height:200px;">'.$content['maree_ville'].'</textarea>';
}

function creasit_meteo_national_aujourdhui(){
  $content = get_option('creasit_dev_informations');
  echo '<input type="text" name="creasit_dev_informations[meteo_national_aujourdhui]" id="meteo_national_aujourdhui_val" value="'.$content['meteo_national_aujourdhui'].'" style="width:460px;"/ >';
}

function creasit_meteo_national_demain(){
  $content = get_option('creasit_dev_informations');
  echo '<input type="text" name="creasit_dev_informations[meteo_national_demain]" id="meteo_national_demain_val" value="'.$content['meteo_national_demain'].'" style="width:460px;"/ >';
}

function creasit_adresse_postale(){
  $content = get_option('creasit_dev_informations');
  echo '<input type="text" name="creasit_dev_informations[adresse_postale]" id="adresse_postale_val" value="'.$content['adresse_postale'].'" style="width:460px;"/ >';
}

function creasit_numero_telephone(){
  $content = get_option('creasit_dev_informations');
  echo '<input type="text" name="creasit_dev_informations[numero_telephone]" id="numero_telephone_val" value="'.$content['numero_telephone'].'" style="width:460px;"/ >';
}

function creasit_numero_fax(){
  $content = get_option('creasit_dev_informations');
  echo '<input type="text" name="creasit_dev_informations[numero_fax]" id="numero_fax_val" value="'.$content['numero_fax'].'" style="width:460px;"/ >';
}

function creasit_theme_option_validate( $checked ){
  return $checked;
}

