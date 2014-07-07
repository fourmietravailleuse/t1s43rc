<?php
/*
Template Name Posts: Contact
*/
?>

<?php

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

          </div>

          <div class="entry-content">

            <?php if (get_field('plan_interactif_contact') == 'gps') {

                $location = get_field('coordonnees_gps_adresse');

                if(empty($location['address'])) {
                    
                    $content = get_option('creasit_dev_informations');
                    $address = $content['adresse_postale'];

                    $coordinates = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=true');
                    $coordinates = json_decode($coordinates);
                    $coordonnee = $coordinates->results[0]->geometry->location->lat.','.$coordinates->results[0]->geometry->location->lng;

                } else {
                   
                    $coordonnee = $location['lat'].','.$location['lng'];

                } ?>

                <h2>Plan d'accès</h2>

                <div id="google-map" style="height:300px;width:100%;"></div>

                <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>

                <script type="text/javascript">


                  // SCRIPT GOOGLE MAP

                  var newStyle = [{
                    "featureType": "poi.park",
                    "elementType": "geometry.fill",
                    "stylers": [{
                      "color": "#dddddd"
                    }]
                  }];     
                  
                  // Coordonnée du point
                  var myLatlng = new google.maps.LatLng(<?php echo $coordonnee; ?>);
                   
                  // Carte centrée sur l'adresse
                  var myMapOptions = {
                    zoom: 15,
                    center: myLatlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    styles : newStyle
                  };
                   
                  // Création de la carte
                  var myMap = new google.maps.Map(
                    document.getElementById('google-map'),
                    myMapOptions
                    );
                   
                  // Création de l'icône
                  var myMarkerImage = new google.maps.MarkerImage("<?php echo get_stylesheet_directory_uri().'/images/marker-gm.png'; ?>");
                   
                  // Création du marker
                  var myMarker = new google.maps.Marker({
                    position: myLatlng, 
                    map: myMap,
                    icon: myMarkerImage
                  });

                </script>

            <?php } else if (get_field('plan_interactif_contact') == 'iframe') { ?>

                <?php if(get_field('plan_interactif_contact')){

                    $content = get_option('creasit_dev_informations');
                    $address = $content['adresse_postale'];

                    $coordinates = file_get_contents('http://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($address) . '&sensor=true');
                    $coordinates = json_decode($coordinates);
                    $coordonnee = $coordinates->results[0]->geometry->location->lat.','.$coordinates->results[0]->geometry->location->lng;

                ?>  

                    <h2>Plan d'accès</h2>

                    <div id="google-map" style="height:300px;width:100%;"></div>

                    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>

                    <script type="text/javascript">


                      // SCRIPT GOOGLE MAP

                      var newStyle = [{
                        "featureType": "poi.park",
                        "elementType": "geometry.fill",
                        "stylers": [{
                          "color": "#dddddd"
                        }]
                      }];     
                      
                      // Coordonnée du point
                      var myLatlng = new google.maps.LatLng(<?php echo $coordonnee; ?>);
                       
                      // Carte centrée sur l'adresse
                      var myMapOptions = {
                        zoom: 15,
                        center: myLatlng,
                        mapTypeId: google.maps.MapTypeId.ROADMAP,
                        styles : newStyle
                      };
                       
                      // Création de la carte
                      var myMap = new google.maps.Map(
                        document.getElementById('google-map'),
                        myMapOptions
                        );
                       
                      // Création de l'icône
                      var myMarkerImage = new google.maps.MarkerImage("<?php echo get_stylesheet_directory_uri().'/images/marker-gm.png'; ?>");
                       
                      // Création du marker
                      var myMarker = new google.maps.Marker({
                        position: myLatlng, 
                        map: myMap,
                        icon: myMarkerImage
                      });

                    </script>



                <?php } else { ?>

                    <h2>Plan d'accès</h2>

                    <p><?php the_field('iframe_google_map'); ?></p>

                <?php } ?>

            <?php } else {} ?>

          </div>


        </article>
    </div>

  </div>
<?php endwhile; endif; ?>

<?php get_footer(); ?>

