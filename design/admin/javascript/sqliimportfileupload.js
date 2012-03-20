YUI.add( 'sqliimportfileupload', function( Y, name ){
	Y.SQLIImport._fileUploaders = [];
	Y.SQLIImport.FileUpload = function( node ){
		
		this.handler = node.getAttribute( 'data-handler' );
		this.option = node.getAttribute( 'data-option' );
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
				boundingBox: overlay
			});
			
			this.uploader.on( "uploaderReady", this.setupUploader, this );
			
		}
	}
	
	Y.SQLIImport.FileUpload.prototype = {
		handler: '',
		option: '',
		field: null,
		
		uploader: null,
		
		setupUploader: function(){
			this.uploader.on( 'fileselect', this.uploadSelectedFile, this );
		},
		
		uploadSelectedFile: function( event ){
			console.log( event );
			//this.uploader.upload()
		}
		
	};
	
	
	Y.SQLIImport.registerOptionModule( name, function( node ){
		Y.SQLIImport._fileUploaders.push( new Y.SQLIImport.FileUpload( node ) );
	});
	
	
}, '0.0.1', {
	requires: [ 'sqliimport', 'uploader' ]
});