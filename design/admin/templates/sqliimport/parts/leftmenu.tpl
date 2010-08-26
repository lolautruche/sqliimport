<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h4>{'Import management'|i18n( 'extension/sqliimport' )}</h4>

</div></div></div></div></div></div>

<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-bl"><div class="box-br"><div class="box-content">

<ul>
    <li><a href={'/sqliimport/list/'|ezurl}>{'Import list'|i18n( 'extension/sqliimport' )}</a></li>
    <li><a href={'/sqliimport/scheduledlist'|ezurl}">{'Scheduled import(s)'|i18n( 'extension/sqliimport' )}</a></li>
    <li><a href={'/sqliimport/addimport'|ezurl}>{'Request a new immediate import'|i18n( 'extension/sqliimport' )}</a></li>
    <li><a onclick="return confirm('{'Are you sure you want purge to import history ?'|i18n( 'extension/sqliimport' )}')" href={'/sqliimport/purgelist'|ezurl}>{'Purge import history'|i18n( 'extension/sqliimport' )}</a></li>
</ul>

{* DESIGN: Content END *}</div></div></div></div></div></div>
