<?php
/**
 * eZInfo for SQLIImport extension
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

class sqliimportInfo
{
    /**
     * eZInfo method
     */
    public static function info()
    {
        return array(
            'Name'            => 'SQLI Import',
            'Version'         => '@@@VERSION@@@',
            'Copyright'       => 'Copyright © 2010 @@@AUTHORS@@@ - SQLi Agency',
            'License'         => 'GNU General Public License v2.0',
            'Info'            => '<a href="http://projects.ez.no/sqliimport" target="_blank">http://projects.ez.no/sqliimport</a>'
        );
    }
}
