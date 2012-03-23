{if is_set( $error_message )}
<div class="message-error">
    <h2>{'Input did not validate'|i18n( 'design/admin/settings' )}</h2>
    <p>{$error_message}</p>
</div>
{/if}
{ezscript_require(array( 'ezjsc::yui3', 'ezjsc::yui3io', 'sqliimport::modules', 'sqliimportoptions.js' ) )}
<form action={'/sqliimport/addimport'|ezurl}
      method="post"
      data-fallback-to-textarea="{cond( ezini( 'OptionsGUISettings', 'FallbackToTextarea', 'sqliimport.ini' )|eq('enabled'), 'true', 'false' )}"
      data-session-name="{$session_name}"
      data-session-id="{$session_id}"
      data-user-session-hash="{$user_session_hash}"
>
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">{'Request a new immediate import'|i18n( 'extension/sqliimport' )}</h1>
                            {* DESIGN: Mainline *}<div class="header-mainline"></div>
                            {* DESIGN: Header END *}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* DESIGN: Content START *}
    <div class="box-ml">
        <div class="box-mr">
            <div class="box-content">
                <table class="list cache block" cellspacing="0" style="margin:0;">
                    <tr class="bglight">
                        <th>{'Import handler'|i18n( 'extension/sqliimport' )}</th>
                        <td>
                            <select name="ImportHandler" id="ImportHandler">
                               <option value="">{'-- Select handler --'|i18n( 'extension/sqliimport' )}</option>
                            {foreach $importHandlers as $handlerName => $handler}
                               <option value="{$handler}">{$handlerName}</option>
                            {/foreach}
                            </select>
                        </td>
                    </tr>
                    <tbody id="handlerOptions">
	                    <tr class="bgdark" style="display:none;">
	                        <td><strong>{'Options (facultative)'|i18n( 'extension/sqliimport' )}</strong></th>
	                        <td>
	                            <textarea name="ImportOptions" id="ImportOptions" rows="7" cols="70"></textarea>
	                            <p><i>{'One option per line : optionName=optionValue'|i18n( 'extension/sqliimport' )}</i></p>
	                        </td>
	                    </tr>
	                 </tbody>
                </table>
                {* DESIGN: Content END *}
            </div>
        </div>
    </div>

    {* Buttons. *}
    <div class="controlbar">
    {* DESIGN: Control bar START *}
        <div class="box-bc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tc">
                        <div class="box-bl">
                            <div class="box-br">
                                <div class="block">
                                    <input class="button" type="submit" name="RequestImportButton" value="{'Request a new immediate import'|i18n( 'extension/sqliimport' )}" />
                                </div>
                            {* DESIGN: Control bar END *}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
