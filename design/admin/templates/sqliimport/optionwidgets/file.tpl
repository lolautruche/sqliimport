{def $allowedFileTypes = ezini( concat( $handler, '-HandlerSettings' ), 'FileOptionsAllowedFileTypes', 'sqliimport.ini' )}
<div data-module="sqliimportfileupload" 
     data-handler="{$handler}" 
     data-option="{$option_id}"
     data-swf-url={concat( ezini('eZJSCore', 'LocalScriptBasePath', 'ezjscore.ini').yui3, 'uploader/assets/uploader.swf' )|ezdesign( 'no' )}
     data-allowed-file-types="{$allowedFileTypes[$option_id]}"
     >
    <div class="sqliimport-option-fileupload-progress" style="width: 300px; height: 10px; border: 1px gray solid;">
        <div class="sqliimport-option-fileupload-progress-meter" style="background-color: red; height: 10px; width: 0%;"></div>
    </div>
    <input type="hidden" class="sqliimport-option-fileupload-field" name="ImportOption_{$option_id}" value="" />
    <span class="sqliimport-option-fileupload-overlay"></span>
    <input type="button" class="sqliimport-option-fileupload-button" value="{"Select file"|i18n( 'extension/sqliimport' )}" />
    <p class="sqliimport-option-fileupload-filename"></p>
</div>
{set $jsModules = $jsModules|merge( array( 'uploader', 'sqliimportfileupload' ) )}