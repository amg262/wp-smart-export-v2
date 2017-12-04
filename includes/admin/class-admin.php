<?php

add_action( 'admin_enqueue_scripts', '_wp_xprt_register_admin_scripts', 20 );
add_action( 'admin_enqueue_scripts', '_wp_xprt_enqueue_admin_scripts', 21 );
add_action( 'admin_notices', '_wp_xprt_template_missing' );

/**
 * The main admin class.
 */
class WP_Smart_Export_Admin_Page extends BC_Framework_Tabs_Page {

	function __construct( $file = false, $options = null ) {
		parent::__construct( $options );

		if ( class_exists('WP_Smart_Export_Guided_Tour') ) {
	        new WP_Smart_Export_Guided_Tour;
	    }

		add_action( 'admin_init', array( $this, 'init_tooltips' ), 9999 );
	}

	/**
	 * Load tooltips for the current screen.
	 * Avoids loading multiple tooltip instances on metaboxes.
	 */
	public function init_tooltips() {
		new BC_Framework_ToolTips( array( 'toplevel_page_wp-smart-export' ) );
	}

	function init_tabs(){}

	function setup() {
		$this->args = array(
			'page_title'    => __( 'Export', 'wp-smart-export' ),
			'page_slug'     => 'wp-smart-export',
			'parent'        => 'options.php',
			'menu_title'    => __( 'WP Smart Export', 'wp-smart-export' ),
			'submenu_title' => __( 'Export', 'wp-smart-export' ),
			'toplevel'      => 'menu',
			'icon_url'      => 'dashicons-download',
		);
	}

