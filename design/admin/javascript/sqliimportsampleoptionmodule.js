/**
 * Sample option JS module for SQLIImport
 * 
 * Modules must set final value in form field of name ImportOption[option]
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Benjamin Choquet <benjamin.choquet@heliopsis.net>
 * @version @@@VERSION@@@
 * @package sqliimport
 */
YUI.add( 'sqliimportsampleoptionmodule', function( Y, name ){
	
	//write whatever JS code you need
	
	/**
	 * Register an init function for your import option
	 * 
	 * It will be called on form generation for each DOM element containing
	 * an attribute named data-module which value is your module name
	 * Said DOM element will be passed as an argument to your init function
	 */
	Y.SQLIImport.registerOptionModule( name, function( node ){
		//init code
	});
	
}, '0.0.1', {
	//set your module dependencies here
	requires: [ 'sqliimport' ]
});