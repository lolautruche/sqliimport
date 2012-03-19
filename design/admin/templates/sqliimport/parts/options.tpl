{foreach $handlerOptions as $optionId => $optionDef sequence array( 'bgdark', 'bglight' ) as $css_class}
    <tr class="{$css_class}">
        <td><strong>{$optionDef.label|i18n( 'extension/sqliimport' )}</strong></th> 
        <td>{include uri=concat( "design:sqliimport/optionwidgets/", $optionDef.type, ".tpl" )
                     option_id=$option
                     handler=$handler
                     value=$optionDef.default
            }</td>
    </tr>
{/foreach}