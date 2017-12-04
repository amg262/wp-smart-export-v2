<?php

// @todo Allow export by post author
// @todo warn user after deleting a template if the template is being used on a schedule
// @todo Add nonces to ajax calls

add_action( 'wp_ajax_wp_xprt_get_content_type_fields', '_wp_xprt_get_content_type_fields' );
add_action( 'wp_ajax_wp_xprt_load_template_content', '_wp_xprt_load_template_content' );
add_action( 'wp_ajax_wp_xprt_update_templates_list', '_wp_xprt_update_templates_list' );


/**
 * Extend Tables class to generate a custom fields table.
 */
class WP_XPRT_Fields_Table extends WP_XPRT_Table {

	public function __construct( $fields, $content_type ){
		$this->fields = $fields;
		$this->content_type = $content_type;
	}

	public function show() {
		echo $this->table( $this->fields, array( 'class' => 'wp_xprt_table widefat fields' ) );
	}

	public function header( $data ) {

		$cols = 3;

		for( $i = 0; $i <= 3; $i++ ) {
			$atts[ $i ] = array(
				'class' => 'index',
			);
		}

		$cells = $this->cells( array(
			html( 'input', array( 'type' => 'checkbox', 'name' => 'bulk_select', 'class' => 'bulk_select', 'value' => 'all' ) ) . ' ' . __( 'Fields', 'wp-smart-export' ),
			__( 'Header Label', 'wp-smart-export' ),
			__( 'Sample Content', 'wp-smart-export' ),
			__( 'Export Field As', 'wp-smart-export' ),
		), $atts, 'th' );

		return html( 'tr', array(), $cells );
	}

	public function footer( $data ) {
		return $this->header( $data );
	}

	protected function row( $item = array(), $atts = array() ) {

		if ( empty( $item['data'] ) ) {
			$item['data_display'] = '-';
		} elseif ( is_serialized( $item['data'] ) ) {
			$item['data_display'] = __( 'SERIALIZED DATA', 'wp-smart-export' ) . '<br/><br/>' . $item['data'];
		} elseif ( false !== stripos( $item['name'], '_oembed_' ) ) {
			$item['data_display'] = __( 'OEMBED_DATA', 'wp-smart-export' );
		}

		if ( empty( $item['data_display'] ) ) {
			$item['data_display'] = $item['data'];
		}

		$field_atts = array(
			'type'    => 'checkbox',
			'name'    => 'field[]',
			'class'   => 'field',
			'value'   => $item['name'],
			'checked' => $item['checked']
		);

		if ( 'user_pass' === $item['name'] || 'post_password' === $item['name'] ) {
			$field_atts['disabled'] = 'disabled';
		}

		$cells = $this->cells( array(
			html( 'input', $field_atts ) . ' ' . html( 'span', array( 'class' => 'field' ), $item['name'] ) .
			html( 'input', array( 'type' => 'hidden', 'name' => 'type['.$item['name'].']', 'value' => $item['type'] ) ),
			html( 'input', array( 'type' => 'text', 'name' => 'label['.$item['name'].']', 'value' => $item['label'] ) ),
			html( 'span', array( 'class' => 'sample_content' ), $item['data_display'] ),
			$this->output_field_types( $item, $this->content_type ),
		) );

		if ( '_' === $item['name'][0] ) {
			$atts['class'] .= ' hidden_field';
		}
		$atts['class'] .= ' ' . $item['type'];

		return html( 'tr', $atts, $cells );
	}

	### Helper Methods

