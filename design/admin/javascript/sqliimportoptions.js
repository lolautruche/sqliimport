YUI( YUI3_config ).use( 'node', 'io-ez', function( Y ){
	Y.on("domready", function(){
		var handlerSelect = Y.one('#ImportHandler'),
			handlerOptions = Y.one('#handlerOptions'),
			fallbackOptions = handlerOptions.all( 'tr' ).remove( false ),
			form = Y.one( 'form[data-fallback-to-textarea]' ),
			fallbackToTextarea = form.getAttribute( 'data-fallback-to-textarea' ) === 'true',
			scheduledImportID = form.getAttribute( 'data-scheduled-import-id' );


		handlerSelect.on( 'change', getHandlerOptionsForm );

		getHandlerOptionsForm();

		/**
		 * Listen to handler selectbox changes
		 */
		function getHandlerOptionsForm(){
			var handler = handlerSelect.get( 'value' );
			if( handler ){

				var url = 'sqliimport::options::' + handler;
				if( scheduledImportID ){
					url += '::' + scheduledImportID;
				}

				Y.io.ez( url, {
					on: {
						success: onOptionsLoaded
					}
				});

			}else{
				handlerOptions.setContent( '' );
			}
		}

		/**
		 * Replace options with AJAX result 
		 */
		function onOptionsLoaded( id, response ){
			if( response.responseJSON.error_text ){
					//alert if server error
				window.alert( response.responseJSON.error_text );

			} else if( response.responseJSON.content ) {
					//show HTML form
				handlerOptions.setContent( response.responseJSON.content );

			} else if( fallbackToTextarea ) {
					//no option : fall back to textarea
				handlerOptions.setContent( fallbackOptions );

			}else{
					//no option
				handlerOptions.setContent( '' );
			}
		}
	});
});