<?php
/**
 * SQLIImportBaseException
 * @copyright Copyright (C) 2010 - SQLi Agency. All rights reserved
 * @licence http://www.gnu.org/licenses/gpl-2.0.txt GNU GPLv2
 * @author Jerome Vieilledent
 * @version @@@VERSION@@@
 * @package sqliimport
 */

class SQLIImportBaseException extends ezcBaseException
{
    const IMPORT_ALREADY_RUNNING = -1,
          UNDEFINED_HANDLERS = -2;
}