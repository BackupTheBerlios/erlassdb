<?php

require_once 'classes/ErlassDB.php';

$erlassDb = new ErlassDB();

if (sizeof($_GET) > 0 || sizeof($_POST) > 0) {
    if (!isset($_GET['register'])
            && !isset($_GET['about'])
            && !isset($_GET['nutzungsbedingungen'])
            && !isset($_POST['nachname'])
            && !isset($_REQUEST['challenge'])
            && !isset($_REQUEST['newPassword'])) {
        $erlassDb->authenticateUser();
    }
}

if (isset($_GET['admin'])) {

    if (isset($_POST['adminMail'])) {
        $erlassDb->saveAdminMail();
    }

    if (isset($_POST['stufe']) && !isset($_POST['delete'])) {
        $erlassDb->setLevel();
    }

    if (isset($_POST['deleteConfirmed'])) {
        $erlassDb->deleteKunde();
    }

    if (isset($_GET['setLevel'])) {
        $erlassDb->setLevelForm($_GET['setLevel']);
    } elseif (isset($_POST['delete'])) {
        $erlassDb->deleteKundeForm();
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
    } elseif (isset($_GET['nutzungsbedingungen'])) {
        $erlassDb->usageterms();
    } else {
        $erlassDb->welcome();
    }
}

$erlassDb->showPage();
?>