	/**
	 * Outputs the fields content types on the table 'Type' column.
	 */
	protected function output_field_types( $field_data, $content_type = 'post' ) {

		$field = $field_data['name'];
		$sel_type = $field_data['content_data_type'];

		$type = wp_xprt_get_field_type( $field, $content_type );

		$all_field_types   = wp_xprt_field_types( $content_type );
		$known_field_types = wp_xprt_get_known_field_types();

		$hidden = '';

		$is_static_field_type = in_array( $field, array_diff( wp_xprt_get_core_fields( $content_type ), array_keys( $known_field_types ) ) );

		$is_static_field_type = $is_static_field_type || false !== stripos( $field, '_oembed_' );

		$is_static_field_type = $is_static_field_type || ( wp_xprt_is_reserved_field( $field ) && ! is_serialized( $field_data['data'] ) );

		// Use a static label instead of dropdown for known field types.
		if ( apply_filters( 'wp_xprt_hidden_field_types', $is_static_field_type, $field_data ) ) {
			$hidden = "display: none";
		}

		// Check for known fields content type in order to limit the selectable values.
		if ( isset( $all_field_types[ $field ] ) ) {
			$valid_types = array(
				'text' => __( 'Text', 'wp-smart-export' ),
				$field => $field
			);
			$types = array_intersect_key( $all_field_types, $valid_types );
		} elseif ( isset( $known_field_types[ $field ] ) ) {
			$types = $known_field_types[ $field ];

			unset( $types['post_status'] );
		} elseif ( isset( $field_data['data'] ) && is_serialized( $field_data['data'] ) ) {
			$types = array(
				'text'         => __( 'Text', 'wp-smart-export' ),
				'unserialized' => __( 'Unserialized', 'wp-smart-export' ),
			);
			$sel_type = 'unserialized';
		} else {
			$types = $all_field_types;
		}

		$options_html = $text = '';

		if ( $hidden ) {
			$text = html( 'span', $types[ $type ] );
			$types = array( $type => $types[ $type ] );
		}

		foreach( $types as $key => $label ) {

			$atts = array( 'value' => $key );

			if ( ( ! $sel_type && $type === $key ) || $sel_type == $key || ( empty( $_POST['template'] ) && $key === $field ) ) {
				$atts['selected'] = 'selected';
			}

			$options_html .= html( 'option', $atts, $label );
		}

		return $text . html( 'select', array( 'name' => "content_data_type[$field]", "style" => $hidden ), $options_html );
	}

	/**
	 * Query the database considering the content type.
	 */
	static function query_content_type( $query_args = array() ) {

		if ( 'user' == $query_args['content_type'] ) {

			$defaults = array(
				'no_paging'     => true,
                'wp_xprt_users' => true,
			);
			$args = wp_parse_args( $query_args, $defaults );

			if ( 'any' === $args['role'] ) {
				unset( $args['role'] );
			}

			$users = new WP_User_Query( $args );

			return $users->results;

		} else {

			$defaults = array(
				'post_type'   => $query_args['content_type'],
				'post_status' => 'publish',
				'nopaging'    => true,
			);
			$args = wp_parse_args( $query_args, $defaults );

			$posts = new WP_Query( $args );

			return $posts->posts;
		}

	}

	/**
	 * Exports the selected fields data.
	 */
	static function export_data( $data, $query_args = array(), $params = array() ) {

		set_time_limit( 0 );

		$q_results = self::query_content_type( $query_args );

		$content_type = $query_args['content_type'];

		$fields = array_keys( $data );

		$core_sel_fields = array_intersect( $fields, wp_xprt_get_core_fields( $content_type ) );

		$results = $row = array();

		$field_struct = array_flip( array_merge( $core_sel_fields, $fields ) );

		// Clear the values of the flipped array.
		$field_struct = array_fill_keys( array_keys( $field_struct ), '' );

		// First pass - extract data for the requested fields.

		foreach( $q_results as $object ) {

			$object_core_fields = array();

			foreach( $core_sel_fields as $core_field ) {
				$object_core_fields[ $core_field ] = $object->$core_field;
			}

			if ( 'user' === $content_type ) {
				$all_custom_fields = get_user_meta( $object->ID );
			} else {
				$all_custom_fields = get_post_custom( $object->ID );
			}

			// Flatten the custom fields array to make sure all meta keys are single values.
			wp_xprt_flatten_array( $all_custom_fields );

			// Leave only the requested meta fields.
			$req_custom_fields = array_intersect_key( $all_custom_fields, array_flip( $fields ) );

			// Merge requested core fields with custom fields.
			$req_custom_and_core_fields = array_merge( $object_core_fields, $req_custom_fields );

			// Make sure to retrieve the same columns for all posts, even when field does not exist
			$req_custom_and_core_fields = wp_parse_args( $req_custom_and_core_fields, $field_struct );

			//
			foreach( $req_custom_and_core_fields as $field => $value ) {

				if ( ! isset( $data[ $field ]['type'] ) || ! isset( $data[ $field ]['content_data_type'] ) ) {
					continue;
				}

				$field_type = $data[ $field ]['type'];
				$field_content_data_type = $data[ $field ]['content_data_type'];

				$arr_val = (array) maybe_unserialize( $value );

				// Make data readable by replacing ids or slugs by their respective readable labels.
				array_walk( $arr_val, array( __CLASS__, 'make_readable' ), array(
					'content_type'            => $content_type,
					'object'                  => $object,
					'field'                   => $field,
					'field_type'              => $field_type,
					'field_content_data_type' => $field_content_data_type
				) );

				// Apply a custom label to be used as column name, if requested by the user.
				if ( ! empty( $data[ $field ]['label'] ) ) {
					$label = $data[ $field ]['label'];
				} else {
					$label = $field;
				}

				$row[ $label ] = implode( ', ', (array) self::flatten( $arr_val, '', $params ) );
			}

			$row = apply_filters( 'wp_xprt_export_data_row', $row, $object, $content_type );

			if ( ! empty( $row ) ) {
				$results[ $object->ID ] = $row;
			}

		}

		$results = apply_filters( 'wp_xprt_export_data', $results );

		wp_xprt_export( $results, $params );

		set_time_limit( 30 );

		return $results;
	}

