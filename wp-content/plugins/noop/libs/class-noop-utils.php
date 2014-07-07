<?php
if( !defined( 'ABSPATH' ) )
	die( 'Cheatin\' uh?' );

/*-----------------------------------------------------------------------------------*/
/* !Noop UTILS CLASS =============================================================== */
/* Provides static methods for sanitization											 */
/*-----------------------------------------------------------------------------------*/

if ( !class_exists('Noop_Utils') ) :
class Noop_Utils {

	const VERSION = '0.6.4';

	/*-------------------------------------------------------------------------------*/
	/* !Utilities ================================================================== */
	/*-------------------------------------------------------------------------------*/

	// !Like ltrim, but with words

	static public function ltrim_word( $source, $remove = '', $limit = -1 ) {
		if ( $remove === '' || !is_string($remove) )
			return $source;
		return $limit && strpos($source, $remove) === 0 ? self::ltrim_word( substr($source, strlen($remove)), $remove, --$limit ) : $source;
	}


	// !Like rtrim, but with words

	static public function rtrim_word( $source, $remove = '', $limit = -1 ) {
		if ( $remove === '' || !is_string($remove) )
			return $source;
		return $limit && strrpos($source, $remove) === (strlen($source) - strlen($remove)) ? self::rtrim_word( substr($source, 0, -strlen($remove)), $remove, --$limit ) : $source;
	}


	// !Like trim, but with words

	static public function trim_word( $source, $remove = '', $limit = -1 ) {
		if ( $remove === '' || !is_string($remove) )
			return $source;
		return self::ltrim_word( self::rtrim_word( $source, $remove, $limit ), $remove, $limit );
	}


	// !str_replace() only the first.
	static public function str_replace_once( $search, $replace, $subject ) {
		if ( false !== ( $pos = strpos( $subject, $search ) ) )
			return substr_replace( $subject, $replace, $pos, strlen( $search ) );
		return $subject;
	}


	/*-------------------------------------------------------------------------------*/
	/* !Work with dates ============================================================ */
	/*-------------------------------------------------------------------------------*/

	// !'0000-00-00 00:00:00' to timestamp

	static public function mysql2timestamp( $date ) {
		if ( $date && $date != '0000-00-00 00:00:00' )
			return mktime( (int)substr($date, 11, 2), (int)substr($date, 14, 2), (int)substr($date, 17, 2), (int)substr($date, 5, 2), (int)substr($date, 8, 2), (int)substr($date, 0, 4) );
		return mktime(0,0,0,0,0,0);
	}


	// !timestamp GMT to timestamp local

