<?php
class SQLIImportJSServerFunctions extends ezjscServerFunctions
{
    /**
     * Returns options form and js modules list for handler
     * @param array $args First value: handler id, optional second value: scheduled import id
     * @throws SQLIImportRuntimeException
     * @return array
     */
    public static function options( $args )
    {
        $scheduledImport = null;
        $currentOptions = null;

        $handler = $args[0];

            //arg 2 may be a scheduled import id
        if( count( $args ) > 1 ){
            $scheduledImport = SQLIScheduledImport::fetch( $args[1] );

            if( !$scheduledImport ){
                throw new SQLIImportRuntimeException( 'Invalid scheduled import ID ' . $args[1] );
            }

            $currentOptions = $scheduledImport->attribute( 'options' );
        }

        $tpl = SQLIImportUtils::templateInit();
        $importINI = eZINI::instance( 'sqliimport.ini' );
        $handlerSection = $handler.'-HandlerSettings';

        if( !$importINI->hasSection( $handlerSection ) )
        {
            throw new SQLIImportRuntimeException( 'No config for handler ' . $handler );
        }


        if( $importINI->hasVariable( $handlerSection, 'Options' ) )
        {
            $aHandlerOptions = array();

            $optionsList = $importINI->variable( $handlerSection, 'Options' );
            $optionsLabels = $importINI->variable( $handlerSection, 'OptionsLabels' );
            $optionsTypes = $importINI->variable( $handlerSection, 'OptionsTypes' );
            $optionsDefaults = $importINI->variable( $handlerSection, 'OptionsDefaults' );

            foreach( $optionsList as $optionAlias )
            {
                $value = '';
                if( $currentOptions && isset( $currentOptions->{$optionAlias} ) )
                {
                    $value = $currentOptions->{$optionAlias};
                }
                elseif( isset( $optionsDefaults[ $optionAlias ] ) )
                {
                    $value = $optionsDefaults[ $optionAlias ];
                }

                $aHandlerOptions[$optionAlias] = array(
                    'label' => isset( $optionsLabels[ $optionAlias ] ) ? $optionsLabels[ $optionAlias ] : $optionAlias,
                    'type'  => isset( $optionsTypes[ $optionAlias ] ) ? $optionsTypes[ $optionAlias ] : 'string',
                    'value' => $value,
                );
            }

            $tpl->setVariable( 'handler', $handler );
            $tpl->setVariable( 'handlerOptions', $aHandlerOptions );
            $tpl->setVariable( 'jsModules', array() );

            return array(
                'form' => $tpl->fetch( 'design:sqliimport/parts/options.tpl' ),
                'modules' => $tpl->variable( 'jsModules' ),
            );
        }

        return '';
    }

    /**
     * Returns YUI3 configuration extension for custom option modules
     * @param array $args Not used
     * @return string JS code declaring YUI3 modules
     */
    public static function modules( $args )
    {
        $importINI = eZINI::instance( 'sqliimport.ini' );
        $modules = $importINI->variable( 'OptionsGUISettings', 'YUI3Modules' );

        if( count( $modules ) )
        {
            $js = 'YUI3_config = YUI3_config || { modules: {}}'.chr(10);
            foreach( $modules as $name => $path )
            {
                eZURI::transformURI( $path, false, 'full' );
                $js .= 'YUI3_config.modules.' . $name . ' = { fullpath: "' . $path . '" };'.chr(10);
            }

            return $js;
        }

        return '';
    }

    /**
     * Saves a file for import option
     * @param array $args First value: handler id, second value: option id
     * @return string saved file name
     * @throws SQLIImportRuntimeException
     */
    public static function fileupload( $args )
    {
        if( count( $args ) !== 2 )
        {
            throw new SQLIImportRuntimeException( 'Invalid arguments' );
        }

        $handler = $args[0];
        $option = $args[1];

        $importINI = eZINI::instance( 'sqliimport.ini' );
        $handlerSection = $handler.'-HandlerSettings';
        if( !$importINI->hasSection( $handlerSection ) )
        {
            throw new SQLIImportRuntimeException( 'No config for handler ' . $handler );
        }

        $options = $importINI->variable( $handlerSection, 'Options' );
        if( !in_array( $option, $options ) )
        {
            throw new SQLIImportRuntimeException( 'Invalid option ' . $option );
        }

        $returnCode = eZHTTPFile::canFetch( 'Filedata', 0 );
        if( $returnCode !== eZHTTPFile::UPLOADEDFILE_OK && $returnCode !== true )
        {
            throw new SQLIImportRuntimeException( 'Invalid uploaded file' );
        }

        $file = eZHTTPFile::fetch( 'Filedata' );
        if( !$file instanceof eZHTTPFile )
        {
            throw new SQLIImportRuntimeException( 'Invalid uploaded file' );
        }

        $dir = $importINI->variable( 'OptionsGUISettings', 'UploadedFilesDir' ) . DIRECTORY_SEPARATOR .
                    $handler . DIRECTORY_SEPARATOR .
                    $option;

        $file->store( $dir );

        return $file->attribute( 'filename' );
    }
}