<?php
/**
 * Outputs and exports array contents to a file.
 */
function wp_xprt_array2csv( $filename, &$rows, $delimiter = ',', $attachment = true ) {

	if ( count( $rows ) == 0 ) {
	  return null;
	}

	ob_start();

	if ( $attachment ) {
		$handle = fopen( "php://output", 'w' );
	} else {
		$handle = fopen( $filename, 'w' );
	}

	fputcsv( $handle, array_keys( reset( $rows ) ), $delimiter );

	foreach ( $rows as $row ) {
		fputcsv( $handle, $row, $delimiter );
	}

	fclose( $handle );

	return ob_get_clean();
}

/**
 * Displays a formatted data based on WP date settings.
 */
function wp_xprt_display_date( $date_time, $format = 'datetime', $gmt_offset = false ) {
	if ( is_string( $date_time ) ) {
		$date_time = strtotime( $date_time );
	}

	if ( $gmt_offset ) {
		$date_time = $date_time + ( get_option( 'gmt_offset' ) * 3600 );
	}

	if ( $format == 'date' ) {
		$date_format = get_option( 'date_format' );
	} elseif ( $format == 'time' ) {
		$date_format = get_option( 'time_format' );
	} else {
		$date_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	}

	return date_i18n( $date_format, $date_time );
}

/**
 * Checks for a valid timestamp.
 */
function wp_xprt_is_valid_timestamp( $timestamp ) {
    return ( (string ) (int) $timestamp === $timestamp )
        && ($timestamp <= PHP_INT_MAX)
        && ($timestamp >= ~PHP_INT_MAX);
}

/**
 * Flattens an array by converting array values into single values.
 */
function wp_xprt_flatten_array( &$array ) {
	return array_walk( $array, create_function( '&$value', '$value = $value[0];' ) );
}

/**
 * Clears all the content for a given array.
 */
function wp_xprt_clear_array( &$array, $value = '' ) {
	return array_walk( $array, create_function( '&$value', '$value = "'.$value.'";' ) );
}

/**
 * Get excerpt from string.
 */
function wp_xprt_get_excerpt( $str, $startPos = 0, $maxLength = 100 ) {

	if ( strlen( $str ) > $maxLength ) {
		$excerpt   = substr( $str, $startPos, $maxLength-3 );
		$lastSpace = strrpos( $excerpt, ' ' );
		$excerpt   = substr( $excerpt, 0, $lastSpace );
		$excerpt  .= '...';
	} else {
		$excerpt = $str;
	}
	return $excerpt;
}
