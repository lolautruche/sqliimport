<?php
class SQLIImportJSServerFunctions
{
    public static function options( $args )
    {
        $handler = $args[0];
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
                $aHandlerOptions[$optionAlias] = array(
                    'label' => isset( $optionsLabels[ $optionAlias ] ) ? $optionsLabels[ $optionAlias ] : $optionAlias,
                    'type'  => isset( $optionsTypes[ $optionAlias ] ) ? $optionsTypes[ $optionAlias ] : 'string',
                    'default' => isset( $optionsDefaults[ $optionAlias ] ) ? $optionsDefaults[ $optionAlias ] : '',
                );
            }

            $tpl->setVariable( 'handler', $handler );
            $tpl->setVariable( 'handlerOptions', $aHandlerOptions );

            return $tpl->fetch( 'design:sqliimport/parts/options.tpl' );
        }

        return '';
    }
}