	function page_content() {
		global $wp_xprt_options;

		$premium_notes = '';
		if ( wse_fs()->is_not_paying() ) {
			$premium_notes = __( 'Colored tip icons <span class="dashicons-before dashicons-editor-help tip-icon-premium"></span> represent options with limited functionality on the <em>Free</em> version of <em>WP Smart Export</em>.', 'wp-smart-export' );
		}


		echo html( 'p', __( 'From this page you can export posts or users content to a <em>*.CSV</em> file. Just choose the content type you wish to export and the fields to include on the file. Additionally, you can reorder and select the output type for some of the fields.', 'wp-smart-export' ) );
		echo html( 'p', sprintf( __( 'Hover the tip icons <span class="dashicons-before dashicons-editor-help tip-icon-default"></span> to get more details about each option. %s', 'wp-smart-export' ), $premium_notes ) );

		$premium_features_cron = $premium_features_post_types = '';

		if ( wse_fs()->is_not_paying() ) {
			$premium_features_cron = '<br/><br/>' . sprintf( '<code class="tip-upgrade"><strong>TIP:</strong> Upgrade to the <a href="%s">Pro</a> version and use your saved templates to schedule automatic exports.</code>', admin_url( 'admin.php?page=wp-smart-export-pricing' ) );
		}

		echo $this->form_table( array(

			array(
				'title' => __( 'Templates', 'wp-smart-export' ),
				'type'  => 'select',
				'name'  => 'templates_list',
				'extra' => array(
					'id' => 'templates_list',
				),
				'choices'  => array_keys( $wp_xprt_options->templates ),
				'selected' => ! empty( $_POST['templates_list'] ) ? $_POST['templates_list'] : '',
				'desc'     => html( 'input', array( 'type' => 'submit', 'name' => 'refresh_templates', 'class' => 'refresh-templates button-secondary', 'value' => __( 'Refresh', 'wp-smart-export' ) ) ) . ' ' .
						      html( 'input', array( 'type' => 'submit', 'name' => 'delete_template', 'class' => 'button-secondary', 'value' => __( 'Delete', 'wp-smart-export' ) ) ),
				'text'     => __( '-- Select Template --', 'wp-smart-export' ),
				'tip'      => __( 'The list of all your saved templates. Choosing an existing template automatically loads the saved configuration.', 'wp-smart-export' ) .
						      $premium_features_cron,
		        'tr'       => array( 'tr-templates' ),
			),
			array(
				'title'      => '',
				'name'       => '_blank',
				'type'       => 'custom',
				'line_break' => true,
				'render'     => array( $this, 'line_break' ),
			),
			array(
				'title'  => __( 'Content Type', 'wp-smart-export' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'render' => array( $this, 'output_content_types' ),
				'tip'    => __( 'The content type from which you want to fetch data. Content types can be normal posts, users or any custom posts types used on your site.', 'wp-smart-export' ) .
				 			( wse_fs()->is_not_paying() ? '<br/><br/>' . sprintf( '<code class="tip-upgrade"><strong>TIP:</strong> Upgrade to the <a href="%s">Pro</a> version and export data from any custom post types.</code>', admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ),
	 			'tr'     => array( 'tr-content-type' ),
			),
			array(
				'title'      => '',
				'name'       => '_blank',
				'type'       => 'custom',
				'line_break' => true,
				'render'     => array( $this, 'line_break' ),
			),
			array(
				'title'  => __( 'Query', 'wp-smart-export' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'render' => array( $this, 'output_query_fields' ),
				'tip'    => __( 'Additional query options to filter the data that will be included on your export file.', 'wp-smart-export' ) .
					 '<span class="wp_xprt_user_type">' . ( wse_fs()->is_not_paying() ? '<br/><br/>' . sprintf( '<code class="tip-upgrade"><strong>TIP:</strong> Upgrade to the <a href="%s">Pro</a> version to be able to export users with a specific user role.</code>', admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ) . '</span>' .
					 '<span class="wp_xprt_post_type">' . ( wse_fs()->is_not_paying() ? '<br/><br/>' . sprintf( '<code class="tip-upgrade"><strong>TIP:</strong> Upgrade to the <a href="%s">Pro</a> version to be able to export posts with a specific post status.</code>', admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ) . '</span>',
	 			'tr'     => array( 'tr-query' ),
			),
			array(
				'title'      => '',
				'name'       => '_blank',
				'type'       => 'custom',
				'line_break' => true,
				'render'     => array( $this, 'line_break' ),
			),
			array(
				'title'  => __( 'Options', 'wp-smart-export' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'render' => array( $this, 'output_fields_filters' ),
				'tip'    => __( 'Choose if you want the table to display custom fields and also if you want taxonomies to be displayed as fields (not available for the <code>Users</code> content type).', 'wp-smart-export' ),
	 			'tr'     => array( 'tr-options' ),
			),
			array(
				'title'      => '',
				'name'       => '_blank',
				'type'       => 'custom',
				'line_break' => true,
				'render'     => array( $this, 'line_break' ),
				'tip'        => __( 'Choose the fields to output and how they should be outputted. For example, a custom field that stores timestamps could be .', 'wp-smart-export' )
			),
			array(
				'title'  => __( 'Fields', 'wp-smart-export' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'render' => array( $this, 'table_fields_title' ),
				'tip'    => $this->table_fields_help(),
	 			'tr'     => array( 'tr-fields' ),
			),
			array(
				'title'  => '',
				'name'   => '_blank',
				'type'   => 'custom',
				'render' => array( $this, 'output_fields_table' ),
			),
			array(
				'title'      => '',
				'name'       => '_blank',
				'type'       => 'custom',
				'line_break' => true,
				'render'     => array( $this, 'line_break' ),
			),
			array(
				'title'  => __( 'Date Span', 'wp-smart-export' ),
				'name'   => '_blank',
				'type'   => 'custom',
				'render' => array( $this, 'output_date_span' ),
				'tip'    => __( 'Choose the content date interval. The post date will be used for post types content and the registered date for users content. Leave empty to export all content.', 'wp-smart-export' ) .
						( wse_fs()->is_not_paying() ? '<br/><br/>' . sprintf( '<code class="tip-upgrade"><strong>TIP:</strong> Upgrade to the <a href="%s">Pro</a> version to schedule automatic daily, weekly, monthly exports.</code>', admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ),
	 			'tr'     => array( 'tr-date-span' ),
			),
			array(
				'title' => __( 'Template', 'wp-smart-export' ),
				'type'  => 'text',
				'name'  => 'template_name',
				'extra' => array(
					'class' => 'regular-text field_dependent',
				),
				'default' => 'my_template',
				'desc'    => html( 'input', array( 'name' => 'save_template', 'type' => 'checkbox', 'class' => 'field_dependent' ) ) . __( 'Save/Update Template on Export', 'wp-smart-export' ),
				'tip'     => __( 'Specify a template name and check the option to save on export, if you wish to save your export settings. Templates can be loaded later for similar exports or to be used on a scheduled export.', 'wp-smart-export' ),
	 			'tr'     => array( 'tr-template-name' ),
			),
			array(
				'title' => __( 'File Name', 'wp-smart-export' ),
				'type'  => 'text',
				'id'    => 'filename',
				'name'  => 'filename',
				'extra' => array(
					'class' => 'regular-text field_dependent required',
				),
				'default' => 'my_template',
				'tip' =>  __( 'The export content destination file name.', 'wp-smart-export' ) .
							( wse_fs()->is_not_paying() ? '<br/><br/>' . sprintf( '<code class="tip-upgrade"><strong>TIP:</strong> Upgrade to the <a href="%s">Pro</a> version and automatically email files to a custom list of recipients on scheduled exports.</code>', admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ),
	 			'tr'     => array( 'tr-filename' ),
			),
			array(
				'title'   => __( 'Delimiter', 'wp-smart-export' ),
				'type'    => 'select',
				'name'    => 'file_delimiter',
				'choices' => array(
					';' => __( '; Semicolon', 'wp-smart-export' ),
					',' => __( ', Comma', 'wp-smart-export' ),
					'|' => __( '| Pipe', 'wp-smart-export' )
				),
				'default' => ';',
	 			'tr'      => array( 'tr-delimiter' ),
			),
			array(
				'title' => __( 'HTML', 'wp-smart-export' ),
				'type'  => 'checkbox',
				'name'  => 'strip_html_tags',
				'desc'  => __( 'Remove HTML tags', 'wp-smart-export' ),
				'tip'   => __( 'Check this option to remove any HTML tags from the content being exported (paragraphs are converted to line breaks).', 'wp-smart-export' ),
	 			'tr'    => array( 'tr-html' ),
			),
			array(
				'title'      => '',
				'name'       => '_blank',
				'type'       => 'custom',
				'line_break' => true,
				'render'     => array( $this, 'line_break' ),
			),
			// hidden fields
			array(
				'title' => '',
				'name'  => 'def_template_name',
				'type'  => 'hidden',
				'extra' => array(
					'id' => 'def_template_name',
					'class' => 'hidden',
				),
				'default' => 'my_template',
			),
			array(
				'title' => '',
				'name'  => 'def_filename',
				'type'  => 'hidden',
				'extra' => array(
					'id' => 'def_filename',
					'class' => 'hidden',
				),
				'default' => 'my_template',
			),
			array(
				'title' => '',
				'name'  => 'fields_order',
				'type'  => 'hidden',
				'extra' => array(
					'id' => 'fields_order',
					'class' => 'hidden',
				),
			),

		) );

	}

	function line_break() {
		return '<hr/>';
	}

	function table_fields_title() {
		return html( 'p', __( 'The table below shows all the available fields for the selected content type, and a sample of the content inside each field.', 'wp-smart-export' ) );
	}

	function table_fields_help() {
		$output = html( 'p', __( 'You can reorder the fields and customize the name that will be displayed on the exported file header row. If you\'re exporting custom fields that contain \'unreadable\' content like a user ID, a timestamp or a category term, you can choose to export them as <code>User</code>, <code>Date</code> and <code>Categories</code>, respectively. '
				. 'Instead of the user ID, and the unreadable timestamp and category ID you\'ll have the username, a readable date and the category name in the exported file. The appropriate export type is automatically assigned for known WordPress fields like <code>post_author</code> or <code>post_status</code>, etc.', 'wp-smart-export' ) );
		$output .= '<br/>';
		$output .= html( 'p', __( 'When selecting the field output make sure you known it\'s content, otherwise you may end up with incorrect data on that field. Use the field name and the sample content to help you choose the correct field type. '
				. 'For example, a custom field with the name <code>_reference_user_id</code> is probably storing a user ID that you can export as <code>User</code> to get a username instead of an ID.', 'wp-smart-export' ) );
		$output .= '<br/>';
		$output .= html( 'p', __( '<strong>Export As:</strong>', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>Text:</code> The field value is exported as is, with no changes.', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>Date:</code> The field value is exported as a date. Recommended for timestamps.', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>User:</code> The field value is exported as a username. Recommended for User ID\'s.', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>Post Type:</code> The field value is exported as a human readable post type. Recommended for post types.', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>Post Status:</code> The field value is exported as a human readable post status. Recommended for post statuses.', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>Taxonomy:</code> The field value is exported as a human readable taxonomy name. Recommended for taxonomy term ID\'s.', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>Unserialized:</code> Serialized values will be unserialized and separated by commas on a human readable form. Recommended for serialized values.', 'wp-smart-export' ) );
		$output .= html( 'p', __( 'Example of a serialized value: <code>\'a:3:{i:0;s:5:"apple";i:1;s:6:"banana";i:2;s:6:"orange";}\'</code>. The unserialized value would be: <code>apple, banana, orange</code>. ', 'wp-smart-export' ) );
		$output .= html( 'p', __( '<code>Posts (post):</code> The field value(s) are interpreted as post ID\'s and will be converted to their respective post titles (ID\'s must be separated by commas. Only works with flat unserialized values). ', 'wp-smart-export' ) );
		$output .= html( 'p', __( 'Supposing you have 2 posts named: <code>foo</code> and <code>bar</code> with post ID\'s <code>100</code> and <code>102</code>, respectively. If the field value contained the post ID\'s: <code>100, 102</code>. The exported value would be: <code>foo, bar</code>.', 'wp-smart-export' ) );
		$output .= '<br/>';
		$output .= html( 'p', __( '<strong>IMPORTANT:</strong> Please note that some custom fields may contain sensitive data. Be careful when choosing the fields to export.', 'wp-smart-export' ) );

		return $output;
	}

	function form_table( $rows, $formdata = false ) {
		$output = '';
		foreach ( $rows as $row ) {
			$output .= $this->table_row( $row, $formdata );
		}

		$output = $this->form_table_wrap( $output );

		return $output;
	}

	function form_table_wrap( $content ) {

		$args = array(
			'class' => 'button-primary',
			'value' => __( 'Export to CSV', 'wp-smart-export' ),
		);

		$output = $this->table_wrap( $content );
		$output = $this->form_wrap( $output, $args );

		return $output;
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	static function table_wrap( $content ) {
			return html( "table class='form-table wp-xprt'", $content );
	}

	function form_handler() {

		if ( empty( $_POST['submit'] ) && empty( $_POST['action'] ) && empty( $_POST['delete_template'] ) ) {
			return false;
		}

		if ( ! empty( $_POST['delete_template'] ) ) {
			if ( ! empty( $_POST['templates_list'] ) ) {
				wp_xprt_delete_template( $_POST['templates_list'] );
			} else {
				echo scb_admin_notice( __( 'Please select a template to delete.', 'wp-smart-export' ), 'error' );
			}
			return;
		}

		if ( empty( $_POST['field'] ) ) {
			echo scb_admin_notice( __( 'Please select some fields to export.', 'wp-smart-export' ), 'error' );
			return;
		}

		if ( empty( $_POST['wp_xprt_content_type'] ) ) {
			echo scb_admin_notice( __( 'Please select a content type to export.', 'wp-smart-export' ), 'error' );
			return;
		}

		// only skip nonce check when saving a template since it already checks the nonce when calling the parent form handler
		if ( empty( $_POST['save_template'] ) ) {
			check_admin_referer( $this->nonce );
		} else  {

			if ( empty( $_POST['template_name'] ) ) {
				echo scb_admin_notice( __( 'Please name your template.', 'wp-smart-export' ), 'error' );
				return;
			}

		}

		if ( empty( $_POST['filename'] ) ) {
			echo scb_admin_notice( __( 'Please name your export file.', 'wp-smart-export' ), 'error' );
			return;
		}

		// handle and retrieve the requested export data
		$export_data = $this->handle_sel_fields();

		### save the current settings if requested by the user

		if ( ! empty( $_POST['save_template'] ) ) {
			$this->save_template( $export_data, sanitize_text_field( $_POST['template_name'] ) );
		}

		### export the data

		$export_params['filename'] = sanitize_text_field( $_POST['filename'] );

		if ( ! empty( $_POST['from_date'] ) ) {
			$from_date = $_POST['from_date'];

			$export_params['filename'] .= "-$from_date";
		}

		if ( ! empty( $_POST['to_date'] ) ) {
			$to_date = $_POST['to_date'];

			$export_params['filename'] .= "-$to_date";
		}

		if ( ! empty( $_POST['file_delimiter'] ) ) {
			$export_params['delimiter'] = $_POST['file_delimiter'];
		}

		if ( isset( $_POST['strip_html_tags'] ) ) {
			$export_params['strip_html_tags'] = $_POST['strip_html_tags'];
		}

		$this->export_data( $export_data, $export_params );
	}

	function admin_msg( $msg = '', $class = 'updated' ) {
		if ( empty( $msg ) ) {
			$msg = __( 'Template <strong>saved</strong>.', 'wp-smart-export' );
		}

		echo scb_admin_notice( $msg, $class );
	}


	### Output Methods

	/**
	 * Output the available content types.
	 */
	function output_content_types() {

		$post_type =  array(
			'title' => __( 'Content Types', 'wp-smart-export' ),
			'type'  => 'select',
			'name'  => 'wp_xprt_content_type',
			'extra' => array(
				'id' => 'wp_xprt_content_type',
			),
			'value'   => wp_xprt_get_content_types(),
			'default' => 'post',
		);

		$output = $this->input( $post_type );

		return $output;
	}

	/**
	 * Output settings to be used on the main WP_Query for retrieving fields.
	 */
	function output_query_fields() {

		$post_status = array(
			'title' => __( 'Post Status', 'wp-smart-export' ),
			'type'  => 'select',
			'name'  => 'wp_xprt_post_status',
			'extra' => array(
				'class' => 'wp_xprt_post_type',
				'id'    => 'wp_xprt_post_status',
			),
			'value'   => wp_xprt_get_post_statuses(),
			'text'    => __( ' -- Post Status --', 'wp-smart-export' ),
			'default' => 'publish',
		);

		$output = $this->input( $post_status );

		$user_role = array(
			'title' => __( 'Role', 'wp-smart-export' ),
			'type'  => 'select',
			'name'  => 'wp_xprt_user_role',
			'extra' => array(
				'class' => 'wp_xprt_user_type',
				'style' => 'display: none',
				'id'    => 'wp_xprt_user_role',
			),
			'value'   => wp_xprt_get_roles(),
			'text'    => __( ' -- User Role --', 'wp-smart-export' ),
			'default' => 'any'
		);

		$user_output = $this->input( $user_role );

		$output .= $user_output;

		return $output;
	}

	/**
	 * Output settings to to filter the visible fields.
	 */
	function output_fields_filters() {

		$output = html( 'p', html( 'span', array( 'class' => 'filter_label' ), html( 'input', array( 'type' => 'checkbox', 'name' => 'show_custom', 'class' => 'custom_fields_toggle', 'checked' => 'checked' ) ) . ' ' . sprintf( '%s <span class="show-hidden-fields">%s</span> %s', __( 'Display', 'wp-smart-export' ), __( 'custom', 'wp-smart-export' ), __( 'fields', 'wp-smart-export' ) ) ) );
		$output .= html( 'p', html( 'span', array( 'class' => 'filter_label filter_label_child' ), html( 'input', array( 'type' => 'checkbox', 'name' => 'show_internal', 'class' => 'internal_fields_toggle' ) ) . ' ' . sprintf( '%s <span class="show-internal-fields">%s</span> %s', __( 'Display', 'wp-smart-export' ), __( 'internal custom', 'wp-smart-export' ), __( 'fields', 'wp-smart-export' ) ) ) );

		$output .= html( 'p', html( 'span', array( 'class' => 'filter_label wp_xprt_post_type' ), html( 'input', array( 'type' => 'checkbox', 'name' => 'show_tax', 'class' => 'taxonomy_fields_toggle', 'checked' => 'checked' ) ) . ' ' . sprintf( '%s <span class="show-taxonomy-fields">%s</span> %s', __( 'Append post type ', 'wp-smart-export' ), __( 'taxonomies', 'wp-smart-export' ), __( 'as fields', 'wp-smart-export' ) ) ) );

		return $output;
	}

	/**
	 * Outputs the settings for the export date span.
	 */
	function output_date_span() {

		$atts = array(
			'type'        => 'text',
			'id'          => 'from_date',
			'name'        => 'from_date',
			'class'       => 'span_date field_dependent',
			'style'       => 'width: 120px;',
			'placeholder' => __( 'click to choose...', 'wp-smart-export' ),
			'readonly'    => true,
		);
		$output = __( 'From:', 'wp-smart-export' ) . ' ' . html( 'input', $atts );

		$atts['id']          = 'to_date';
		$atts['name']        = 'to_date';
		$atts['placeholder'] = __( 'click to choose...', 'wp-smart-export' );

		$output .= ' ' . __( 'To:', 'wp-smart-export' ) . ' ' . html( 'input', $atts );

		$output .= html( 'a', array( 'class' => 'button clear_span_dates' ), __( 'Clear', 'wp-smart-export' ) );

		return $output;
	}

	/**
	 * Outputs the main fields table.
	 */
	function output_fields_table() {
		global $wp_xprt_options;

		if ( ! empty( $_POST['templates_list'] ) ) {

			$template_name = sanitize_text_field( $_POST['templates_list'] );

			if ( ! empty( $wp_xprt_options->templates[ $template_name ] ) ) {
				$template_settings = $wp_xprt_options->templates[ $template_name ];

				$query_args = $template_settings['query_args'];

				return wp_xprt_output_fields_table( $query_args );
			}

		}

		// if user tried to export fields without selecting a template, keep his selected fields data
		$posted_fields_data = $this->handle_sel_fields();

		if ( $posted_fields_data ) {

			extract( $posted_fields_data );

			return wp_xprt_output_fields_table( $query_args, $fields );
		}

		return wp_xprt_output_fields_table();
	}


	### handle the requested export data

	function handle_sel_fields() {

		### get requested fields, labels and types

		if ( empty( $_POST['field'] ) ) {
			return false;
		}

		$sel_fields = $_POST['field'];
		$fields_order = $sel_fields;

		// keep only the selected fields sorted, as requested by the user
		if ( ! empty( $_POST['fields_order'] ) ) {
			$fields_order = explode( ',', $_POST['fields_order'] );
			$sel_fields = array_intersect( $sel_fields, $fields_order );
		}

		$sel_fields_flipped = array_flip( $sel_fields );

		// clear the values of the flipped array
		$sel_fields_flipped = array_fill_keys( array_keys( $sel_fields_flipped ), '' );

		$field_types = $field_content_data_types = $field_labels = $sel_fields_flipped;

		// default field type
		if ( ! empty( $_POST['type'] ) ) {
			$field_types = wp_parse_args( $_POST['type'], $field_content_data_types );
		}

		// selected field data type
		if ( ! empty( $_POST['content_data_type'] ) ) {
			$field_content_data_types = wp_parse_args( $_POST['content_data_type'], $field_content_data_types );
		}

		// field label
		if ( ! empty( $_POST['label'] ) ) {
			$field_labels = wp_parse_args( $_POST['label'], $field_labels );
		}

		$data = array();

		foreach( $sel_fields as $field ) {

			$data[ $field ] = array(
				'name'		   => $field,
				'label'		   => $field_labels[ $field ],
				'type'		   => $field_types[ $field ],
				'content_data_type' => $field_content_data_types[ $field ]
			);

		}

		### Users

		if ( 'user' == $_POST['wp_xprt_content_type'] ) {

			### get requested users query args

			$query_args =  array(
				'content_type'	=> $_POST['wp_xprt_content_type'],
				'role'	=> $_POST['wp_xprt_user_role'],
			);

			if ( ! empty( $_POST['from_date'] ) ) {

				$from_date = $_POST['from_date'];

				$query_args['user_date_query'] = array(
					'from_date' => $from_date,
				);

				if ( ! empty( $_POST['to_date'] ) ) {
					$query_args['user_date_query']['to_date'] = $_POST['to_date'];
				}

			}

		} else {

			### get requested posts query args

			$query_args =  array(
				'content_type'	=> $_POST['wp_xprt_content_type'],
				'post_status'	=> $_POST['wp_xprt_post_status'],
			);

			if ( ! empty( $_POST['from_date'] ) ) {

				$from_date = $_POST['from_date'];

				$query_args['date_query'] = array(
					array(
						'after' => $from_date,
						'inclusive' => true,
					),
				);

				if ( ! empty( $_POST['to_date'] ) ) {

					$to_date = $_POST['to_date'];

					if ( ! strtotime( $from_date ) > strtotime( $to_date ) ) {
						$query_args['date_query'][0]['before'] = $to_date;
					}

				}

			}

		}

		$export_data = array(
			'fields'	 => $data,
			'query_args' => $query_args,
		);

		return $export_data;
	}

	/**
	 * Save the export settings as a template.
	 */
	function save_template( $export_data, $template_name ) {
		global $wp_xprt_options;

		parent::form_handler();

		$templates = $wp_xprt_options->templates;

		$templates[ $template_name ] = $export_data;

		$wp_xprt_options->templates = $templates;
	}

	/**
	 * Export the table data.
	 */
	function export_data( $data, $export_params ) {

		if ( empty( $data['query_args'] ) ) {
			echo scb_admin_notice( __( 'Invalid content type!', 'wp-smart-export' ), 'error' );
			return;
		}

		$default = array(
			'content_type' => 'post'
		);
		$data['query_args'] = wp_parse_args( $data['query_args'], $default );

		if ( 'user' != $data['query_args']['content_type'] ) {
			$data['query_args']['post_type'] = $data['query_args']['content_type'];
		}

		$results = WP_XPRT_Fields_Table::export_data( $data['fields'], $data['query_args'], $export_params );

		if ( is_wp_error( $results ) ) {

			echo scb_admin_notice( sprintf( __( 'An error occurred - %s. Could not export data!', 'wp-smart-export' ), 'error' ), $results->get_error_message() );

		} elseif ( ! count( $results ) ) {

			echo scb_admin_notice( __( 'No data found for the given post and settings.', 'wp-smart-export' ) );

 		}

 		return $results;
	}

}


### Hook Callbacks

/**
 * Check for missing templates.
 */
function _wp_xprt_template_missing() {
	global $post;

	if ( empty( $_GET['post'] ) || empty( $post ) || WP_XPRT_SCHEDULE_POST_TYPE != $post->post_type ) {
		return;
	}

	if ( ! get_post_meta( $post->ID, '_wp_xprt_template', true ) ) {
		echo scb_admin_notice( __( 'NOTE: You haven\'t selected a template for this schedule. It will remain inactive until you assign an existing template.', 'wp-smart-export' ), 'error' );
	}
}

/**
 * Register admin JS scripts and CSS styles.
 */
function _wp_xprt_register_admin_scripts() {

	wp_register_script(
		'wp_xprt-settings',
		plugin_dir_url(__FILE__) . 'js/scripts.js',
		array( 'jquery' ),
		WP_XPRT_VERSION,
		true
	);

	wp_register_script(
		'validate',
		plugin_dir_url(__FILE__) . 'js/jquery.validate.min.js',
		array( 'jquery' ),
		WP_XPRT_VERSION
	);

	wp_register_style(
		'wp_xprt-css',
		plugin_dir_url(__FILE__) . 'css/styles.css'
	);

}

/**
 * Enqueue registered admin JS scripts and CSS styles.
 */
function _wp_xprt_enqueue_admin_scripts( $hook ) {

	// Selective load.
	$parent_menu_slug = 'toplevel_page_wp-smart-export';

 	if ( empty( $_GET['post_type'] ) && empty( $_GET['post'] ) && $hook !== $parent_menu_slug ) {
		return;
    }

	if ( $hook !== $parent_menu_slug ) {

		if ( ! empty( $_GET['post'] ) ) {
			$post = get_post( (int) $_GET['post'] );
			$post_type = $post->post_type;
		} else {
			$post_type = $_GET['post_type'];
		}

		if ( $post_type !== WP_XPRT_SCHEDULE_POST_TYPE ) {
			return;
		}

	}

	wp_enqueue_script('wp_xprt-settings');
	wp_enqueue_script('validate');
	wp_enqueue_script('jquery-ui-datepicker');
	wp_enqueue_script('jquery-ui-sortable');

	wp_enqueue_style('wp_xprt-css');
	wp_enqueue_style('wp_xprt-jquery-ui-css', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');


	wp_localize_script( 'wp_xprt-settings', 'wp_xprt_admin_l18n', array(
		'ajaxurl'         => admin_url('admin-ajax.php'),
		'ajax_nonce'      => wp_create_nonce('wp_xprt_nonce'),
		'date_format'     => get_option('date_format'),
		'msg_refreshing'  => __( 'Updating table content. This may take some time depending on your database size and number of fields. Please wait ...', 'wp-smart-export' ),
		'msg_cancel'      => __( 'Cancel', 'wp-smart-export' ),
		'msg_refresh'     => __( 'Try Again', 'wp-smart-export' ),
		'msg_fatal_error' => __( 'There was a problem exporting this content. You may need to increase your PHP memory limits.', 'wp-smart-export' ),
    ) );

}


### Helpers

/**
 * Deletes an existing template.
 */
function wp_xprt_delete_template( $name ) {
	global $wp_xprt_options;

	$templates = $wp_xprt_options->templates;

	unset( $templates[ $name ] );

	$wp_xprt_options->templates = $templates;

	echo scb_admin_notice( __( 'The template was deleted.', 'wp-smart-export' ) );
}
