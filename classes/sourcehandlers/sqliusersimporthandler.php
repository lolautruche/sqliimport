<?php
class SQLIUsersImportHandler extends SQLIImportAbstractHandler implements ISQLIFileImportHandler
{

    protected $csv;

    /**
     * Main method called to configure/initialize handler.
     * Here you may read your data to import
     */
    public function initialize()
    {
        if( !$this->options->file )
        {
            throw new SQLIImportConfigException( 'No file to import!' );
        }

        $clusterFilePath = eZClusterFileHandler::instance()->fileFetch( $this->options->file );
        if( !$clusterFilePath )
        {
            throw new SQLIImportConfigException( $this->options->file . ' could not be found' );
        }

        $this->validateFile( 'file', $clusterFilePath );

        $this->csv = new SQLICSVDoc( new SQLICSVOptions( array(
            'csv_path' => $clusterFilePath
        ) ) );
        $this->csv->parse();
    }

    /**
     * Get the number of iterations needed to complete the process.
     * For example, if you have 150 XML nodes to process, you may return 150.
     * This is needed to display import progression in admin interface
     * @return int
     */
    public function getProcessLength()
    {
        return $this->csv->rows->count();
    }

    /**
     * Must return next row to process.
     * In an iteration over several XML nodes, you'll return the current node (like current() function for arrays)
     * @return SimpleXMLElement|SimpleXMLIterator|DOMNode|SQLICSVRow
     */
    public function getNextRow()
    {
        if( $this->csv->rows->valid() )
        {
            return $this->csv->rows->current();
        }

        return false;
    }

    /**
     * Main method to process current row returned by getNextRow() method.
     * You may throw an exception if something goes wrong. It will be logged but won't break the import process
     * @param mixed $row Depending on your data format, can be DOMNode, SimpleXMLIterator, SimpleXMLElement, CSV row...
     */
    public function process( $row )
    {
        $contentOptions = new SQLIContentOptions( array(
            'class_identifier'      => 'user',
            'remote_id'             => (string)$row->login
        ) );
        $content = SQLIContent::create( $contentOptions );
        $content->fields->first_name = (string)$row->firstName;
        $content->fields->last_name = (string)$row->lastName;

        $userParts = array(
            (string)$row->login,
            (string)$row->email
        );

        //password management : if empty, generate it, use custom default or fixed default
        $password = $row->password;
        if( !$password )
        {
            if( isset( $this->options->generate_password ) && $this->options->generate_password )
            {
                $password = eZUser::createPassword( 6 );
            }
            elseif( isset( $this->options->default_password ) && $this->options->default_password )
            {
                $password = $this->options->default_password;
            }
            else
            {
                $password = '_ezpassword';
            }
        }

        $userParts[] = $password;
        $userParts[] = eZUser::createHash((string)$row->login, $password, eZUser::site(), eZUser::hashType() );
        $userParts[] = eZUser::hashType();

        $content->fields->user_account = implode( '|', $userParts );

        // Now publish content
        $content->addLocation( SQLILocation::fromNodeID( $this->handlerConfArray['DefaultParentNodeID'] ) );
        $publisher = SQLIContentPublisher::getInstance();
        $publisher->publish( $content );

        // Free some memory. Internal methods eZContentObject::clearCache() and eZContentObject::resetDataMap() will be called
        // @see SQLIContent::__destruct()
        unset( $content );

        $this->csv->rows->next();
    }

    /**
     * Final method called at the end of the handler process.
     */
    public function cleanup()
    {
        $this->csv = null;
    }

    /**
     * Returns full handler name
     * @return string
     */
    public function getHandlerName()
    {
        return 'Members';
    }

    /**
     * Returns handler identifier, as in sqliimport.ini
     * @return string
     */
    public function getHandlerIdentifier()
    {
        return 'members';
    }

    /**
     * Returns notes for import progression. Can be any string (an ID, a reference...)
     * Can be for example ID of row your import handler has just processed
     * @return string
     */
    public function getProgressionNotes()
    {
        return $this->csv->rows->current()->login;
    }

    /**
     * Checks if file is in a valid format for $option
     * Returns true or throws an SQLIImportInvalidFileFormatException
     *
     * @param string $option 	File option alias
     * @param string $filePath	File to validate. Must be a valid local file (fetched from cluster if needed)
     * @return boolean
     * @throws SQLIImportInvalidFileFormatException
     * @throws SQLIImportConfigException if $option is not right
     */
    public function validateFile( $option, $filePath )
    {
        if( $option !== 'file' )
        {
            throw new SQLIImportConfigException( '"' . $option . '" is not a valid file option. Correct value is "file"' );
        }

        $csvOptions = new SQLICSVOptions( array() );

        //extract headers in temp file to ensure fast csv parsing
        $f = fopen( $filePath, 'r' );
        $headersLine = fgets( $f, $csvOptions->csv_line_length );
        $headersLine .= fgets( $f, $csvOptions->csv_line_length );
        fclose( $f );
        $tmpFile = tempnam( sys_get_temp_dir(), 'usersimport' );
        file_put_contents( $tmpFile, $headersLine );

        $csvOptions->csv_path = $tmpFile;
        $csv = new SQLICSVDoc( $csvOptions );
        $csv->parse();

        unlink( $tmpFile );

        $requiredHeaders = array(
            'login',
            'password',
            'email',
            'firstName',
            'lastName'
        );

        $sentHeaders = $csv->rows->getHeaders();

        $diff = array_diff( $requiredHeaders, $sentHeaders );
        if( !empty( $diff ) )
        {
            throw new SQLIImportInvalidFileFormatException(
                SQLIImportUtils::translate(
                	'sqliimport/usershandler',
                	"File must contain the following columns: %required Sent columns are: %sent",
                	'',
                    array(
                        '%required' => implode( $csvOptions->delimiter, $requiredHeaders ),
                        '%sent'		=> implode( $csvOptions->delimiter, $sentHeaders ),
                    )
                )
            );
        }

        return true;
    }

}