<?php

/*
 * Security: Force quoting of GET and POST variables.
 */
require_once 'magic_quotes.php';

/**
 * Connects to a MySQL-Database. Checks configuration and provides information
 * about it.
 */
class MyDatabase {
    const CONFIG = 'mysql.php';
    const SETUP = 'setup.sql';

    private static $connection = null;

    /**
     * Checks whether a database configuration exists.
     * @return boolean true, if a readable configuration file exists
     */
    public static function isConfigured() {
        if (!is_file(self::CONFIG))
            return false;
        if (!is_readable(self::CONFIG))
            return false;
        return true;
    }

    public static function writeConfig($server, $username, $password, $database) {
        // TODO: use or delete
        if (!is_writable('./'))
            return 'not writeable';
        $fp = fopen('mysql.php', 'w');
        $filedata = '<?php'
                . ' $server = \'' . $server . '\';'
                . ' $username = \'' . $username . '\';'
                . ' $password = \'' . $password . '\';'
                . ' $database = \'' . $database . '\'; ?>';
        fwrite($fp, $filedata, strlen($filedata));
        fclose($fp);
        chmod('mysql.php', 0400);
        mkdir('img', 0755);
        return '';
    }

    public static function createTables() {
        // TODO: use or delete
        if (!self::$connection)
            self::connect();
        $fileArray = file(self::SETUP);
        $query = '';
        foreach ($fileArray as $line) {
            if (!$line) {
                mysql_query($query);
                $query = '';
                continue;
            }
            $query .= $line;
        }
    }

    /**
     * Opens a connection to the database.
     */
    public static function connect() {
        if (!self::isConfigured()) {
            return;
        }
        include self::CONFIG;
        self::$connection = @mysql_connect($server, $username, $password);
        if (!self::$connection)
            return;
        if (!mysql_select_db($database)) {
            /* fail */
            $err_num = mysql_errno();
            switch ($err_num) {
                case 1049: // database not found
                    mysql_query('create database ' . $database . ';');
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
