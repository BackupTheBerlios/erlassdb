<?php

require_once 'MyDatabase.php';
require_once 'HtmlTemplate.php';
require_once 'User.php';
require_once 'Erlass.php';
require_once 'Files.php';
require_once 'Themen.php';
require_once 'Search.php';

class ErlassDB {

    private $template;
    private $size = 0;
    private $user;

    public function __construct() {
        MyDatabase::connect();
        $this->querySize();
        $this->template = HtmlTemplate::fromFile('index.html');
        $this->user = new User();
        $this->user->assignToTemplate($this->template);
    }

    public function start() {
        if ($this->size > 0) {
            $this->showNewest();
            $this->searchForm();
        } else {
            $this->template->addSubtemplate('empty');
        }
        $this->template->addSubtemplate('description');
    }

    public function extendedSearch() {
        $form = $this->template->addSubtemplate('extendedSearch');
        $search = new Search();
        $search->assignToTemplate($form);
    }

    public function sendFilter() {
        echo Search::filterFromPost();
    }

    public function register() {
        if (isset($_POST['mail'])) {
            $this->registerData();
        } else {
            $formTmpl = $this->template->addSubtemplate('registerForm');
            $this->user->assignRegistrationToTemplate($formTmpl);
        }
    }

    private function registerData() {
        $missingField = $this->user->registration();
        if ($missingField) {
            $formTmpl = $this->template->addSubtemplate('registerForm');
            $formTmpl->addSubtemplate('missing_' . $missingField);
            $this->user->assignRegistrationToTemplate($formTmpl);
        } else {
            $this->template->addSubtemplate('registered');
            $this->start();
        }
    }

    public function setLevelForm($mail) {
        $this->forceAdmin();
        $sub = $this->template->addSubtemplate('setLevelForm');
        $sub->assign('mail', $mail);
        $sub->assign('stufe', User::levelOf($mail));
    }

    public function setLevel() {
        $this->forceAdmin();
        if (isset($_POST['setLevel']) && isset($_POST['stufe'])) {
            $stufe = (int) $_POST['stufe'];
            if ($stufe < 1) {
                return;
            }
            $query = 'update Kunde set Stufe="' . $stufe . '"'
                    . ' where id="' . $_POST['setLevel'] . '";';
            mysql_query($query);
            if (mysql_affected_rows ()) {
                $this->template->addSubtemplate('levelSet');
            }
        }
    }

    public function resultsFor($search) {
        $this->searchForm($search);
        $query = 'select id, Betreff from Erlass'
                . ' where match(Betreff, Dokument)'
                . ' against ("' . $search . '" in boolean mode)'
                . ' order by Datum;';
        $result = mysql_query($query);
        if (mysql_num_rows($result) == 0) {
            $this->template->addSubtemplate('noResults');
            return;
        }
        $list = $this->template->addSubtemplate('results');
        $this->fillItemsInto($list, $result);
    }

    public function show($id) {
        $erlass = Erlass::fromDB($id);
        if ($erlass == null) {
            header('HTTP/1.0 404 Not Found');
            $this->template->addSubtemplate('notFound');
            return;
        }
        $this->display($erlass);
    }

    public function download($file) {
        if (!$this->user->hasFileaccess()) {
            exit;
        }
        $idExt = explode('.', $file);
        if (sizeof($idExt) != 2) {
            exit;
        }
        $id = (int) $idExt[0];
        $ext = $idExt[1];
        $files = new Files($id);
        $files->send($ext);
    }

    public function edit($id) {
        $erlass = Erlass::fromDB($id);
        if ($erlass == null) {
            exit;
        }
        $form = $this->erlassForm($erlass);
        $form->addSubtemplate('legendEdit');
        $form->addSubtemplate('submitEdit');
    }

    public function update() {
        $this->forceAdmin();
        $erlass = Erlass::fromPost();
        $this->display($erlass);
        $form = $this->template->addSubtemplate('uploadForm');
        $erlass->assignToTmpl($form);
    }

    public function delete($id) {
        $this->forceAdmin();
        $query = 'delete from Erlass where id="' . $id . '";';
        mysql_query($query);
        $this->template->addSubtemplate('deleted');
    }

