<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">{'Scheduled import list'|i18n( 'extension/sqliimport' )}</h1>
                            <div class="header-mainline"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="box-ml">
    <div class="box-mr">
        <div class="box-content">
            <div class="context-toolbar">
				<div class="block">
				    <div class="left">
			        <p>
                    {switch match=$limit}
                        {case match=25}
                            <a href={'/user/preferences/set/sqliimport_import_limit/10/'|ezurl} title="{'Show 10 items per page.'|i18n( 'design/admin/node/view/full' )}">10</a>
                            <span class="current">25</span>
                            <a href={'/user/preferences/set/sqliimport_import_limit/50/'|ezurl} title="{'Show 50 items per page.'|i18n( 'design/admin/node/view/full' )}">50</a>
                        {/case}
                    
                        {case match=50}
                            <a href={'/user/preferences/set/sqliimport_import_limit/10/'|ezurl} title="{'Show 10 items per page.'|i18n( 'design/admin/node/view/full' )}">10</a>
                            <a href={'/user/preferences/set/sqliimport_import_limit/25/'|ezurl} title="{'Show 25 items per page.'|i18n( 'design/admin/node/view/full' )}">25</a>
                            <span class="current">50</span>
                        {/case}
                    
                        {case}
                            <span class="current">10</span>
                            <a href={'/user/preferences/set/sqliimport_import_limit/25/'|ezurl} title="{'Show 25 items per page.'|i18n( 'design/admin/node/view/full' )}">25</a>
                            <a href={'/user/preferences/set/sqliimport_import_limit/50/'|ezurl} title="{'Show 50 items per page.'|i18n( 'design/admin/node/view/full' )}">50</a>
                        {/case}
                    
                    {/switch}
                    </p>
				    </div>
			    </div>
		    </div>
            <div class="block">
                <p><a href={'/sqliimport/addscheduled'|ezurl}>{'Add a scheduled import'|i18n( 'extension/sqliimport' )}</a></p>
            {if not( $imports|count )}
                <p><strong>{"No scheduled imports"|i18n( 'extension/sqliimport' )}</strong></p>
            {else}
                <table class="list" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{"Label"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Import type"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Params"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"User"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Frequency"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Next import"|i18n( 'extension/sqliimport' )}</th>
                            <th class="tight">{"Active"|i18n( 'extension/sqliimport' )}</th>
                            <th class="tight">{'Edit'|i18n( 'extension/sqliimport' )}</th>
                            <th class="tight">&nbsp;</th>
                        </tr>
                    </thead>

                    <tbody>
                        {foreach $imports as $import sequence array( 'bglight', 'bgdark' ) as $trClass}
                        <tr class="{$trClass}">
                            <td>{$import.label}</td>
                            <td>{$import.handler_name}</td>
                            <td>{$import.options|nl2br}</td>
                            <td>{$import.user.login}</td>
                            <td>{$import.frequency}</td>
                            <td>{$import.next|l10n( 'shortdatetime' )}</td>
                            <td><input type="checkbox" disabled="disabled"{if $import.is_active} checked="checked"{/if}/></td>
                            <td>
                            {if $import.user_has_access}
                                
                                <a href={concat( '/sqliimport/addscheduled/', $import.id )|ezurl} title="{'Edit import'|i18n( 'extension/sqliimport' )}"><img src={'edit.gif'|ezimage} alt="{'Edit'|i18n( 'extension/sqliimport')}" /></a>
                            {else}
                            
                                <img src={'edit-disabled.gif'|ezimage} alt="{'Edit'|i18n( 'extension/sqliimport')}" />
                            {/if}
                            </td>
                            <td>
                            {if $import.user_has_access}
                                <a href={concat( '/sqliimport/removescheduled/', $import.id )|ezurl} title="{'Remove scheduled import'|i18n( 'extension/sqliimport' )}"
                                   onclick="return confirm('{'Are you sure you want to remove this scheduled import ?'|i18n( 'extension/sqliimport' )}')"><img src={'trash-icon-16x16.gif'|ezimage} alt="{'Edit'|i18n( 'extension/sqliimport')}" /></a>
                            {/if}
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                <p>&nbsp;</p>
            {/if}
            </div>
            <div class="context-toolbar">
			{include name=navigator uri='design:navigator/google.tpl'
			                        page_uri=$uri
			                        item_count=$import_count
			                        view_parameters=$view_parameters
			                        item_limit=$limit}
            </div>
        </div>
    </div>
</div>

<div class="controlbar"><div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br"></div></div></div></div></div></div></div>