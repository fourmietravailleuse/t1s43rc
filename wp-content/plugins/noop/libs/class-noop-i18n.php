<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop I18N CLASS ================================================================ */
/* i18n system, can be used alone but is needed (almost) everywhere.				 */
/* Used in: Noop_Options, Noop_Admin, Noop_Settings									 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_i18n') ) :
class Noop_i18n {

	const VERSION = '0.5';
	protected static $locale = array();
	protected static $default_locale;
	protected static $network_default_locale;
	protected static $available_languages;


	// !Get the default locale.
	// @param  $network_wide (bool) Used when the settings page is in the network admin.
	// @return (string) The default locale. "en_US" for example.

	static public function get_default_locale( $network_wide = false ) {
		if ( $network_wide && is_multisite() ) {
			if ( !self::$network_default_locale ) {
				$wplang = get_site_option('WPLANG', 'en_US');
				self::$network_default_locale = $wplang ? $wplang : 'en_US';
			}
			return apply_filters( 'noop_network_default_locale', self::$network_default_locale );
		}
		else {
			if ( !self::$default_locale ) {
				if ( is_multisite() )
					$wplang = get_option('WPLANG', 'en_US');
				else
					$wplang = defined('WPLANG') && WPLANG ? WPLANG : 'en_US';
				self::$default_locale = $wplang ? $wplang : 'en_US';
			}
			return apply_filters( 'noop_default_locale', self::$default_locale );
		}
	}


	// !Get the current locale.
	// If the filter "noop_use_locale_param" returns true, the result will be based on $_GET['loc'].
	// This filter should be used only on your settings page, where you switch between language settings.
	// @uses   self::get_languages()
	// @uses   self::get_default_locale()
	// @param  (string) $option built like this: {option_group}-{option_name}. This way, each instance of Noop can have its own locale, usefull for the settings pages.
	// @return (string) The current locale. "fr_FR" for example.

	static public function get_locale( $option = 'generic-none' ) {
		if ( !isset( self::$locale[ $option ] ) ) {
			if ( apply_filters( 'noop_use_locale_param', false, $option ) ) {
				$langs = self::get_languages();
				self::$locale[ $option ] = !empty($_GET['loc']) && !empty($langs) && in_array($_GET['loc'], $langs) ? esc_attr($_GET['loc']) : self::get_default_locale( is_network_admin() );
			} else {
				self::$locale[ $option ] = get_locale();
			}
		}
		return apply_filters( 'noop_locale', self::$locale[$option], $option );
	}


	// !Get the languages in use in the site.
	// @uses   self::get_available_languages()
	// @return (array) Languages in use in the site.

	static public function get_languages() {
		return apply_filters( 'noop_languages', self::get_available_languages() );
	}


	// !Get available languages (based on the files in the "/wp-content/language" folder of the site).
	// @return (array) Languages installed in the site.

	static public function get_available_languages() {
		if ( !self::$available_languages ) {
			self::$available_languages = get_available_languages();
			if ( !in_array('en_US', self::$available_languages) )
				self::$available_languages[] = 'en_US';
		}
		return self::$available_languages;
	}


	// !Tell if the site uses multiple languages.
	// Should be filtered only by the user.
	// @uses   self::get_languages()
	// @return (bool) Return true if the site uses multiple languages.

	static public function use_multilang() {
		return ( count( self::get_languages() ) > 1 ) && apply_filters( 'noop_use_multilang', true );
	}

}
endif;


/*-----------------------------------------------------------------------------------*/
/* !Link WPML/POLYLANG and Noop in the administration ============================== */
/*-----------------------------------------------------------------------------------*/

// !Filter Noop_i18n::get_default_locale()

if ( !function_exists('noop_third_parties_default_locale') ):
add_filter( 'noop_network_default_locale', 'noop_third_parties_default_locale', 0 );
add_filter( 'noop_default_locale', 'noop_third_parties_default_locale', 0 );

function noop_third_parties_default_locale( $def_locale ) {
	global $sitepress;
	if ( (empty($sitepress) && ! function_exists('pll_default_language')) || ! is_admin() ) {
		return $def_locale;
	}

	static $thirdp_locale = null;
	if ( is_null($thirdp_locale) ) {
		// Polylang
		if ( function_exists('pll_default_language') ) {
			$def_language = pll_default_language( 'locale' );
		}
		// WPML
		else {
			$def_language = $sitepress->get_default_language();
			$def_language = $sitepress->get_locale( $def_language );
		}
		$thirdp_locale = $def_language ? $def_language : $def_locale;
	}
	return $thirdp_locale;
}
endif;


// !Filter Noop_i18n::get_locale()

if ( !function_exists('noop_third_parties_locale') ):
add_filter( 'noop_locale', 'noop_third_parties_locale', 0, 2 );

function noop_third_parties_locale( $_locale, $option ) {
	global $sitepress;
	if ( (empty($sitepress) && ! function_exists('pll_current_language')) || !is_admin() ) {
		return $_locale;
	}

	if ( apply_filters( 'noop_use_locale_param', false, $option ) ) {
		static $thirdp_locale = array();
		if ( empty( $thirdp_locale[ $option ] ) ) {
			// Polylang
			if ( function_exists('pll_current_language') ) {
				$thirdp_locale[ $option ] = pll_current_language('locale');
			}
			// WPML
			else {
				$thirdp_locale[ $option ] = $sitepress->get_locale( $sitepress->get_current_language() );
			}

			if ( empty( $thirdp_locale[ $option ] ) ) {
				$thirdp_locale[ $option ] = $_locale;
			}
		}
		return $thirdp_locale[ $option ];
	}

	return $_locale;
}
endif;


// !Filter Noop_i18n::get_languages()

if ( !function_exists('noop_third_parties_languages') ):
add_filter( 'noop_languages', 'noop_third_parties_languages', 0 );

function noop_third_parties_languages( $languages ) {
	global $sitepress, $polylang;
	if ( (empty($sitepress) && empty($polylang)) || !is_admin() ) {
		return $languages;
	}

	static $thirdp_languages = null;
	if ( is_null($thirdp_languages) ) {
		// WPML
		if ( ! empty( $sitepress ) ) {
			$active_languages	= array_keys($sitepress->get_active_languages());
			if ( empty( $active_languages ) || ! is_array( $active_languages ) ) {
				$thirdp_languages	= $languages;
			}
			else {
				$thirdp_languages	= array();
				$all_languages		= $sitepress->get_languages();
				$all_codes			= wp_list_pluck($all_languages, 'code');

				foreach ( $active_languages as $code ) {
					if ( ($code_index = array_search($code, $all_codes)) !== false && isset($all_languages[$code_index]) ) {
						$thirdp_languages[$code] = $all_languages[$code_index]['default_locale'];
					}
				}

				if ( empty( $thirdp_languages ) ) {
					$thirdp_languages	= $languages;
				}
			}
		}
		// Polylang
		else {
			$active_languages = $polylang->model->get_languages_list();
			if ( empty( $active_languages ) || ! is_array( $active_languages ) ) {
				$thirdp_languages	= $languages;
			}
			else {
				$thirdp_languages	= array();
				foreach ( $active_languages as $object ) {
					$thirdp_languages[ $object->slug ] = $object->locale;
				}
			}
		}
	}
	return $thirdp_languages;
}
endif;
/**/