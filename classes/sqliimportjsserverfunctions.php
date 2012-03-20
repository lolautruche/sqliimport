<?php
class SQLIImportJSServerFunctions
{
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
     * @param unknown_type $args
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
}