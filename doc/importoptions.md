# Import options
You can now define for each source handler a set of options which will be rendered as an HTML form in admin interface.

Under the hood, when the user selects a source handler, an AJAX call is made returning the right HTML form. User input is posted when submitting form and stored as a global options string in the `sqliimportitem` DB table, just like before.

## A Note on backwards compatibility
New options implementation is fully compatible with previous behaviour (options string defined in a single textarea). 
If you wish to keep it old school, do not define any option and set the following value in `sqliimport.ini` :
<pre><code>
[OptionsGUISettings]
FallbackToTextarea=enabled
</code></pre>

## Defining options

Options are defined in `sqliimport.ini` in source handler block. 
Each option must have :

* an alias
* a label
* a type

<pre><code>
[myhandler-HandlerSettings]
Options[]
Options[]=myoption
OptionsLabels[myoption]=My custom option
OptionsType[myoption]=string
</code></pre>

Available types are :

* `string` (renders as a text input)
* `boolean` (renders as a checkbox)
* `file` (renders as a file upload widget)


## The `file` option type
The `file` option type allows the user to manually upload a file and store it on the server.

### File type restrictions
You may restrict selectable file types for each option by adding a new entry in `sqliimport.ini`.
Use option alias as key and define multiple types using format *label1*:*filext1*;*fileextN*|*label2*:*fileext3*;*fileextN*
<pre><code>
```
FileOptionsAllowedFileTypes[]
FileOptionsAllowedFileTypes[myoption]=CSV Files:*.csv;*.CSV
```
</code></pre>


### File format validation
File format can be checked before storing file on the server by making your source handler implement the `ISQLIFileImportHandler` interface and implement the `validateFile` method.
Said method takes two parameters :

* `$option` is option alias
* `$filePath` is the path for the file we want to check

The method must return true if validation was successful or throw a `SQLIImportInvalidFileFormatException` if there was any error. Exception message will be shown to the user.

See `classes/sourcehandlers/sqliusersimporthandler.php` for a working example.

## Creating a custom option type
Creating a custom option type is very easy : 

1. set an option to your new type
2. create a template to render your HTML form input

A few rules though :

* Your template must be saved in `design/admin/templates/sqliimport/optionwidgets/` and named after your customtype.
* Your template must contain an HTML form input named `ImportOption_{$option_id}` returning your final option value
* Current value is available in `$value` template variable

For example:

*settings/sqliimport.ini.append.php:*
<pre><code>
[myhandler-HandlerSettings]
Options[]
Options[]=myoption
OptionsLabels[myoption]=Select one:
OptionsType[myoption]=mycustomtype
</code></pre>

*design/admin/templates/sqliimport/optionwidgets/mycustomtype.tpl:*
<pre><code>
```
<select name="ImportOption_{$option_id}">
	<option value="foo"{if $value|eq('foo')} selected{/if}>Foo</option>
	<option value="bar"{if $value|eq('bar')} selected{/if}>Bar</option>
</select>
```
</code></pre>


## Creating an advanced custom option type
In case of complex user input for a single option (where you cannot rely on a single form field), you have to create a javascript module to process input and store a single value with processed data in a hidden form field.

Due to asynchronous loading of options form, your javascript file must be declared as a YUI3 module and register an initialization function in the main `sqliimport` JS module.
Boilerplate code for such a module is available in `design/admin/javascript/sqliimportsampleoptionmodule.js`

You then have to declare the JS module in `sqliimport.ini` :

<pre><code>
[OptionsGUISettings]
YUI3Modules[mycustommodulename]=javascript/mycustommodule.js
</code></pre>

Finally you have to declare in your template which modules it requires and which div DOM node constitutes your UI:
<pre><code>
```
<div data-module="mycustommodulename">
	<!--your module must fill in this field-->
	<input type="hidden" name="ImportOption_{$option_id}" value="" />
	<!--your custom UI-->
	...
</div>
{set $jsModules = $jsModules|merge( array( 'mycustommodulename' ) )}
```
</code></pre>

### Sequence of events

1. User selects a source handler
2. An AJAX call is made to get corresponding HTML form
3. Required YUI3 modules are loaded
4. HTML form is appended to the DOM
5. A module init function is called for each `div[data-module]` in the form

