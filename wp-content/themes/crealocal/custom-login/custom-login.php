<?php
/**
Custom login admin
*/
// Fonction qui insere le lien vers le css qui surchargera celui d'origine
function custom_login_css()  {
    echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('template_directory') . '/custom-login/styles-login.css" />';
}
add_action('login_head', 'custom_login_css');

// Filtre qui permet de changer l'url du logo
function custom_url_login()  {
    return get_bloginfo( '' ); // On retourne l'index du site
}
add_filter('login_headerurl', 'custom_url_login');

// Fonction qui permet d'ajouter du contenu juste au dessus de la balise 
function add_footer_login()  {
    echo '<p id="contact"><span class="haut">En cas de problème de connexion, veuillez contacter <b>Creasit</b></span><br>au numéro suivant : <b>02 40 37 01 77</b> ou par adresse mail : <b><a href="mailto:info@creasit.com">info@creasit.fr</a></b></p>'; 
} 
add_action('login_footer','add_footer_login');
