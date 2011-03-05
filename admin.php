<?php

require_once 'classes/ErlassDB.php';
require_once 'classes/Template.php';

$erlassDb = new ErlassDB();

if (isset($_POST['adminMail'])) {
    $erlassDb->saveAdminMail($_POST['adminMail']) ;
}

if (isset($_POST['new'])) {
    $erlassDb->add($_POST);
} elseif (isset($_POST['new'])) {
    $erlassDb->add($_POST);
} else {
    $erlassDb->admin();
}

$erlassDb->showPage();
?>
