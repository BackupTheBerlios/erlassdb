<?php

require_once 'classes/ErlassDB.php';

$erlassDb = new ErlassDB();

if (isset($_GET['admin'])) {
    
    if (isset($_POST['adminMail'])) {
        $erlassDb->saveAdminMail();
    }

    if (isset($_POST['stufe'])) {
        $erlassDb->setLevel();
    }

    if (isset($_GET['setLevel'])) {
        $erlassDb->setLevelForm($_GET['setLevel']);
    } else {
        $erlassDb->admin();
    }
} else {

    if (isset($_GET['register']) || isset($_POST['nachname'])) {
        $erlassDb->register();
    } elseif (isset($_GET['extended']) || isset($_POST['extended'])) {
        $erlassDb->extendedSearch();
    } elseif (isset($_POST['filter'])) {
        $erlassDb->sendFilter();
        exit;
    } elseif (isset($_GET['search'])) {
        $erlassDb->resultsFor($_GET['search']);
    } elseif (isset($_GET['show'])) {
        $erlassDb->show((int) $_GET['show']);
    } elseif (isset($_GET['new'])) {
        $erlassDb->newForm();
    } elseif (isset($_POST['new'])) {
        $erlassDb->add($_POST);
    } elseif (isset($_GET['edit'])) {
        $erlassDb->edit((int) $_GET['edit']);
    } elseif (isset($_POST['edit'])) {
        $erlassDb->update();
    } elseif (isset($_GET['delete'])) {
        $erlassDb->delete((int) $_GET['delete']);
    } elseif (isset($_POST['upload'])) {
        $erlassDb->upload((int) $_POST['upload']);
    } elseif (isset($_GET['download'])) {
        $erlassDb->download($_GET['download']);
    } elseif (isset($_GET['setLevel'])) {
        $erlassDb->setLevelForm($_GET['setLevel']);
    } elseif (isset($_REQUEST['themen']) || isset($_POST['thema'])) {
        $erlassDb->themen();
    } else {
        $erlassDb->start();
    }
}

$erlassDb->showPage();
?>