	/**
	 * Recursively flattens an associative array so that values are readable.
	 */
	public static function flatten( $array, $prefix = '', $params = array() ) {
	    $result = array();

	    foreach( $array as $key => $value ) {

			$new_key = ( ! is_numeric( $prefix ) ? $prefix : '' ) . sprintf( empty( $prefix ) ? '%s' : '[%s]', $key );

	        if ( is_array( $value ) ) {
	            $result = array_merge( $result, self::flatten( $value, $new_key, $params ) );
	        } else {
	        	if ( is_numeric( $new_key ) ) {

    				// Make sure all special char are correctly converted and optionally stripped of tags.
	        		if ( ! empty( $params['strip_html_tags'] ) && $params['strip_html_tags'] ) {
	        			$value = wp_strip_all_tags( $value );
	        		}

					$result[] = html_entity_decode( $value );
	        	} else {
	        		if ( is_object( $value ) || is_array( $value ) ) {

		        		if ( ! empty( $params['strip_html_tags'] ) && $params['strip_html_tags'] ) {
		        			$value = array_map( 'wp_strip_all_tags', (array) $value );
		        		}

	        			$result[] = print_r( $value, true );
	        		} else {

    					// Make sure all special char are correctly converted and optionally stripped of tags.
		        		if ( ! empty( $params['strip_html_tags'] ) && $params['strip_html_tags'] ) {
		        			$value = wp_strip_all_tags( $value );
		        		}

	        			$result[] = "{$new_key} = " . html_entity_decode( $value );
	        		}
	            }
	        }
	    }
	    return $result;
	}

	/**
	 * Iterates through the data being exported and converts ID's and other data into meaningful information.
	 *
	 * @todo: maybe recursively decode all fields separated by commas for taxonomies.
	 */
	static function make_readable( &$value, $key, $args) {

		extract( $args );

		if ( 'user' !== $content_type && ( 'taxonomy' === $field_type || ( in_array( $field, get_object_taxonomies( $object->post_type ) ) && 'post_status' !== $field ) ) ) {

			$taxonomy = $field;

			if ( 'taxonomy' === $field_type ) {
				if ( $field == $field_content_data_type ) {
					$fields = 'names';
				} else {
					$fields = 'ids';
				}
				$value = wp_get_post_terms( $object->ID, $taxonomy, array( 'fields' => $fields ) );

				$field_content_data_type = 'terms';
			} else {
				$field_content_data_type = 'single_term';
			}

		}

		switch( $field_content_data_type ) {

			case 'user':
				$user = get_user_by( 'id', $value );
				if ( is_object( $user ) ) {
					$value = $user->display_name;
				}
				break;

			case 'single_term':
				// try geting term by id
				$term = get_term_by( 'id', $value, $taxonomy );
				if ( ! $term ) {
					// try again by slug
					$term = get_term_by( 'slug', $value, $taxonomy );
				}
				if ( $term ) {
					$value = $term->name;
				}
				break;

			case 'post_status':
				$status = get_post_status_object( $value );
				$value = $status->label;
				break;

			case 'post_type':
				$status = get_post_type_object( $value );
				$value = $status->label;
				break;

			case 'date':
				if ( wp_xprt_is_valid_timestamp( $value ) ) {
					$value = wp_xprt_display_date( $value );
				}
				break;

			case 'boolean':
				if ( is_bool( $value ) ) {
					$value = $value ? __( 'Yes', 'wp-smart-export' ) : __( 'No', 'wp-smart-export' );
				}
				break;

			default:

				$post_types = get_post_types( '', 'names' );

				if ( in_array( $field_content_data_type, $post_types )  ) {

					$values = explode( ',', $value );

					$value = array();

					foreach( $values as $post_id ) {
						$value[] = get_the_title( $post_id );
					}

				}

				if ( 'unserialized' === $field_content_data_type ) {
					$value = maybe_unserialize( $value );
				}
/*
				if ( is_array( $value ) ) {
					$value = implode( ', ', $value );
				}
*/
				break;
		}

	}

}


