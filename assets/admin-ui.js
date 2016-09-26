/* global securePostWithLink = [
	 'enable_ui' => $enable_ui,
	 'current_post_status' => $post->post_status ]
 */
( function( $, data ){

	var AdminUi = {

		/**
		 * Holds added elements
		 */
		new_input: undefined,
		new_label: undefined,

		/**
		 * Constructor
		 */
		init: function () {

			//Bootstrap
			this.addVisibilitySettings();
			if( data.current_post_status === 'secured' ) {
				this.modifyUiOnSecuredPost();
			}

			//Events
			$( '.save-post-visibility' ).on( 'click', $.proxy( this.okButtonHandler, this ) );

		},

		/**
		 * Add new checkbox in publish metabox
		 */
		addVisibilitySettings: function () {

			var private_label = $( '#post-visibility-select label[for="visibility-radio-private"]' );

			var new_input = $( "<input/>")
				.attr( 'name', 'visibility' )
				.attr( 'id', 'visibility-radio-secured' )
				.attr( 'value', 'secured' )
				.attr( 'type', 'radio' );

			var new_label = $( "<label/>" )
				.attr( 'for', 'visibility-radio-secured' )
				.attr( 'class', 'selectit' )
				.text( ' ' + data.lang_secured_link );

			private_label.after( new_label ).after( new_input ).after( $( "<br/>" ) );

			this.new_input = new_input[0];
			this.new_label = new_label[0];

		},

		/**
		 * Change text of visibility, when secured link is selected
		 */
		okButtonHandler: function() {

			if( this.new_input.checked ) {

				$( '#post-visibility-display' ).text( data.lang_secured_link );

			}

		},

		/*
		 * If current post is secured, modify visibility and status inputs acordingly
		 */
		modifyUiOnSecuredPost: function() {
			$( this.new_input).prop( 'checked', true );
			$( '#post-visibility-display' ).text( data.lang_secured_link );

			var new_option = $( '<option/>' )
				.attr( 'value', 'secured' )
				.prop( 'selected', true )
				.text( data.lang_secured_link );

			$( '#post_status' ).val( 'secured' ).append( new_option );
			$( '.save-post-status' ).trigger( 'click' );
		}

	};

	/*
	Init on DOM Ready
	Only if we want UI on current screen
	 */
	if( data.enable_ui ){
		$( function() {
			Object.create( AdminUi ).init();
		} );
	}


})( jQuery, securePostWithLink );