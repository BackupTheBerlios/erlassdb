<?php

require_once 'classes/ErlassDB.php';
// TODO: delete user
// Suchformular weiter zusammen: drop-down neben Überschrift
// kleiner suchbutton
// logout statt wechseln
// links in der unteren zeile ohne hover
// Text für Themenbaum
// datum deutsch formatieren
$erlassDb = new ErlassDB();

if (sizeof($_GET) > 0 || sizeof($_POST) > 0) {
    if (!isset($_GET['register'])
            && !isset($_GET['about'])
            && !isset($_POST['nachname'])
            && !isset($_GET['newPassword'])) {
        $erlassDb->authenticateUser();
    }
}

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
    } elseif (isset($_REQUEST['newPassword']) || isset($_REQUEST['challenge'])) {
        $erlassDb->newPassword();
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
    } elseif (isset($_GET['start'])) {
        $erlassDb->start();
    } elseif (isset($_GET['about'])) {
        $erlassDb->about();
    } else {
        $erlassDb->welcome();
    }
}

$erlassDb->showPage();
?>