	static public function timestamp_gmt_to_local( $timestamp = 0 ) {
		return ( $timestamp + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) );
	}


	// !The function wp_checkdate() for WP < 3.5

	static public function checkdate( $month, $day, $year, $source_date ) {
		if ( function_exists('wp_checkdate') )	// WP 3.5+
			return wp_checkdate( $month, $day, $year, $source_date );
		return apply_filters( 'wp_checkdate', checkdate( $month, $day, $year ), $source_date );
	}


	// !A date array to mysql string with some checks

	static public function implode_date( $date_arr ) {
		$date_arr = array_merge( array(
			'aa'	=> 0,
			'mm'	=> 0,
			'jj'	=> 0,
			'hh'	=> 0,
			'mn'	=> 0,
			'ss'	=> 0,
		), $date_arr);
		$date_arr	= array_map('intval', $date_arr);
		extract($date_arr);

		$aa = ($aa <  0 ) ?  0 : $aa;
		$mm = self::min_max($mm, 0, 12);
		$jj = self::min_max($jj, 0, 31);
		$hh = self::min_max($hh, 0, 23);
		$mn = self::min_max($mn, 0, 59);
		$ss = self::min_max($ss, 0, 59);
		$date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss );
	/*	$valid_date = wp_checkdate( $mm, $jj, $aa, $date );
		if ( !$valid_date )
			return new WP_Error( 'invalid_date', __( 'Whoops, the provided date is invalid.' ) );
		return get_gmt_from_date( $date );*/
		return $date;
	}


	// !A date in mysql string format to an array

	static public function explode_date( $date ) {
		$date = $date && $date != '0000-00-00 00:00:00' ? $date : '0000-00-00 00:00:00';

		$date_arr				= array( 'jj' => '00', 'mm' => '00', 'aa' => '0000', 'hh' => '00', 'mn' => '00', 'ss' => '00' );
		$date					= explode(' ', $date);
		$date[0]				= explode('-', $date[0]);
		if ( (bool) $date[0][0] )
			$date_arr['aa']		= $date[0][0];
		if ( isset($date[0][1]) )
			$date_arr['mm']		= $date[0][1];
		if ( isset($date[0][2]) )
			$date_arr['jj']		= $date[0][2];
		if ( isset($date[1]) ) {
			$date[1]			= explode(':', $date[1]);
			if ( (bool) $date[1][0] )
				$date_arr['hh']	= $date[1][0];
			if ( isset($date[1][1]) )
				$date_arr['mn']	= $date[1][1];
			if ( isset($date[1][2]) )
				$date_arr['ss']	= $date[1][2];
		}
		return $date_arr;
	}


	// !A date array to a "yyyy-mm" string with some checks

	static public function implode_month_year( $date_arr ) {
		$date_arr = array_merge( array(
			'aa'	=> 0,
			'mm'	=> 0,
		), $date_arr);
		$date_arr	= array_map('intval', $date_arr);
		extract($date_arr);

		$aa = ($aa <  0 ) ? 0 : $aa;
		$mm = self::min_max($mm, 0, 12);
		$date = sprintf( "%04d-%02d", $aa, $mm );
		return $date;
	}


	// !A date in "yyyy-mm" string format to an array

	static public function explode_month_year( $date ) {
		$date = $date && $date != '0000-00' ? $date : '0000-00';

		$date_arr['mm'] = mysql2date( 'm', $date, false );
		$date_arr['aa'] = mysql2date( 'Y', $date, false );

		return $date_arr;
	}


	// !A date array to a "hh:mn" string with some checks

	static public function implode_hour_minute( $date_arr ) {
		$date_arr = array_merge( array(
			'hh'	=> 0,
			'mn'	=> 0,
		), $date_arr);
		extract($date_arr);

		$hh = self::min_max($hh, 0, 23);
		$mn = self::min_max($mn, 0, 59);
		$date = sprintf( "%02d:%02d", $hh, $mn );
		return $date;
	}


	// !A date in "hh:mn" string format to an array

	static public function explode_hour_minute( $date ) {
		$date = $date && $date != '00:00' ? $date : '00:00';

		$date_arr['hh'] = mysql2date( 'H', $date, false );
		$date_arr['mn'] = mysql2date( 'i', $date, false );

		return $date_arr;
	}


	/*-------------------------------------------------------------------------------*/
	/* !Basic sanitization ========================================================= */
	/*-------------------------------------------------------------------------------*/

	// !absint but doesn't return zero

	static public function absint_no_zero( $int = 0 ) {
		$int = absint($int);
		return !$int ? '' : $int;
	}


	// !intval but doesn't return zero

	static public function intval_no_zero( $int = 0 ) {
		$int = intval($int);
		return !$int ? '' : $int;
	}


	// !floatval but doesn't return zero

	static public function floatval_no_zero( $int = 0 ) {
		$int = str_replace(',', '.', ''.$int);
		$int = floatval($int);
		return $int ? $int : '';
	}


	/*
	 * !Pass an integer through min and max
	 * How to use in $noop_options->sanitization_functions():
	 * $functions = array(
	 *     'my_param' => array( 'function'  => array( 'Noop_Utils', 'min_max' ), 'params' => array(3, 8) )	// 3 and 8 as $min and $max. Use "params"! Not "param"!
	 * );
	 */
	static public function min_max( $int = 1, $min = 1, $max = 10 ) {
		return max($min, min($max, intval($int)));
	}


	/*-------------------------------------------------------------------------------*/
	/* !More exotic sanitization =================================================== */
	/*-------------------------------------------------------------------------------*/

	/*
	 * !Like esc_js() + unslash output
	 */
	static public function esc_js( $js = '' ) {
		$js = esc_js( $js );
		return function_exists('wp_unslash') ? wp_unslash( $js ) : stripslashes_deep( $js );
	}

	/*
	 * !Sanitize a slug containing a path
	 * "\boo bar/foo/bar/ " => "foo-bar/foo/bar"
	 */
	static public function sanitize_slug( $slug = '' ) {
		$slug = str_replace( DIRECTORY_SEPARATOR, '/', $slug );
		$slug = array_filter( explode('/', trim($slug, ' /')) );
		$slug = array_map('sanitize_title', $slug);
		return implode('/', $slug);
	}


	// !Parse a list of IDs (string, IDs separated with commas) or take directly an array, and return this list as a string

	static public function parse_id_list( $ids = '', $unique = true, $sep = ',' ) {
		$ids = is_array($ids) ? $ids : preg_split('/[\s'.$sep.']+/', $ids);
		$ids = array_map('absint', $ids);
		$ids = $unique ? array_unique($ids) : $ids;
		return implode($sep, array_filter($ids));
	}


	// !Parse a list of slugs (string, slugs separated with commas) or take directly an array, and return this list as a string

	static public function parse_slug_list( $slugs = '', $unique = true, $sep = ',' ) {
		$slugs = is_array($slugs) ? $slugs : preg_split('/[\s'.$sep.']+/', $slugs);
		$slugs = array_map('sanitize_title', $slugs);
		$slugs = $unique ? array_unique($slugs) : $slugs;
		return implode($sep, array_filter($slugs));
	}


	// !Sanitize an hexadecimal color

	static public function sanitize_hex( $hex ) {
		$hex = trim( $hex, ' #' );
		$hex = strpos( $hex, '%23' ) === 0 ? substr( $hex, 3 ) : $hex;

		if ( strlen( $hex ) == 3 )
			$hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);

		if ( preg_match( '/^[0-9a-fA-F]{6}$/', $hex ) )
			return '#'.strtolower( esc_attr( $hex ) );
		return '';
	}


	// !Sanitize a date

	static public function sanitize_date( $date ) {						// array/string
		if ( is_array( $date ) )
			$date = self::implode_date( $date );

		return $date == '0000-00-00 00:00:00' ? '' : esc_attr( $date );	// string
	}


	// !Sanitize a "month-year" date

	static public function sanitize_month_year( $date ) {	// array/string
		if ( is_array( $date ) )
			$date = self::implode_month_year( $date );

		return $date == '0000-00' ? '' : esc_attr( $date );	// string
	}


	// !Sanitize a "hour-minute" date

	static public function sanitize_hour_minute( $date ) {	// array/string
		if ( is_array( $date ) )
			$date = self::implode_hour_minute( $date );

		return $date == '00:00' ? '' : esc_attr( $date );	// string
	}


	// !Sanitize a Twitter id

	static public function sanitize_twitter_id( $twittos ) {
		return esc_attr(ltrim($twittos, ' @'));
	}

}
endif;
/**/