### Hook Callbacks

/**
 * Dynamically outputs the fields table for a given post type.
 */
function _wp_xprt_update_templates_list() {
	global $wp_xprt_options;

	if (  ! wp_verify_nonce( $_POST['_ajax_nonce'], 'wp_xprt_nonce' ) ) {
		die(0);
	}

	echo json_encode( array(
		'templates' => array_keys( $wp_xprt_options->templates ),
	) );

	die(1);
}

/**
 * Dynamically outputs the fields table for a given post type.
 */
function _wp_xprt_get_content_type_fields() {

	if ( empty( $_POST['content_type'] ) || ! wp_verify_nonce( $_POST['_ajax_nonce'], 'wp_xprt_nonce' ) ) {
		die(0);
	}

	$content_type = sanitize_text_field( $_POST['content_type'] );

	$query_args = array(
		'content_type' => $content_type

	);

	echo wp_xprt_output_fields_table( $query_args );

	die(1);
}

/**
 * Dynamically outputs the pre-saved fields settings for a given template.
 */
function _wp_xprt_load_template_content() {
	global $wp_xprt_options;

	if ( ! wp_verify_nonce( $_POST['_ajax_nonce'], 'wp_xprt_nonce' ) ) {
		die(0);
	}

	// User selected 'none' on the template list dropdown.
	if ( empty( $_POST['template'] ) ) {

		$query_args = array(
			'content_type' => $_POST['content_type'],
			'post_status'  => $_POST['post_status'],
		);

		$fields = array();

	} else {

		// User has selected a template.

		$template_name = sanitize_text_field( $_POST['template'] );

		$template_settings = $wp_xprt_options->templates[ $template_name ];

		$query_args = $template_settings['query_args'];
		$fields = $template_settings['fields'];

	}

	$json_args = array(
		'table_output' => wp_xprt_output_fields_table( $query_args, $fields ),
	);

	$json_args = array_merge( $json_args, $query_args );

	echo json_encode( $json_args );
	die(1);
}


### Helper Functions

/**
 * Given a list of fields retrieves them as a normalized associative array.
 */
function wp_xprt_make_fields_struct( $fields, $type = 'custom', $content_type = 'post' ) {

	$fields_struct = array();

	foreach( $fields as $field => $data ) {

		if ( ! is_array( $data ) && $data ) {
			$data = array( 'data' => $data );
		}

		$field_type = $type;

		// Hide internal WP fields.
		if ( wp_xprt_is_reserved_field( $field ) ) {
			$type = 'internal';
		}

		$default = array(
			'name'              => strlen( $field ) > 28 ? substr( $field, 0, 28 ) . '...' : $field,
			'type'              => $type,
			'content_data_type' => wp_xprt_get_field_type( $field, $content_type ),
			'data'              => '-',
			'label'	            => '',
			'checked'           => false,
		);

		$type = $field_type;

		$fields_struct[ $field ] = wp_parse_args( $data, $default );
	}
	return $fields_struct;
}

/**
 * Retrieve fields based on the content type (user or post_type).
 */
