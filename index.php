<?php

require_once 'classes/ErlassDB.php';
require_once 'classes/Template.php';

// Administration

// TODO: if (isset)

// Erlassdatenbank

$erlassDb = new ErlassDB();

if (isset($_GET['register']) || isset($_POST['nachname'])) {
    $erlassDb->register();
}
elseif (isset($_GET['search'])) {
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
