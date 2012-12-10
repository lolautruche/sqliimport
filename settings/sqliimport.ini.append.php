<?php /* #?ini charset="utf-8"?

[ImportSettings]
AvailableSourceHandlers[]=rssimporthandler
AvailableSourceHandlers[]=members

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




[members-HandlerSettings]
# Indicates if handler is enabled or not. Mandatory. Must be "true" or "false"
Enabled=true
# Intelligible name
Name=Members
# Class for source handler. Must implement ISQLIImportSourceHandler and extend SQLIImportAbstractSourceHandler
ClassName=SQLIUsersImportHandler
# Facultative. Indicates whether debug is enabled or not
Debug=enabled
# Same as [ImportSettings]/DefaultParentNodeID, but source handler specific
DefaultParentNodeID=12

# Import handler options list
Options[]
Options[]=generate_password
Options[]=default_password
Options[]=file

# Options labels. Use alias defined in Options[] as key
OptionsLabels[generate_password]=Generate password if empty
OptionsLabels[default_password]=Default password if empty
OptionsLabels[file]=File

# Options type. Use alias defined in Options[] as key
# Defaults to string
# Available types : string|boolean|file
OptionsTypes[generate_password]=boolean
OptionsTypes[file]=file

# Options default values. Empty String if not set
OptionsDefaults[]
OptionsDefaults[default_password]=ezpasswd

# Allowed file types for options
# Format: <label1>:<filext1>;<fileextN>|<label2>:<fileext3>;<fileextN>
FileOptionsAllowedFileTypes[]
FileOptionsAllowedFileTypes[file]=CSV Files:*.csv;*.CSV
*/ ?>
