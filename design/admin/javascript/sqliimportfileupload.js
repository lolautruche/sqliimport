YUI.add( 'sqliimportfileupload', function( Y, name ){
	Y.SQLIImport._fileUploaders = [];
	Y.SQLIImport.FileUpload = function( node ){
		
		this.handler = node.getAttribute( 'data-handler' );
		this.option = node.getAttribute( 'data-option' );
		
		this.UPLOAD_URL = this.UPLOAD_URL.replace( '_handler_', this.handler )
										 .replace( '_option_', this.option );
		
		this.uploadVars[Y.SQLIImport.sessionData.sessionName] = Y.SQLIImport.sessionData.sessionId;
		this.uploadVars.UserSessionHash = Y.SQLIImport.sessionData.userSessionHash;

		this.field = node.one( '.sqliimport-option-fileupload-field' );
		this.progressBar = node.one( '.sqliimport-option-fileupload-progress' );
		this.progressMeter = node.one( '.sqliimport-option-fileupload-progress-meter' );
		
		var button = node.one( '.sqliimport-option-fileupload-button' ),
			overlay = node.one( '.sqliimport-option-fileupload-overlay' );
		
		this.progressBar.hide();
		
		if( overlay && button && this.field ){
			
			overlay.setStyles({
				position: 'absolute',
				zIndex: 2,
				width: button.getStyle( 'width' ),
				height: button.getStyle( 'height' ),
				paddingTop: button.getStyle( 'paddingTop' ),
				paddingBottom: button.getStyle( 'paddingBottom' ),
				paddingLeft: button.getStyle( 'paddingLeft' ),
				paddingRight: button.getStyle( 'paddingRight' ),
			});
			
			this.uploader  = new Y.Uploader({
				boundingBox: overlay,
				swfURL: node.getAttribute( 'data-swf-url' )
			});
			
			this.uploader.on( "uploaderReady", this.setupUploader, this );
			
		}
	}
	
	Y.SQLIImport.FileUpload.prototype = {
		UPLOAD_URL: '/ezjscore/call/sqliimport::fileupload::_handler_::_option_?ContentType=json',
		
		uploadVars: {
			UserSessionHash: '',
			ezxform_token: '@$ezxFormToken@'
		},

		handler: '',
		option: '',
		field: null,
		
		uploader: null,
		
		setupUploader: function(){
			this.uploader.on( 'fileselect', this.uploadSelectedFile, this );
			this.uploader.on( 'uploadprogress', this.uploadProgess, this );
			this.uploader.on( 'uploadcompletedata', this.uploadCompleteData, this );
			
		},
		
		uploadSelectedFile: function( event ){
			this.uploader.upload( "file0", this.UPLOAD_URL, 'POST', this.uploadVars );
			this.progressBar.show();
		},
		
		uploadProgess: function( event ){
			var percent = Math.round( event.bytesLoaded / event.bytesTotal * 100 );
			this.progressMeter.setStyle( 'width', percent + '%' );
		},
		
		uploadCompleteData: function( event ){
			console.log( event.data );
			this.field.set( 'value', event.data );
			this.progressBar.hide();
		}
		
	};
	
	
	Y.SQLIImport.registerOptionModule( name, function( node ){
		Y.SQLIImport._fileUploaders.push( new Y.SQLIImport.FileUpload( node ) );
	});
	
	
}, '0.0.1', {
	requires: [ 'sqliimport', 'uploader' ]
});