function wp_xprt_get_content_type_fields( $query_args = array() ) {

	$defaults = array(
		'content_type' => 'post',
	);
	$query_args = wp_parse_args( $query_args, $defaults );

	if ( 'user' === $query_args['content_type'] ) {

		### users
		return wp_xprt_get_user_fields( $query_args );
	}

	### posts
	$query_args['post_type'] = $query_args['content_type'];

	return wp_xprt_get_post_type_fields( $query_args );
}

/**
 * Retrieves fields and related data, for users.
 */
function wp_xprt_get_user_fields( $query_args = array() ) {
	global $wpdb;

	set_time_limit( 0 );

	$defaults = array(
		'number' => 500,
	);
	$args = wp_parse_args( $query_args, $defaults );

	if ( ! empty( $args['role'] ) && 'any' == $args['role'] ) {
		unset( $args['role'] );
	}

	$users = new WP_User_Query( $args );

	$core_fields = wp_xprt_get_core_fields( $content_type = 'user');

	$core_fields_values = $cust_fields_values = array();

	$contains_sample_data = false;

	// Iterate through the requested posts to retrieve fields and sample content (first value).
	foreach( $users->results as $user ) {

		### core fields / values

		if ( ! $contains_sample_data ) {

			foreach( $core_fields as $field ) {
				$value = $user->$field;

				if ( 'user_pass' === $field ) {
					$value = '***';
				}
				$core_fields_values[ $field ] = wp_strip_all_tags( wp_xprt_get_excerpt( $value ) );
			}

			$contains_sample_data = true;
		}

	}

	unset( $users );

	### custom fields / values.

	$fields = $wpdb->get_results( "SELECT meta_key, meta_value FROM ( SELECT DISTINCT meta_key, meta_value FROM $wpdb->usermeta, ( SELECT ID FROM $wpdb->users LIMIT 0, 50 ) as users WHERE user_id = users.ID GROUP BY meta_key ORDER by meta_value DESC ) as meta ORDER BY meta_key ASC" );

	$custom_fields = array();

	foreach( $fields as $field ) {
		$custom_fields[ $field->meta_key ] = $field->meta_value;
	}

	if ( $custom_fields ) {

		// Convert the custom fields to the normalized fields/data structure.
		$custom_fields = wp_xprt_make_fields_struct( $custom_fields, 'custom', 'user' );

		$cust_fields_values = array_merge( $cust_fields_values, $custom_fields );
	}

	// Convert the core fields to the normalized fields/data structure.
	$core_fields_values = wp_xprt_make_fields_struct( $core_fields_values, 'core', 'user' );

	### taxonomies fields / values.

	$post_type_tax = get_object_taxonomies( $query_args['content_type'], 'objects' );

	// Make sure we keep only valid field types.
	$tax_fields_values = array_intersect_key( $post_type_tax, wp_xprt_field_types( $args['content_type'] ) );

	// Use the taxonomy label as the field label.
	foreach( $tax_fields_values as $key => $field_value ) {

		$tax_fields_values[ $key ] = array(
			'label'        => $field_value->labels->name,
			'content_type' => $key,
		);

	}

	// Convert the custom fields to the normalized fields/data structure.
	$tax_fields_values = wp_xprt_make_fields_struct( $tax_fields_values, 'taxonomy', 'user' );

	$fields_data = array_merge( $core_fields_values, $cust_fields_values, (array) $tax_fields_values );

	set_time_limit( 30 );

	return $fields_data;
}

/**
 * Retrieves fields and related data, for a given post type and query args.
 */
