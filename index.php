<?php

require_once 'classes/ErlassDB.php';
require_once 'classes/Template.php';

$erlassDb = new ErlassDB();

if (isset($_GET['search'])) {
    $erlassDb->resultsFor($_GET['search']);
}
elseif (isset($_GET['show'])) {
    $erlassDb->show($_GET['show']);
}
elseif (isset($_GET['edit'])) {
    // TODO
}
elseif (isset($_GET['new'])) {
    // TODO
}
else {
    $erlassDb->start();
}

$erlassDb->showPage();

?>
