<?php
/*
 * Security: Force quoting of GET and POST variables.
**/
include_once 'magic_quotes.php';

/**
 * Connects to a MySQL-Database. Checks configuration and provides information
 * about it.
 */
class MyDatabase {

    private static $connection = null;

    /**
     * Opens a connection to the database.
     */
    public static function connect() {
        include 'mysql.php';
        self::$connection = @mysql_connect($server,$username,$password);
        if (!self::$connection) return;
        if (!mysql_select_db($database)) {
            /* fail */
            $err_num = mysql_errno();
            switch ($err_num) {
                case 1049: // database not found
                    mysql_query('create database '.$database.';');
                    mysql_select_db($database);
                    break;
                    /* else */
                    echo 'Failed to create database.';
                    exit;
            }
        }
    }

    /**
     * Returns the connection handler.
     */
    public static function getConnection() {
        return self::$connection;
    }

}
?>