function wp_xprt_get_post_type_fields( $query_args = array() ) {
	global $wpdb;

	set_time_limit( 0 );

	$defaults = array(
		'content_type'   => 'post',
		'post_type'      => 'post',
		'post_status'    => 'any',
		'posts_per_page' => 500,
	);
	$args = wp_parse_args( $query_args, $defaults );

	$posts = new WP_Query( $args );

	$core_fields = wp_xprt_get_core_fields();

	$core_fields_values = $cust_fields_values = array();

	// Build the core field struct (usefull if post type has no posts).
	foreach( $core_fields as $field ) {
		$core_fields_values[ $field ] = __( 'n/a', 'wp-smart-export' );
	}

	$contains_sample_data = false;

	// Iterate through the requested posts to retrieve fields and sample content (first value).
	foreach( $posts->posts as $post ) {

		### core fields / values

		if ( ! $contains_sample_data ) {

			foreach( $core_fields as $field ) {
				$value = $post->$field;

				if ( 'post_password' === $field ) {
					$value = '***';
				}

				$core_fields_values[ $field ] = wp_strip_all_tags( wp_xprt_get_excerpt( $value ) );
			}

			$contains_sample_data = true;
		}

	}

	unset( $posts );

	### custom fields / values.

	$fields = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM ( SELECT DISTINCT meta_key, meta_value FROM $wpdb->postmeta, ( SELECT ID FROM $wpdb->posts WHERE post_type = '%s' LIMIT 0, 50 ) as posts WHERE post_id = posts.ID GROUP BY meta_key ORDER by meta_value DESC ) as meta ORDER BY meta_key ASC", $args['post_type'] ) );

	$custom_fields = array();

	foreach( $fields as $field ) {
		$custom_fields[ $field->meta_key ] = $field->meta_value;
	}

	if ( $custom_fields ) {

		// Convert the custom fields to the normalized fields/data structure.
		$custom_fields = wp_xprt_make_fields_struct( $custom_fields, 'custom' );

		$cust_fields_values = array_merge( $cust_fields_values, $custom_fields );
	}

	// Convert the core fields to the normalized fields/data structure.
	$core_fields_values = wp_xprt_make_fields_struct( $core_fields_values, 'core' );

	### taxonomies fields / values

	$post_type_tax = get_object_taxonomies( $query_args['content_type'], 'objects' );
	unset( $post_type_tax['post_status'] );

	// Make sure we keep only valid field types.
	$tax_fields_values = array_intersect_key( $post_type_tax, wp_xprt_field_types( $args['content_type'] ) );

	// Use the taxonomy label as the field label.
	foreach( $tax_fields_values as $key => $field_value ) {

		$tax_fields_values[ $key ] = array(
			'label'        => $field_value->labels->name,
			'content_type' => $key,
		);

	}

	// Convert the custom fields to the normalized fields/data structure.
	$tax_fields_values = wp_xprt_make_fields_struct( $tax_fields_values, 'taxonomy' );

	$fields_data = array_merge( $core_fields_values, $cust_fields_values, (array) $tax_fields_values );

	set_time_limit( 0 );

	return $fields_data;
}

/**
 * Outputs the fields table.
 */
function wp_xprt_output_fields_table( $query_args = array(), $sel_fields = array() ) {

	$defaults = array(
		'content_type' => 'post'
	);
	$query_args = wp_parse_args( $query_args, $defaults );

	$post_type_fields = wp_xprt_get_content_type_fields( $query_args );

	$parsed_fields = array();

	// Merge selected fields with all the post type fields and check them.
	foreach( $sel_fields as $field => $data ) {
		$post_type_fields[ $field ]['checked'] = true;
		$parsed_fields[ $field ] = wp_parse_args( $data, $post_type_fields[ $field ] );
	}

	$fields = array_merge( $post_type_fields, $parsed_fields );

	// Re-order the fields as set by the user.
	$sorted = array();

	foreach( $sel_fields as $field => $data ) {
		$sorted[ $field ] = $fields[ $field ];
	}

	$fields = array_merge( $sorted, $fields );

	// Output the table.
	$table = new WP_XPRT_Fields_Table( $fields, $query_args['content_type'] );

	ob_start();

	$table->show();

	return ob_get_clean();
}


/**
 * Checks whether a field is WP internal or not.
 */
function wp_xprt_is_reserved_field( $field ) {

	$reserved = array(
		'wp_',
		'closedpostboxes_',
		'metaboxhidden_',
		'session_tokens',
		'nav_menu_recently_edited',
		'managenav-',
		'rich_editing',
		'comment_shortcuts',
		'show_welcome_panel',
		'meta-box-',
		'_edit_lock',
		'_edit_last',
		'admin_color',
		'use_ssl',
		'show_admin_bar_front',
		'last_login',
		'_per_page',
		'_oembed_'
	);

	foreach( $reserved as $keyword ) {

		if ( FALSE !== stripos( $field, $keyword ) ) {
			return true;
		}

	}
	return false;
}
