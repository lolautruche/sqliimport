<?php /* #?ini charset="utf-8"?

[CronjobSettings]
ExtensionDirectories[]=sqliimport

[CronjobPart-sqliimport_cleanup]
Scripts[]=sqliimport_cleanup.php
Scripts[]=indexcontent.php

[CronjobPart-sqliimport_run]
Scripts[]=sqliimport_run.php
Scripts[]=sqliimport_cleanup.php
Scripts[]=indexcontent.php

*/ ?>