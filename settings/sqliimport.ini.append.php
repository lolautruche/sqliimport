<?php /* #?ini charset="utf-8"?

[ImportSettings]
AvailableSourceHandlers[]=rssimporthandler

[rssimporthandler-HandlerSettings]
# Indicates if handler is enabled or not. Mandatory. Must be "true" or "false"
Enabled=true
# Intelligible name
Name=RSS Handler
# Class for source handler. Must implement ISQLIImportSourceHandler and extend SQLIImportAbstractSourceHandler
ClassName=SQLIRSSImportHandler
# Facultative. Indicates whether debug is enabled or not
Debug=enabled
# Same as [ImportSettings]/DefaultParentNodeID, but source handler specific
DefaultParentNodeID=43
# StreamTimeout, handler specific. If empty, will take [ImportSettings]/StreamTimeout
StreamTimeout=
# Below you can add your own settings for your source handler
RSSFeed=http://www.lolart.net/rss/feed/blog

# Import handler options list
Options[]
Options[]=sample_string
Options[]=sample_bool
Options[]=sample_file

# Options labels. Use alias defined in Options[] as key
OptionsLabels[sample_string]=String
OptionsLabels[sample_bool]=Boolean
OptionsLabels[sample_file]=File

# Options type. Use alias defined in Options[] as key
# Defaults to string
# Available types : string|boolean|file
OptionsTypes[sample_bool]=boolean
OptionsTypes[sample_file]=file

# Options default values. Empty String if not set
OptionsDefaults[]
OptionsDefaults[sample_string]=Sample

*/ ?>
