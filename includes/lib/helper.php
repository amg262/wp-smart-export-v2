<?php

add_action( 'pre_user_query', '_wp_xprt_users_by_date_sql', 10 );

/**
 * Sets the headers required for exporting a file.
 */
function wp_xprt_get_export_headers( $filename ) {

    $now = gmdate("D, d M Y H:i:s");

	header("Pragma: no-cache");
	header("Expires: 0");
    header("Last-Modified: {$now} GMT");

	// force download
	header('Content-type: text/csv; charset=UTF-8');
	header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

	// disposition / encoding on response body
	header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

/**
 * Retrieves the selectable content types.
 *
 * @uses apply_filters() Calls 'wp_xprt_content_types'
 *
 */
function wp_xprt_get_content_types( $args = array() ) {

	$c_types = wp_xprt_get_post_types( $args );

	$c_types['empty_users'] = __( ' -- Userdata --', 'wp-smart-export' );
	$c_types['user']        = __( 'Users', 'wp-smart-export' );

	return apply_filters( 'wp_xprt_content_types', $c_types, $args );
}

/**
 * Retrieves the selectable post types.
 *
 * @uses apply_filters() Calls 'wp_xprt_post_types'
 *
 */
function wp_xprt_get_post_types( $args = array() ) {
	$p_types['empty_posts']      =  __( ' -- Post Type --', 'wp-smart-export' );

	// Posts.
	$post_type = get_post_type_object('post');
	$p_types[ $post_type->name ] = $post_type->labels->name . ' (post)';

	// Pages.
	$post_type = get_post_type_object('page');
	$p_types[ $post_type->name ] = $post_type->labels->name . ' (page)';

	return apply_filters( 'wp_xprt_post_types', $p_types, $args );
}

/**
 * Retrieves the selectable post statuses.
 *
 * @uses apply_filters() Calls 'wp_xprt_post_statuses'
 *
 */
function wp_xprt_get_post_statuses() {

	$statuses['publish'] = __( 'Publish', 'wp-smart-export' );

	return apply_filters( 'wp_xprt_query_post_statuses', $statuses );
}

/**
 * Retrieves the available user roles.
 *
 * @uses apply_filters() Calls 'wp_xprt_user_roles'
 *
 */
function wp_xprt_get_roles() {

	$roles['any'] = __( 'All Roles', 'wp-smart-export' );

	return apply_filters( 'wp_xprt_query_user_roles', $roles );
}

/**
 * Retrieves the core fields list (wp_post).
 */
function wp_xprt_get_core_fields( $content_type = 'post' ) {
	global $wpdb;

	if ( 'user' === $content_type ) {
		$table = 'users';
	} else {
		$table = 'posts';
	}

	$table_name = $wpdb->$table;

	$cols = array();

	foreach ( $wpdb->get_col( "DESC $table_name", 0 ) as $column_name ) {
	  $cols[] = $column_name;
	}

	return $cols;
}

/**
 * Retrieves the field content type if recongnized. Defaults to 'text' if unknown.
 */
function wp_xprt_get_field_type( $field, $content_type = 'post' ) {

	$type = 'text';

	$fields = wp_xprt_get_known_field_types();

	$fields = array_merge( $fields, wp_xprt_field_types( $content_type ) );

	if ( $field && isset( $fields[ $field ] ) ) {
		$type = $field;
	}
	return $type;
}

/**
 * Retrieve known field types for a know list of core fields.
 */
function wp_xprt_get_known_field_types() {

	$fields = array(
		'post_author' => array(
			'user' => __( 'User', 'wp-smart-export' ),
			'text' => __( 'Text', 'wp-smart-export' ),
		),
		'post_status' => array(
			'post_status' => __( 'Post Status', 'wp-smart-export' ),
			'text'        => __( 'Text', 'wp-smart-export' )
		),
		'post_type' => array(
			'post_type' => __( 'Post Type', 'wp-smart-export' ),
			'text'      => __( 'Text', 'wp-smart-export' )
		),
	);
	return apply_filters( 'wp_xprt_known_field_types', $fields );
}

/**
 * Retrieve all possible field types.
 *
 * @uses apply_filters() Calls 'wp_xprt_field_types'
 *
 */
function wp_xprt_field_types( $content_type = 'post' ) {

	$types = array(
		'text'         => __( 'Text', 'wp-smart-export' ),
		'date'         => __( 'Date', 'wp-smart-export' ),
		'user'         => __( 'User', 'wp-smart-export' ),
		'boolean'      => __( 'Yes/No', 'wp-smart-export' ),
		'unserialized' => __( 'Unserialized', 'wp-smart-export' ),
	);

	if ( 'user' !== $content_type ) {
		$types['post_status'] = __( 'Post Status', 'wp-smart-export' );
	}

	$post_type = get_post_type_object('post');
	$p_types[ $post_type->name ] = $post_type->labels->name . ' (post)';
	$p_types = apply_filters( 'wp_xprt_post_types', $p_types );

	$types = array_merge( $types, $p_types );

	// get existing taxonomies
	$taxonomies = get_object_taxonomies( $content_type, 'objects' );

	// unset the 'post_status' taxonomy since it's empty
	unset( $taxonomies['post_status'] );

	foreach( $taxonomies as $tax ) {
		$types[ $tax->name ] = sprintf( __( "Taxonomy :: %s", 'wp-smart-export' ), $tax->label );
	}

	return apply_filters( 'wp_xprt_field_types', $types, $content_type );
}

/**
 * Exports a given list of row data to a '*.csv' file.
 */
function wp_xprt_export( $rows, $export = array() ) {

	$defaults = array(
		'delimiter' => ',',
		'type'      => 'download',
	);
	$export = wp_parse_args( $export, $defaults );

	extract( $export );

	if ( ! $filename ) {
		$filename = 'wp-export-data-'.date('Ymd');
	}

	$filename .= '.csv';

	$rows_total = count( $rows );

	// Count/stored total exports.
	wp_xprt_update_export_stats( $rows_total );

	// Export and send file by email.
	if ( is_array( $type ) && ! empty( $type['email']['recipients'] ) ) {

		// If no schedule ID is set return immediately.
		if ( empty( $schedule ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$filename = $upload_dir['basedir'] . '/' . $filename;

		$schedule = get_post( $schedule );

		$message = $schedule->post_content;

		$message .= "\r\n\r\n" . sprintf( __( "Total: %s", 'wp-smart-export' ), $rows_total );

		wp_xprt_array2csv( $filename, $rows, $delimiter, false );

		wp_xprt_send_csv_by_mail( $type['email']['recipients'], $schedule->post_title, $message, array( $filename ) );

		// Delete the file after sending it.
		@unlink( $filename );

		// Count/store export through the scheduler.
		wp_xprt_update_export_stats( $rows_total, 'schedule' );

	} else {

		if ( empty( $rows ) ) {
			return;
		}

		// Export file to browser.
		wp_xprt_get_export_headers( $filename );
		echo wp_xprt_array2csv( $filename, $rows, $delimiter );
		die();
	}

}

/**
 * Sends a CSV file to a list of recipients.
 *
 * @uses apply_filters() Calls 'wp_xprt_send_csv_by_mail'
 *
 */
function wp_xprt_send_csv_by_mail( $to, $subject, $message, $attachments ) {

	$br = "\r\n";

	// Strip 'www.' from URL
	$domain = preg_replace( '#^www\.#', '', strtolower( $_SERVER['SERVER_NAME'] ) );

	$blogname = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

	$headers = array(
		'from'     => sprintf( 'From: %1$s <%2$s>', $blogname, "wordpress@$domain" ),
		'mime'     => 'MIME-Version: 1.0',
		'type'     => 'Content-Type: text/html; charset="' . get_bloginfo( 'charset' ) . '"',
		'reply_to' => "Reply-To: noreply@$domain",
	);

	$subject = sprintf( __( '[%1$s] Scheduled Export :: %2$s', 'wp-smart-export' ), get_bloginfo('name'), $subject );

	$message .= $br . $br;
	$message .= __( "Kind Regards, ", 'wp-smart-export' ) . $br;
	$message .= sprintf( __( 'The Team @ <a href="%1$s">%2$s</a>', 'wp-smart-export' ), home_url(), get_bloginfo( 'name' ) ) . $br . $br;

	$message = wpautop( $message );

	extract( apply_filters( 'wp_xprt_send_csv_by_mail', compact( 'to', 'subject', 'message', 'headers', 'attachments' ) ) );

	wp_mail( $to, $subject, $message, $headers, $attachments );
}

/**
 * Custom user query SQL to check for expired recruiter addons.
 */
function _wp_xprt_users_by_date_sql( $user_query ) {

	if ( $user_query->get( 'user_date_query' ) ) {

		$date_query = $user_query->get('user_date_query');

		if ( empty( $date_query['from_date'] ) ) {
			return $user_query;
		}

		$start_dt = new DateTime( $date_query['from_date'] . ' 00:00:00' );
		$start_dt = $start_dt->format('Y-m-d H:i:s');

		if ( empty( $date_query['to_date'] ) ) {
			$end_dt = $date_query['from_date'];
		} else {
			$end_dt = $date_query['to_date'];
		}

		$end_dt = new DateTime( $end_dt .' 23:59:59' );
		$end_dt = $end_dt->format('Y-m-d H:i:s');

		$user_query->query_where .= " AND CAST(user_registered AS DATE) BETWEEN '$start_dt' AND '$end_dt'";
	}

	return $user_query;
}

/**
 * Retrieve AppThemes order statuses labels.
 */
function wp_xprt_get_appthemes_order_status_verbiages( $status = '' ) {

	$statuses = array(
		'tr_pending'   => __( '(Order) Pending Payment', 'wp-smart-export' ),
		'tr_failed'    => __( '(Order) Failed/Canceled', 'wp-smart-export' ),
		'tr_completed' => __( '(Order) Paid', 'wp-smart-export' ),
		'tr_activated' => __( '(Order) Paid/Activated', 'wp-smart-export' ),
		'tr_refunded'  => __( '(Order) Refunded', 'wp-smart-export' ),
	);

	if ( $status && ! empty( $statuses[ $status ] ) ) {
		return $statuses[ $status ];
	} elseif( ! $status ) {
		return $statuses;
	} else {
		return false;
	}

}

/**
 * Updates the total number of exports or scheduled exports and retrieves it.
 *
 * @since 1.3.1
 */
function wp_xprt_update_export_stats( $sum, $part = 'count' ) {
	$stats  = (int) get_option( '_wp-smart-export-' . $part );

    $defaults = array(
        'exports' => 0,
        'count'   => 0,
    );
    $stats = wp_parse_args( $stats, $defaults );

    $stats['exports']++;
    $stats['count'] += (int) $sum;

	update_option( '_wp-smart-export-' . $part, $stats, false );

	return $stats['count'];
}
