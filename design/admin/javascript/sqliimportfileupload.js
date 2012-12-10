/**
 * YUI3 module handling file options
 * Handles AJAX upload and sets uploaded file path to option value
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Benjamin Choquet <benjamin.choquet@heliopsis.net>
 * @version @@@VERSION@@@
 * @package sqliimport
 */
YUI.add( 'sqliimportfileupload', function( Y, name ){
	Y.SQLIImport._fileUploaders = [];
	Y.SQLIImport.FileUpload = function( node ){
		
		this.handler = node.getAttribute( 'data-handler' );
		this.option = node.getAttribute( 'data-option' );
		
		this.UPLOAD_URL = this.UPLOAD_URL.replace( '_handler_', this.handler )
										 .replace( '_option_', this.option );
		
		this.uploadVars[Y.SQLIImport.sessionData.sessionName] = Y.SQLIImport.sessionData.sessionId;

		this.field = node.one( '.sqliimport-option-fileupload-field' );
		this.filenameContainer = node.one( '.sqliimport-option-fileupload-filename' );
		this.progressBar = node.one( '.sqliimport-option-fileupload-progress' );
		this.progressMeter = node.one( '.sqliimport-option-fileupload-progress-meter' );
		
		var button = node.one( '.sqliimport-option-fileupload-button' ),
			container = node.one( '.sqliimport-option-fileupload-button-container' );
		
		this.progressBar.hide();
		
		if( container && button && this.field ){
			
			this.uploader  = new Y.Uploader({
				selectButtonLabel: button.getAttribute( 'value' )
			});
			
			if( Y.Uploader.TYPE === "flash" ){
				this.uploader.set("fileFilters", this.getFileFilters( node.getAttribute( 'data-allowed-file-types' ) ) );
				this.uploader.set("swfURL", node.getAttribute( 'data-swf-url' ) );
			}
			
			if (Y.Uploader.TYPE != "none") {
				this.uploader.on( 'fileselect', this.uploadSelectedFile, this );
				this.uploader.on( 'uploadprogress', this.uploadProgress, this );
				this.uploader.on( 'uploadcomplete', this.uploadCompleteData, this );
				
				
				button.remove();
				this.uploader.render( '.sqliimport-option-fileupload-button-container' );
			}
		}
	}
	
	Y.SQLIImport.FileUpload.prototype = {
		UPLOAD_URL: '/ezjscore/call/sqliimport::fileupload::_handler_::_option_?ContentType=json',
		
		uploadVars: {
			ezxform_token: '@$ezxFormToken@'
		},

		handler: '',
		option: '',
		field: null,
		filenameContainer: null,
		
		uploader: null,
		
		getFileFilters: function( allowedTypesString ){
			var fileFilters = [],
				typeStrings = allowedTypesString.split( '|' ),
				typesNum = typeStrings.length,
				typeInfo;

			for( var i = 0; i < typesNum; i++ ){
				typeInfo = typeStrings[i].split(':');
				fileFilters.push({
					description: typeInfo[0],
					extensions: typeInfo[1]
				});
			}

			return fileFilters;

		},

		uploadSelectedFile: function( event ){
			if( event.fileList.length > 0 ){
				this.uploader.upload( event.fileList[0], this.UPLOAD_URL, 'POST', this.uploadVars );
				this.progressBar.show();
			}
		},
		
		uploadProgress: function( event ){
			var percent = Math.round( event.bytesLoaded / event.bytesTotal * 100 );
			this.progressMeter.setStyle( 'width', percent + '%' );
		},
		
		uploadCompleteData: function( event ){
			var data;

			try{
				data = Y.JSON.parse( event.data );
			}
			catch( e ){
				data = {
					error_text: e.message,
					content: ""
				};
			}

			if( data.error_text ){
				this.field.set( 'value', "" );
				this.filenameContainer.setContent( "" );
				window.alert( data.error_text );
			} else {
				this.field.set( 'value', data.content );
				this.filenameContainer.setContent( data.content );
			}
			this.progressBar.hide();
		}
		
	};
	
	
	Y.SQLIImport.registerOptionModule( name, function( node ){
		Y.SQLIImport._fileUploaders.push( new Y.SQLIImport.FileUpload( node ) );
	});
	
	
}, '0.0.1', {
	requires: [ 'sqliimport', 'uploader', 'json-parse' ]
});