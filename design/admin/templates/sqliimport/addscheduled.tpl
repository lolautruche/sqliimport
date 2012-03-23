{*<script language="javascript" src={'javascript/calendar.js'|ezdesign}></script>*}
{if is_set( $error_message )}
<div class="message-error">
    <h2>{'Input did not validate'|i18n( 'design/admin/settings' )}</h2>
    <p>{$error_message}</p>
</div>
{/if}

{ezscript_require(array( 'ezjsc::yui3', 'ezjsc::yui3io', 'sqliimport::modules', 'sqliimportoptions.js' ) )}
<form action={concat( '/sqliimport/addscheduled/', $import_id )|ezurl}
      method="post"
      data-scheduled-import-id="{$import_id}"
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
                            <h1 class="context-title">{'Schedule an import'|i18n( 'extension/sqliimport' )}</h1>
                            {* DESIGN: Mainline *}<div class="header-mainline"></div>
                            {* DESIGN: Header END *}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {* DESIGN: Content START *}
    <div class="box-ml"><div class="box-mr"><div class="box-content">
        <div class="block">
            <h4>{'Import label'|i18n( 'extension/sqliimport' )}</h4>
            <p>
                <input type="text" name="ScheduledLabel" value="{$import_label}" />
            </p>
            <p>&nbsp;</p>

			<h4>{'Import handler'|i18n( 'extension/sqliimport' )}</h4>
			<p>
				<select name="ImportHandler" id="ImportHandler">
				    <option value="">{'-- Select handler --'|i18n( 'extension/sqliimport' )}</option>
                {foreach $importHandlers as $handlerName => $handler}
                    <option value="{$handler}"{if $handler|eq( $current_import_handler )} selected="selected"{/if}>{$handlerName}</option>

                {/foreach}
                </select>
            </p>
            <p>&nbsp;</p>

            <table class="list cache block" cellspacing="0" style="margin:0;" id="handlerOptions">
                <tr style="display:none;"><td>
	            <h4>{'Options (facultative)'|i18n( 'extension/sqliimport' )}</h4>
	            <div>
	                <textarea name="ImportOptions" id="ImportOptions" rows="7" cols="70">{$import_options}</textarea>
	                <p><i>{'One option per line : optionName=optionValue'|i18n( 'extension/sqliimport' )}</i></p>
	            </div>
	            <p>&nbsp;</p>
	            </td></tr>
            </table>

			<h4>{'Choose a start date (YYYY-mm-dd)'|i18n( 'extension/sqliimport' )}</h4>
			<p>
			    <input type="text" name="ScheduledDate" value="{if is_set( $import_date )}{$import_date}{else}YYYY-mm-dd{/if}" id="ScheduledDate" />

			    {'Hour'|i18n( 'extension/sqliimport' )}
			    <select name="ScheduledHour">
		        {for 0 to 23 as $hour}
		            <option value="{$hour}"{if and( is_set( $import_hour ), eq( $import_hour, $hour ) )} selected="selected"{/if}>{$hour}</option>

		        {/for}
			    </select>

			    {def $quarters = array( 0, 15, 30, 45 )}
			    {'Minutes'|i18n( 'extension/sqliimport' )}
			    <select name="ScheduledMinute">
		        {foreach $quarters as $quarter}
		            <option value="{$quarter}"{if and( is_set( $import_minute ), eq( $import_minute, $quarter ) )} selected="selected"{/if}>{$quarter}</option>

		        {/foreach}
			    </select>
			</p>
			<p>&nbsp;</p>

			<h4>{'Frequency'|i18n( 'extension/sqliimport/schedulefrequency' )}</h4>
			<p>
			    <input type="radio" name="ScheduledFrequency" value="none"    {if or( is_unset( $import_frequency ), $import_frequency|eq( 'none' ) )} checked="checked"{/if}/>{'None'|i18n( 'extension/sqliimport/schedulefrequency' )}
			    <input type="radio" name="ScheduledFrequency" value="hourly"  {if and( is_set( $import_frequency ), $import_frequency|eq( 'hourly' ) )} checked="checked"{/if}/>{'Hourly'|i18n( 'extension/sqliimport/schedulefrequency' )}
			    <input type="radio" name="ScheduledFrequency" value="daily"   {if and( is_set( $import_frequency ), $import_frequency|eq( 'daily' ) )} checked="checked"{/if}/>{'Daily'|i18n( 'extension/sqliimport/schedulefrequency' )}
			    <input type="radio" name="ScheduledFrequency" value="weekly"  {if and( is_set( $import_frequency ), $import_frequency|eq( 'weekly' ) )} checked="checked"{/if}/>{'Weekly'|i18n( 'extension/sqliimport/schedulefrequency' )}
			    <input type="radio" name="ScheduledFrequency" value="monthly" {if and( is_set( $import_frequency ), $import_frequency|eq( 'monthly' ) )} checked="checked"{/if}/>{'Monthly'|i18n( 'extension/sqliimport/schedulefrequency' )}

			    {* Manual frequency *}
			    <br />
			    <input type="radio" name="ScheduledFrequency" value="manual"  {if and( is_set( $import_frequency ), $import_frequency|eq( 'manual' ) )} checked="checked"{/if} onclick="document.getElementById('ManualScheduledFrequency').removeAttribute('disabled')" />{'Every'|i18n( 'extension/sqliimport/schedulefrequency' )} :
			    <input type="text" id="ManualScheduledFrequency" name="ManualScheduledFrequency" size="5" value="{cond( is_set( $manual_frequency ), $manual_frequency, 0 )}" {if or( is_unset( $import_frequency ), $import_frequency|ne( 'manual' ) )} disabled="disabled"{/if} /> {'minutes (not less than 5min)'|i18n( 'extension/sqliimport/schedulefrequency' )}
			</p>
			<p>&nbsp;</p>

			<h4>{'Activate import'|i18n( 'extension/sqliimport' )}</h4>
			<p>
			 <input type="checkbox" name="ScheduledActive"{if $import_is_active} checked="checked"{/if} />
			</p>
            {* DESIGN: Content END *}
        </div>
    </div></div></div>

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
                                    <input class="button" type="submit" name="RequestScheduledImportButton" value="{'Add a scheduled import'|i18n( 'extension/sqliimport' )}" />
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

<!-- JSCalendar settings -->
<style type="text/css">
    @import url({'stylesheets/jscal2.css'|ezdesign});
    @import url({'stylesheets/border-radius.css'|ezdesign});
    @import url({'stylesheets/steel/steel.css'|ezdesign});
</style>
<script type="text/javascript" src={'javascript/jscal2.js'|ezdesign}></script>
<script type="text/javascript" src={concat( 'javascript/lang/en.js'|i18n( 'extension/sqliimport' ) )|ezdesign}></script>
<script type="text/javascript">
    Calendar.setup({ldelim}

        trigger : 'ScheduledDate',
        inputField : 'ScheduledDate',
        onSelect : function() {ldelim} this.hide(); {rdelim},

    {rdelim});
</script>