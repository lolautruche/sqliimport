/**
 * Main YUI3 module for options GUI
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Benjamin Choquet <benjamin.choquet@heliopsis.net>
 * @version @@@VERSION@@@
 * @package sqliimport
 */
YUI( YUI3_config ).add( 'sqliimport', function(Y, name){
	Y.SQLIImport = (function(){
		
		var modules = {};
		
		return {
			registerOptionModule: function( name, initFunc ){
				modules[name] = initFunc;
			},
			
			initOptionModule: function( name, node ){
				if( modules[name] ){
					modules[name].call( null, node );
				}
			},

			sessionData: {
				sessionName: '',
				sessionId: '',
				userSessionHash: ''
			}
		};
		
	})();
});

YUI( YUI3_config ).use( 'sqliimport', 'node', 'loader', 'io-ez', function( Y ){
	
	
	
	Y.on("domready", function(){
		var handlerSelect = Y.one('#ImportHandler'),
			handlerOptions = Y.one('#handlerOptions'),
			fallbackOptions = handlerOptions.all( 'tr' ).remove( false ).show(),
			form = Y.one( 'form[data-fallback-to-textarea]' ),
			fallbackToTextarea = form.getAttribute( 'data-fallback-to-textarea' ) === 'true',
			scheduledImportID = form.getAttribute( 'data-scheduled-import-id' );

		Y.SQLIImport.sessionData.sessionName = form.getAttribute( 'data-session-name' );
		Y.SQLIImport.sessionData.sessionId = form.getAttribute( 'data-session-id' );
		Y.SQLIImport.sessionData.userSessionHash = form.getAttribute( 'data-user-session-hash' );

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
						success: onOptionsFormLoaded
					}
				});

			}else{
				handlerOptions.setContent( '' );
			}
		}

		/**
		 * Callback for options form loading
		 * Loads required JS modules  
		 */
		function onOptionsFormLoaded( id, response ){
			
			if( response.responseJSON.error_text ){
					//alert if server error
				window.alert( response.responseJSON.error_text );
				return;
			}
			
			if( response.responseJSON.content.modules ){
				Y.use( response.responseJSON.content.modules, function(){
					showOptionsForm( response.responseJSON.content.form );
				} );
			}
			else showOptionsForm( response.responseJSON.content.form );
		}
		
		
		/**
		 * Replace options with AJAX result 
		 */
		function showOptionsForm( form ){
			if( form ) {
					//show HTML form
				handlerOptions.setContent( form );
					//init modules
				handlerOptions.all( 'div[data-module]' ).each( function( node ){
					Y.SQLIImport.initOptionModule( node.getAttribute( 'data-module' ), node );
				});
				

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