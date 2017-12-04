<?php
/**
 * Provides the info to use on the guided tour and help pages.
 */
class WP_Smart_Export_Guided_Tour extends BC_Framework_Pointers_Tour {

	public function __construct() {

		parent::__construct( 'toplevel_page_wp-smart-export', array(
			'version'     => '1.0',
			'prefix'      => 'wp_smart_export_tour',
			'text_domain' => 'wp-smart-export',
			'help'        => true
        ) );

	}

	/**
	 * The guided tour steps.
	 */
	protected function pointers() {
		$pointers['step1'] = array(
			'title'     => html( 'h3', sprintf( __( 'Welcome to <em>%s</em>!', 'wp-smart-export' ), 'WP Smart Export' ) ),
			'content'   => html( 'p', sprintf( __( 'This is the main screen for manual exporting data. Here you can also create automatic export templates for regular manual exports or for use on scheduled exports%s.', 'wp-smart-export' ),
						   ( wse_fs()->is_not_paying() ? ' (*)' : '' ) ) ) .
						   html( 'p', __( 'Most of the options below are optional.', 'wp-smart-export' ) ) .
						   html( 'p', __( 'Essentially, you just need to choose what content type and which fields you want to export.', 'wp-smart-export' ) ) .
						   html( 'p', html( 'span class="dashicons-before dashicons-editor-help"', '&nbsp;' ) . ' ' . __( 'If you need to revisit the guided tour later or just disable it use the - <em>Screen Options</em> - tab on top of the page. If you need more help click the - <em>Help</em> - tab, also on top of the page.', 'wp-smart-export' ) ) .
						   html( 'p' , ( wse_fs()->is_not_paying() ? sprintf( __( '(*) <a href="%s">Pro plan only</a>', 'wp-smart-export' ), admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ) ),
			'anchor_id' => 'h2:first',
			'edge'      => 'top',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step2'] = array(
			'title'     => html( 'h3', __( 'Templates', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'Templates you have previous saved will be displayed on the templates list. Saved templates contain all your export settings.', 'wp-smart-export' )  ) .
						   html( 'p', __( 'To load a saved template simply choose a template from the list.', 'wp-smart-export' )  ) .
						   html( 'p', __( 'Use the <em>Refresh</em> button after you\'ve saved a new template to update the templates list.', 'wp-smart-export' )  ) .
						   html( 'p', __( 'To remove a template just click <em>Delete</em>.', 'wp-smart-export' )  ),
			'anchor_id' => '.tr-templates span.description',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step3'] = array(
			'title'     => html( 'h3', __( 'Content Types', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'Here you can choose which content type you wish to export: <em>Posts, Users, Pages, etc</em>.', 'wp-smart-export' ) .
						   html( 'p', sprintf( __( 'Custom post types %s from any themes or plugins you\'ve installed will also be selectable (e.g: WooCommerce <em>Products</em>, ClassiPress <em>Ads</em>, etc).', 'wp-smart-export' ),
						   ( wse_fs()->is_not_paying() ? ' (*)' : '' ) ) ) .
						   html( 'p' , ( wse_fs()->is_not_paying() ? sprintf( __( '(*) <a href="%s">Pro plan only</a>', 'wp-smart-export' ), admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ) ) ),
			'anchor_id' => '.tr-content-type select',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step4'] = array(
			'title'     => html( 'h3', __( 'Query', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'This option acts as a filter for the content being exported and varies depending on if you are exporting <em>posts</em> or </em>users</em>.', 'wp-smart-export' ) .
						   html( 'p', sprintf( __( '<em>Exporting Posts</em>: you can choose to export <em>posts</em> with any status or with a specific post status%s.', 'wp-smart-export' ),
						   ( wse_fs()->is_not_paying() ? ' (*)' : '' ) ) ) .
						   html( 'p', sprintf( __( '<em>Exporting Users</em>: you can choose to export <em>users</em> with any role or with a specific user role%s.', 'wp-smart-export' ),
						   ( wse_fs()->is_not_paying() ? ' (*)' : '' ) ) ) .
						   html( 'p' , ( wse_fs()->is_not_paying() ? sprintf( __( '(*) <a href="%s">Pro plan only</a>', 'wp-smart-export' ), admin_url( 'admin.php?page=wp-smart-export-pricing' ) ) : '' ) ) ),
			'anchor_id' => '.tr-query select',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step5'] = array(
			'title'     => html( 'h3', __( 'Options', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'Check these options considering the data you wish to export. For basic data exports you can leave these options unchanged.', 'wp-smart-export' )  ) .
						   html( 'p', __( '<em>Display Custom Fields:</em> Check to be able to export custom fields.', 'wp-smart-export' )  ) .
						   html( 'p', __( '<em>Display Internal Custom Fields:</em> Check to be able to export WordPress internal custom fields (not recommended).', 'wp-smart-export' )  ) .
						   html( 'p', __( '<em>Append post type taxonomies as fields:</em> Check to be able to export posts taxonomies like: <em>categories, tags, etc</em>.', 'wp-smart-export' )  ),
			'anchor_id' => '.tr-options td:last-of-type',
			'edge'      => 'top',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step6'] = array(
			'title'     => html( 'h3', __( 'Export Fields', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'The table below shows all the fields available for export. It will vary depending on the content type you\'ve previously selected (e.g: <em>posts, pages, users, etc</em>).', 'wp-smart-export' )  ),
			'anchor_id' => '.tr-fields td:last-of-type',
			'edge'      => 'bottom',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step7'] = array(
			'title'     => html( 'h3', __( 'Fields Column', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'The list of field names available for the selected content type as stored on the database.', 'wp-smart-export' )  ) .
						   html( 'p', __( 'Check the fields you wish to export, one by one, or click the top most checkbox to check/uncheck all fields.', 'wp-smart-export' )  ) .
						   html( 'p', __( 'You can sort the fields using simple drag&drop.', 'wp-smart-export' )  ),
			'anchor_id' => '.wp_xprt_table thead th:nth-child(1)',
			'edge'      => 'bottom',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step8'] = array(
			'title'     => html( 'h3', __( 'Header Label Column', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'Use the inputs in this column to rename the fields as you wish. These will be used as the fields column names on the exported file.', 'wp-smart-export' )  ),
			'anchor_id' => '.wp_xprt_table thead th:nth-child(2)',
			'edge'      => 'bottom',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step9'] = array(
			'title'     => html( 'h3', __( 'Sample Content Column', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'This column contains a short sample of the content available in each field on the database.', 'wp-smart-export' )  ) .
						   html( 'p', __( 'Use this field as a reference for choosing if you want to export it as is or make it readable.', 'wp-smart-export' )  ),
			'anchor_id' => '.wp_xprt_table thead th:nth-child(3)',
			'edge'      => 'bottom',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step10'] = array(
			'title'     => html( 'h3', __( 'Export Field As Column', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'In this column you can specify how the data for each field should be exported. You can choose to export the data as is, or make it readable by selecting a different export type.', 'wp-smart-export' )  ) .
						   html( 'p', __( '<em>Examples:</em><br><br/> If a field contains a <em>user ID</em> for identifying the user <em>John Doe</em>, you can choose to export the field as a user name by selecting <em>User</em>. The field will contain the user name instead of the ID.
						   					<br><br/> Likewise, if a field contains a <em>term ID</em> for identifying a category named <em>Fruits</em>, you can choose to export the field as a taxonomy name by selecting <em>Taxonomy::Categories</em>. The field will contain the term name instead of the ID.', 'wp-smart-export' )  ),
			'anchor_id' => '.wp_xprt_table thead th:nth-child(4)',
			'edge'      => 'bottom',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step11'] = array(
			'title'     => html( 'h3', __( 'Date Span', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'Use the date fields to export data within a specific date interval.', 'wp-smart-export' ) ),
			'anchor_id' => '.tr-date-span .button',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step12'] = array(
			'title'     => html( 'h3', __( 'Template Name', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'If you intend to replicate this export at a later date fill in a meaningful template name here and check the  <em>Save/Update Template on Export</em> option.', 'wp-smart-export' ) ) .
						   html( 'p', __( 'When checked, the <em>Save/Update Template on Export</em> option saves all your export settings as a template that can be later re-used.', 'wp-smart-export' ) ),
			'anchor_id' => '.tr-template-name input[type=text]',
			'edge'      => 'top',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step13'] = array(
			'title'     => html( 'h3', __( 'File Name', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'The name to give the file that will contain the exported data.', 'wp-smart-export' ) ),
			'anchor_id' => '.tr-filename input',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step14'] = array(
			'title'     => html( 'h3', __( 'Delimiter', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'Choose the fields column delimiter here.', 'wp-smart-export' ) ),
			'anchor_id' => '.tr-delimiter select',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step15'] = array(
			'title'     => html( 'h3', __( 'HTML', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'Check this option if you wish to remove all HTML tags from the exported fields (paragraphs will be replaced by line breaks).', 'wp-smart-export' ) ) .
						   html( 'p', __( 'If left unchecked, any HTML tags will also be exported.', 'wp-smart-export' ) ),
			'anchor_id' => '.tr-html .description',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' )
		);

		$pointers['step16'] = array(
			'title'     => html( 'h3', __( 'Export to CSV!', 'wp-smart-export' )  ),
			'content'   => html( 'p', __( 'When you are ready click here to export the data.', 'wp-smart-export' ) ) .
						   html( 'p', __( 'If you\'ve checked the <em>Save/Update Template on Export</em> option all your current export settings will also be saved.', 'wp-smart-export' ) ),
			'anchor_id' => 'p.submit input[type=submit]',
			'edge'      => 'left',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' ),
		);

		$pointers['help'] = array(
			'title'     => html( 'h3', sprintf( __( 'Thanks for using %s!', 'wp-smart-export' ), 'WP Smart Export' ) ),
			'content'   => html( 'p', __( 'If you need to revisit this guided tour later or need specific help on an option use the - <em>Screen Options</em> - or - <em>Help</em> - tabs on the top right.', 'wp-smart-export' ) ),
			'anchor_id' => 'h2:first',
			'edge'      => 'top',
			'align'     => 'left',
			'where'     => array( 'toplevel_page_wp-smart-export' ),
		);
		return $pointers;
	}

	/**
	 * The help tabs.
	 */
	protected function help() {
		$tabs = array();

		$pointers = $this->pointers();

		unset( $pointers['help'] );

		foreach( $pointers as $id => $pointer ) {

			$tabs[] = array(
				'id'      => $id,
				'title'   => wp_strip_all_tags( $pointer['title'] ),
				'content' => $pointer['content'],
			);

		}

		return $tabs;
	}

}