    public function upload($id) {
        $this->forceAdmin();
        $files = new Files($id);
        $files->upload();
        $erlass = Erlass::fromDB($id);
        $this->display($erlass);
        $form = $this->template->addSubtemplate('uploadForm');
        $erlass->assignToTmpl($form);
    }

    public function admin() {
        $this->forceAdmin();
        $this->user->assignAdminToTemplate($this->template);
        $this->template->addSubtemplate('adminMenu');
    }

    public function saveAdminMail() {
        $this->forceAdmin();
        if ($_POST['adminPasswort'] != $_POST['adminPasswortB']) {
            echo 'Passwörter stimmen nicht überein.';
            return;
        }
        $this->user->newAdminMail($_POST['adminMail'], $_POST['adminPasswort']);
    }

    public function newForm() {
        $this->forceAdmin();
        $form = $this->erlassForm();
        $form->addSubtemplate('legendNew');
        $form->addSubtemplate('submitNew');
    }

    public function add($input) {
        $this->forceAdmin();
        $erlass = Erlass::fromPost();
        if ($erlass->get('id')) {
            $this->template->addSubtemplate('erlassAdded');
            $this->admin();
        } else {
            $this->template->addSubtemplate('erlassNotAdded');
        }
    }

    public function themen() {
        $this->forceAdmin();
        Themen::deleteFromPost();
        Themen::insertFromPost();
        $themen = Themen::fromDatabase();
        $sub = $this->template->addSubtemplate('themen');
        $sub->assignHtml('themen', $themen->getHtml());
    }

    public function showPage() {
        echo $this->template->result();
    }

    private function erlassForm(Erlass $erlass = null) {
        if ($erlass == null) {
            $erlass = new Erlass();
        }
        $erlassThemen = Themen::listOf($erlass->get('id'));
        $form = $this->template->addSubtemplate('erlassForm');
        $erlass->assignToTmpl($form);
        $themen = Themen::fromDatabase();
        $form->assignHtml('themen', $themen->getHtml($erlassThemen));
        // TODO: Themenfelder
        return $form;
    }

    private function display(Erlass $erlass) {
        if ($erlass->get('NfD') && !$this->user->hasNfd()) {
            $sub = $this->template->addSubtemplate('noAccessToNfD');
            if ($this->user->isRegistered()) {
                $sub->addSubtemplate('pleaseWait');
            } else {
                $sub->addSubtemplate('pleaseRegister');
            }
            return;
        }
        $erlassTmpl = $this->template->addSubtemplate('erlass');
        $erlass->assignToTmpl($erlassTmpl);
        if ($this->user->hasFileaccess()) {
            $files = new Files($erlass->get('id'));
            $files->assignToTmpl($erlassTmpl);
        }
        if ($erlass->get('NfD')) {
            $erlassTmpl->addSubtemplate('erlassNfD');
        }
        $erlassThemen = Themen::listOf($erlass->get('id'));
        if (count($erlassThemen) > 0) {
            $sub = $erlassTmpl->addSubtemplate('betrifft');
            foreach ($erlassThemen as $thema) {
                $themaTmpl = $sub->addSubtemplate('thema');
                $themaTmpl->assign('Name', $thema);
            }
        }
        if ($this->user->isAdmin()) {
            $erlassTmpl->addSubtemplate('erlassAdmin');
        }
    }

    private function querySize() {
        $query = 'select count(*) from Erlass;';
        $result = mysql_query($query);
        $row = mysql_fetch_row($result);
        $this->size = $row[0];
    }

    private function forceAdmin() {
        if (!$this->user->isAdmin()) {
            exit;
        }
    }

    private function showNewest() {
        $query = 'select id, Betreff from Erlass order by Datum desc;'; // TODO: nfd?
        $result = mysql_query($query);
        if (mysql_num_rows($result) == 0)
            return;
        $newest = $this->template->addSubtemplate('newest');
        $this->fillItemsInto($newest, $result);
    }

    private function fillItemsInto(HtmlTemplate $tmpl, $result) {
        while ($erlassArray = mysql_fetch_array($result)) {
            $item = $tmpl->addSubtemplate('erlassItem');
            $item->assign('id', $erlassArray['id']);
            $item->assign('Betreff', $erlassArray['Betreff']);
        }
    }

    private function searchForm($search = '') {
        $form = $this->template->addSubtemplate('search');
        $form->assign('search', stripslashes($search));
    }

}

?>
