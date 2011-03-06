<?php

require_once 'classes/ErlassDB.php';

$erlassDb = new ErlassDB();

if (isset($_GET['admin']) && $erlassDb->userIsAdmin()) {
    
    if (isset($_POST['adminMail'])) {
        $erlassDb->saveAdminMail();
    }

    if (isset($_POST['stufe'])) {
        $erlassDb->setLevel();
    }

    if (isset($_GET['setLevel'])) {
        $erlassDb->setLevelForm($_GET['setLevel']);
    } elseif (isset($_GET['new'])) {
        $erlassDb->newForm();
    } elseif (isset($_POST['new'])) {
        $erlassDb->add($_POST);
    } else {
        $erlassDb->admin();
    }
} else {

    if (isset($_GET['register']) || isset($_POST['nachname'])) {
        $erlassDb->register();
    } elseif (isset($_GET['search'])) {
        $erlassDb->resultsFor($_GET['search']);
    } elseif (isset($_GET['show'])) {
        $erlassDb->show($_GET['show']);
    } elseif (isset($_GET['edit'])) {
        // TODO
    } elseif (isset($_GET['new'])) {
        // TODO
    } else {
        $erlassDb->start();
    }
}

$erlassDb->showPage();
?>
