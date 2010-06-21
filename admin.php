<?php

require_once 'classes/ErlassDB.php';
require_once 'classes/Template.php';

$erlassDb = new ErlassDB();

if (isset($_GET['new'])) {
    $erlassDb->newForm();
}
elseif (isset($_POST['new'])) {
	$erlassDb->add($_POST);
}
else {
    $erlassDb->admin();
}

$erlassDb->showPage();

?>
