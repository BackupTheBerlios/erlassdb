<?php

require_once 'classes/MyDatabase.php';

MyDatabase::isConfigured() or die("Database is not configured.");
MyDatabase::connect();

$query = false;

// 2011-11-06 Erlass.Status tinyint -> varchar
//$query = 'alter table Erlass modify Status varchar(128) not null;';

if ($query) {
    echo 'Executing: ' . $query . " <br />\n";
    echo mysql_query($query) ? 'Success.' : 'Failed!';
} else {
    echo 'No query to execute.';
}
?>
