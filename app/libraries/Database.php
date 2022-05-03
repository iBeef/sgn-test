<?php
/*
 *  PDO Database Class
 *  Connect to database
 *  Create prepared statements
 *  Bind Values
 *  Return rows and results
 */

class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbName = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;

    /**
     * Setup variables and database on class initialisation.
     */
    public function __construct() {
        // Set DSN
        $dsn = "mysql:host=$this->host;dbname=$this->dbName";
        $options = array(
            PDO::ATTR_PERSISTENT => TRUE,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        );
        // Create PDO instance
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            echo $this->error;
        }
    }

    /**
     * Prepare a MYSQL statement.
     *
     * @access public
     * @access public
     * @param string $sql
     * @return void
     */
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    /**
     * Bind a parameter to a named value in the statement.
     *
     * @access public
     * @param string $param
     * @param dynamic $value
     * @param PDO::TYPE $type
     * @return void
     */
    public function bind($param, $value, $type=NULL) {
        if(is_null($type)) {
            switch(TRUE) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    /**
     * Bind multiple values in an array.
     *
     * @access public
     * @param assoc array [string => string] $data
     * @return void
     */
    public function multiBind($data) {
        foreach (array_keys($data) as $key) {
            $this->bind($key, $data[$key]);
        }
    }

    /**
     * Execute the prepared statement
     *
     * @access public
     * @return PDO::ResultSet
     */
    public function execute() {
        return $this->stmt->execute();
    }

    /**
     * Get result set as an array of objects
     *
     * @access public
     * @return array
     */
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * Get a single record as an object
     *
     * @access public
     * @return object
     */
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Get the row count of the executed statement
     *
     * @access public
     * @return int
     */
    public function rowCount() {
        return $this->stmt->rowCount();
    }
}