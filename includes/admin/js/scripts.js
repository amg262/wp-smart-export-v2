jQuery(document).ready(function($) {

	var xhr = '';

	var validator = $('form[name=post]').validate({
		errorElement: "div",
		errorPlacement: function(error, element) {
			error.insertAfter( element.closest('label') );
		},
	});

	$('.tip-upgrade, [data-tooltip*=tip-upgrade]').parents('.tip').addClass( 'color-upgrade' );

	refresh_query_fields();

	// Single schedules page.

	$(document).on( 'change', '#_wp_xprt_period', function() {

		if ( 'custom' == $(this).val() ) {
			$('#_wp_xprt_period_custom' ).closest('tr').fadeIn();
		} else {
			$('#_wp_xprt_period_custom' ).closest('tr').hide();
		}

	});

	$('#_wp_xprt_period').trigger('change');

	// Hide rows with hidden inputs.

	$('input.hidden').closest('tr').hide();


	// Hidden fields & taxonomy fields toggle.


	// Disable internal fields by default.

	$('.custom_fields_toggle').on( 'change', function() {
		$('.wp_xprt_table .custom').toggle( unselect_fields( $('.wp_xprt_table .custom') ) );

		$('.disable').removeClass('disable');
		$('.internal_fields_toggle').prop( 'disabled', false );

		if ( ! $(this).is( ":checked" ) ) {
			$('.internal_fields_toggle').closest('span').addClass('disable');
			$('.internal_fields_toggle + span').addClass('disable');

			$('.internal_fields_toggle').prop( 'checked', false );
			$('.internal_fields_toggle').prop( 'disabled', true );
		}
	});

	$('.internal_fields_toggle').on( 'change', function() {

		if ( $(this).is(':checked') ) {
			$('.wp_xprt_table .internal').show();
		} else {
			$('.wp_xprt_table .internal').hide();
		}

		unselect_fields( $('.wp_xprt_table .internal') );
	});

	$('.taxonomy_fields_toggle').on( 'change', function() {
		$('.wp_xprt_table .taxonomy').toggle( unselect_fields( $('.wp_xprt_table .taxonomy') ) );
	});

	function unselect_fields( obj ) {

		if ( $(obj).is(':visible') ) {
			$( 'input.field', obj ).prop( 'checked', false );
		}

	}

	$(document).on( 'click', '.refresh-templates', function(e) {

		refresh_templates_list();

		e.preventDefault();

		return false;
	});

	// Fields bulk select.

	$(document).on( 'change', '.bulk_select', function() {

		$('input[type=checkbox].field, .bulk_select').prop( 'checked', $(this).prop('checked') );

		enable_feed_import();
	});

	$(document).on( 'change', 'input[type=checkbox].field', function() {

        if ( $('input[type=checkbox].field').length == $('input[type=checkbox].field:checked').length ) {
            $('.bulk_select').prop( 'checked', true );
		} else {
            $('.bulk_select').prop( 'checked', false );
		}

		enable_feed_import();
    });


	// Dynamic post type fields loading.

	$(document).on( 'change', '#wp_xprt_content_type', function() {

		if ( $(this).val().indexOf('empty') >= 0 ) {
			return false;
		}

		$('#templates_list').val('');

		$('.wp_xprt.processing').remove();

		$('.wp_xprt_table').before('<div class="wp_xprt processing"><div class="msg-refresh">' + wp_xprt_admin_l18n.msg_refreshing + ' <a class="button-secondary cancel-load">' + wp_xprt_admin_l18n.msg_cancel + '</a></div></div>');

		$('.wp_xprt_table').hide();

		var data = {
			action: 'wp_xprt_get_content_type_fields',
			content_type: $(this).val(),
			_ajax_nonce: wp_xprt_admin_l18n.ajax_nonce,
		};

		xhr = $.post( wp_xprt_admin_l18n.ajaxurl, data, function( response ) {

			if ( response && response !== undefined ) {

				if ( 0 === response ) {

					$('.cancel-load').trigger();

					$('.wp_xprt_table').before('<span class="wp_xprt error"> ' + wp_xprt_admin_l18n.fatal_error );

				} else {

					$('.wp_xprt_table').replaceWith( response ).slideDown();

					enable_feed_import();

					refresh_query_fields();

				}

			}

			init_table_reorder();

			$('.wp_xprt.processing').remove();
		});

	});

	// On cancel load feed.
	$(document).on( 'click', '.cancel-load', function(e) {

		xhr.abort();

		$('.wp_xprt.processing').html('');
 		$('.wp_xprt.processing').append( '<a class="button-secondary cancel-refresh">' + wp_xprt_admin_l18n.msg_refresh + '</a>' ).bind( 'click', function() {
 			$('#wp_xprt_content_type').trigger('change');
			$(this).remove();
 		});

		e.preventDefault();
		return false;
	});

	// Dynamic template loading.

	$(document).on( 'change', '#templates_list', function() {

		$('.wp_xprt.processing').remove();

		$('.wp_xprt_table').before('<div class="wp_xprt processing"><span class="msg-refresh">' + wp_xprt_admin_l18n.msg_refreshing + '</span></div>');

		$('.wp_xprt_table').hide();

		var data = {
			action:       'wp_xprt_load_template_content',
			template:     $(this).val(),
			content_type: $('#wp_xprt_content_type').val(),
			post_status:  $('#wp_xprt_post_status').val(),
			role:         $('#wp_xprt_user_role').val(),
			_ajax_nonce:  wp_xprt_admin_l18n.ajax_nonce,
		};

		$.post( wp_xprt_admin_l18n.ajaxurl, data, function( response ) {

			if ( response !== null && response !== undefined ) {
				$('.wp_xprt_table').replaceWith( response.table_output );
				$('#wp_xprt_content_type').val( response.content_type );
				$('#wp_xprt_post_status').val( response.post_status );
				$('#wp_xprt_user_role').val( response.role );

				refresh_query_fields();

				enable_feed_import();

				if ( $('#templates_list').val() ) {
					$('#template_name, #filename').val( $('#templates_list').val() );
				} else {
					$('#template_name').val( $('#def_template_name').val() );
					$('#filename').val( $('#def_filename').val() );
				}

				init_table_reorder();
			}

			$('.wp_xprt.processing').remove();

			$('.wp_xprt_table').slideDown();

		}, 'json' );

	});

	// Date picker.

	$('#from_date').datepicker({
		dateFormat: 'yy-mm-dd',
		changeMonth: true,
		onClose: function( selectedDate ) {
			$( "#to_date" ).datepicker( "option", "minDate", selectedDate );
			if ( ! $( "#to_date" ).val() ) {
				$( "#to_date" ).val( $( "#from_date" ).val() );
			}
		}
	});

	$('#to_date').datepicker({
		dateFormat: 'yy-mm-dd',
		changeMonth: true,
		onClose: function( selectedDate ) {
			$( "#from_date" ).datepicker( "option", "maxDate", selectedDate );
		}
	});

	$('.clear_span_dates').on( 'click', function() {
		$('.span_date').val('');
	});


	// Functions

	function refresh_query_fields() {

		if ( 'user' === $('#wp_xprt_content_type').val() ) {
			$('.wp_xprt_post_type').hide();
			$('.wp_xprt_user_type').show();
		} else {
			$('.wp_xprt_post_type').show();
			$('.wp_xprt_user_type').hide();
		}

	}

	/**
	 * Get existing templates list.
	 */
	function refresh_templates_list() {

		$('#templates_list option').filter( function() {
			return this.value;
		}).remove();

		$('.wp_xprt.processing').remove();

		$('#templates_list + .description').after('<span class="wp_xprt processing process-templates">&nbsp;</span>');

		var data = {
			action: 'wp_xprt_update_templates_list',
			_ajax_nonce: wp_xprt_admin_l18n.ajax_nonce,
		};

		$.post( wp_xprt_admin_l18n.ajaxurl, data, function( response ) {

			if ( response !== undefined ) {

				$.each( response.templates, function( key, value ) {

					if ( ! $('#templates_list[value='+ value + ']').length ) {
						$('#templates_list').append( $( '<option>', { value : value } ).text( value ) );
					}

				});

				$('.wp_xprt.processing').remove();

				$('#templates_list').trigger('change');
			}

		}, 'json' );

	}

	 // Helpers & init.

	function enable_feed_import() {
		$('.field_dependent').prop( 'disabled', ! $('.field:checked').length );

		$('.wp_xprt_table .custom').toggle( $('.custom_fields_toggle').prop('checked') );
		$('.wp_xprt_table .taxonomy').toggle( $('.taxonomy_fields_toggle').prop('checked') );
	}

	enable_feed_import();

	/**
	 * Reorder table rows.
	 */
	function init_table_reorder() {

		var fixHelperModified = function(e, tr) {
			var $originals = tr.children();
			var $helper = tr.clone();
			$helper.children().each(function(index) {
				$(this).width($originals.eq(index).width())
			});
			return $helper;
		},

		updateIndex = function(e, ui) {

			var fields = new Array();

			$( 'td.index', ui.item.parent() ).each( function ( i ) {

				var value = $( 'input.field', this ).val();

				if ( undefined != value ) {
					fields.push( value );
				}

			});

			if ( fields ) {
				$('#fields_order').val( fields );
			}

			$(".wp_xprt_table tr").removeClass('alternate');
			$(".wp_xprt_table tr:odd").addClass('alternate');

		};

		$(".wp_xprt_table tbody").sortable({
			helper: fixHelperModified,
			stop: updateIndex
		}).disableSelection();

		$('.internal_fields_toggle').trigger('change');
	}

	init_table_reorder();
});
