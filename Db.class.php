<?php
class DB
{
    # @object, The PDO object
    private $pdo;

    # @object, PDO statement object
    private $sQuery;

    # @array,  The database settings
    private $settings;

    # @bool ,  Connected to the database
    private $bConnected = false;

    # @array, The parameters of the SQL query
    private $parameters;

    /**
     *   Default Constructor 
     *
     *  1. Connect to database.
     *  2. Creates the parameter array.
     */
    public function __construct()
    {
        $this->Connect();
        $this->parameters = array();
    }

    /**
     *  This method makes connection to the database.
     *  
     *  1. Reads the database settings from a ini file. 
     *  2. Puts  the ini content into the settings array.
     *  3. Tries to connect to the database.
     */
    private function Connect()
    {
        $this->settings = parse_ini_file("settings.ini.php");
        $dsn            = 'mysql:dbname=' . $this->settings["dbname"] . ';host=' . $this->settings["host"] . '';
        try {
            # Read settings from INI file, set UTF8
            $this->pdo = new PDO($dsn, $this->settings["user"], $this->settings["password"], array(
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ));


            # Connection succeeded, set the boolean to true.
            $this->bConnected = true;
        }
        catch (PDOException $e) {
            # Throw the Exception
            throw new Exception($e->getMessage());
        }
    }
    /*
     *   You can use this little method if you want to close the PDO connection
     *
     */
    public function CloseConnection()
    {
        # Set the PDO object to null to close the connection
        # http://www.php.net/manual/en/pdo.connections.php
        $this->pdo = null;
    }

    /**
     *  Every method which needs to execute a SQL query uses this method.
     *  
     *  1. If not connected, connect to the database.
     *  2. Prepare Query.
     *  3. Parameterize Query.
     *  4. Execute Query.   
     *  5. On exception : Write Exception into the log + SQL query.
     *  6. Reset the Parameters.
     */
    private function Init($query, $parameters = "")
    {
        # Connect to database
        if (!$this->bConnected) {
            $this->Connect();
        }
        try {
            # Prepare query
            $this->sQuery = $this->pdo->prepare($query);

            # Add parameters to the parameter array 
            $this->bindMore($parameters);

            # Bind parameters
            if (!empty($this->parameters)) {
                foreach ($this->parameters as $param => $value) {
                    if(is_int($value[1])) {
                        $type = PDO::PARAM_INT;
                    } else if(is_bool($value[1])) {
                        $type = PDO::PARAM_BOOL;
                    } else if(is_null($value[1])) {
                        $type = PDO::PARAM_NULL;
                    } else {
                        $type = PDO::PARAM_STR;
                    }
                    // Add type when binding the values to the column
                    $this->sQuery->bindValue($value[0], $value[1], $type);
                }
            }

            # Execute SQL 
            $this->sQuery->execute();
        }
        catch (PDOException $e) {
            # Throw the Exception
            throw new Exception($e->getMessage());
        }

        # Reset the parameters
        $this->parameters = array();
    }

    /**
     *  @void 
     *
     *  Add the parameter to the parameter array
     *  @param string $para  
     *  @param string $value 
     */
    public function bind($para, $value)
    {
        $this->parameters[sizeof($this->parameters)] = [":" . $para , $value];
    }
    /**
     *  @void
     *  
     *  Add more parameters to the parameter array
     *  @param array $parray
     */
    public function bindMore($parray)
    {
        if (empty($this->parameters) && is_array($parray)) {
            $columns = array_keys($parray);
            foreach ($columns as $i => &$column) {
                $this->bind($column, $parray[$column]);
            }
        }
    }
    /**
     *  If the SQL query  contains a SELECT or SHOW statement it returns an array containing all of the result set row
     *  If the SQL statement is a DELETE, INSERT, or UPDATE statement it returns the number of affected rows
     *
     *      @param  string $query
     *  @param  array  $params
     *  @param  int    $fetchmode
     *  @return mixed
     */
    public function query($query, $params = null, $fetchmode = PDO::FETCH_ASSOC)
    {
        $query = trim(str_replace("\r", " ", $query));

        $this->Init($query, $params);

        $rawStatement = explode(" ", preg_replace("/\s+|\t+|\n+/", " ", $query));

        # Which SQL statement is used 
        $statement = strtolower($rawStatement[0]);

        if ($statement === 'select' || $statement === 'show') {
            return $this->sQuery->fetchAll($fetchmode);
        } elseif ($statement === 'insert' || $statement === 'update' || $statement === 'delete') {
            return $this->sQuery->rowCount();
        } else {
            return NULL;
        }
    }
    /**
     *  Returns the value of one single field/column
     *
     *  @param  string $query
     *  @param  array  $params
     *  @return string
     */
    public function single($query, $params = null)
    {
        $this->Init($query, $params);
        $result = $this->sQuery->fetchColumn();
        $this->sQuery->closeCursor(); // Frees up the connection to the server so that other SQL statements may be issued
        return $result;
    }
}
?>