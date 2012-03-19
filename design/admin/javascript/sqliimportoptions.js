YUI( YUI3_config ).use( 'node', 'io-ez', function( Y ){
	
	var handlerSelect = Y.one('#ImportHandler'),
		handlerOptions = Y.one('#handlerOptions'),
		fallbackOptions = handlerOptions.all( 'tr' ).remove( false ),
		fallbackToTextarea = Y.one( 'form[data-fallback-to-textarea]' ).get( 'data-fallback-to-textarea' ) === 'true';
	
	
	/**
	 * Listen to handler selectbox changes
	 */
	handlerSelect.on( 'change', function(){
		var handler = handlerSelect.get( 'value' );
		if( handler ){
			Y.io.ez( 'sqliimport::options::' + handler, {
				on: {
					success: onOptionsLoaded
				}
			});
			
		}else{
			handlerOptions.setContent( '' );
		}
	});
	
	/**
	 * Replace options with AJAX result 
	 */
	function onOptionsLoaded( id, response ){
		if( response.responseJSON.error_text ){
			window.alert( response.responseJSON.error_text );
		} else if( response.responseJSON.content ) {
			handlerOptions.setContent( response.responseJSON.content );
		} else if( fallbackToTextarea ) {
			//no option : fall back to textarea
			handlerOptions.setContent( fallbackOptions );
		}
	}
});