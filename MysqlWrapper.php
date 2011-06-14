<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dina
 * Date: 14.06.11
 * Time: 21:34
 * To change this template use File | Settings | File Templates.
 */
 
class MysqlWrapper {

    // Store the single instance of Database
    private static $instance;

    private    $server   = ""; //database server
    private    $user     = ""; //database login name
    private    $pass     = ""; //database login password
    private    $database = ""; //database name

    private    $link_id = 0;
    private    $query_id = 0;

    private function __construct ($server = null, $user = null, $pass = null, $database = null)
    {
        if ($server === null || $user === null || $database === null)
            throw new Exception("Database information must be passed in when the object is first created.");

        $this->server   = $server;
        $this->user     = $user;
        $this->pass     = $pass;
        $this->database = $database;

        $this->connect();
    }

    public static function getInstance ($server = null, $user = null, $pass = null, $database = null)
    {
        if (!self::$instance)
            self::$instance = new MysqlWrapper($server, $user, $pass, $database);
        return self::$instance;
    }

    public function connect($new_link = false)
    {
        $this->link_id = mysql_connect ($this->server, $this->user, $this->pass, $new_link);

        if (!$this->link_id)
            throw new Exception("Could not connect to server: <b>$this->server</b>.");

        if (!mysql_select_db($this->database, $this->link_id))
            throw new Exception("Could not open database: <b>$this->database</b>.");

        $this->server   = "";
        $this->user     = "";
        $this->pass     = "";
        $this->database = "";
    }

    public function close ()
    {
        if (!mysql_close($this->link_id))
            throw new Exception("Connection close failed.");
    }

    public function dbQuery ($sql_string)
    {
        $this->query_id = mysql_query($sql_string, $this->link_id);
        if (!$this->query_id)
        {
            throw new Exception("<b>MySQL Query fail:</b> $sql_string");
            return 0;
        }
        return $this->query_id;
    }

    public function fetchAssocArray ()
    {
        $query_id = $this->query_id;
        $out = array();

        while ($row = mysql_fetch_assoc($query_id))
            $out[] = $row;

        return $out;
    }

    public function mysqlRows ()
    {
        return mysql_num_rows($this->query_id);
    }

}
