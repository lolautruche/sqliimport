<div class="context-block">
    <div class="box-header">
        <div class="box-tc">
            <div class="box-ml">
                <div class="box-mr">
                    <div class="box-tl">
                        <div class="box-tr">
                            <h1 class="context-title">{'Import list'|i18n( 'extension/sqliimport' )}</h1>
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
            {if not( $imports|count )}
                {"No imports"|i18n( 'extension/sqliimport' )}
            {else}
                <table class="list" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{"Import type"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Params"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"User"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Requested on"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Type"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Status"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Progress"|i18n( 'extension/sqliimport' )}</th>
                            <th>{"Progression notes"|i18n( 'extension/sqliimport' )}</th>
                        </tr>
                    </thead>

                    <tbody>
                        {foreach $imports as $import sequence array( 'bglight', 'bgdark' ) as $trClass}
                        <tr class="{$trClass}">
                            <td>{$import.handler_name}</td>
                            <td>{$import.options|nl2br}</td>
                            <td>{$import.user.login}</td>
                            <td>{$import.requested_time|l10n( 'shortdatetime' )}</td>
                            <td>{$import.type_string|i18n( 'extension/sqliimport/type' )}</td>
                            <td>
                                {$import.status_string|i18n( 'extension/sqliimport/status' )}
                            {if $import.user_has_access}
                                {switch match=$import.status}
                                    {case match=0}{* Pending *}
                                        (<a href={concat( '/sqliimport/alterimport/cancel/', $import.id )|ezurl} 
                                                 onclick="return confirm('{'Are you sure you want to cancel this import ?'|i18n( 'extension/sqliimport' )}')">{'Cancel'|i18n( 'extension/sqliimport' )}</a>)
                                    
                                    {/case}
                                    {case match=1}{* Running *}
                                        (<a href={concat( '/sqliimport/alterimport/interrupt/', $import.id )|ezurl}
                                                 onclick="return confirm('{'Are you sure you want to interrupt this import ?'|i18n( 'extension/sqliimport' )}')">{'Interrupt'|i18n( 'extension/sqliimport' )}</a>)
                                    
                                    {/case}
                                    {case}{/case}
                                {/switch}
                            {/if}
                            </td>
                            <td>{$import.percentage}%</td>
                            <td>{$import.progression_notes}</td>
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