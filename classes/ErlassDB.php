<?php

require_once 'MyDatabase.php';
require_once 'HtmlTemplate.php';
require_once 'User.php';

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

    public function register() {
        if (isset($_POST['mail'])) {
            $this->registerData();
        } else {
            $this->template->addSubtemplate('registerForm');
        }
    }

    public function registerData() {
        $missingField = $this->user->registration();
        if ($missingField) {
            $formTmpl = $this->template->addSubtemplate('registerForm');
            $formTmpl->addSubtemplate('missing_' . $missingField);
        }
    }

    public function resultsFor($search) {
        $this->searchForm($search);
        $query = 'select id, aktenzeichen from erlass'
                . ' where match(text) against ("' . $search . '")'
                . ' and nfd=0'
                . ' order by datum;';
        $result = mysql_query($query);
        if (mysql_num_rows($result) == 0) {
            $this->template->addSubtemplate('noResults');
            return;
        }
    }

    public function show($id) {
        $query = 'select id, aktenzeichen, datum, institution, verfasser, text from erlass'
                . ' where id="' . $id . '" and nfd=0;';
        $result = mysql_query($query);
        if (mysql_num_rows($result) != 1) {
            // TODO
            exit;
        }
        $array = mysql_fetch_array($result);
        $erlassTmpl = $this->template->addSubtemplate('erlass');
        foreach ($array as $key => $value) {
            $erlassTmpl->assign($key, $value);
            // TODO: quoting
        }
    }

    public function admin() {
        $this->user->assignAdminToTemplate($this->template);
        $this->template->addSubtemplate('adminMenu');
    }

    public function saveAdminMail($adminMail) {
        $this->user->newAdminMail($adminMail);
    }

    public function newForm() {
        $form = $this->template->addSubtemplate('newForm');
        $form->assign('datum', date('Y-m-d'));
    }

    public function add($input) {
        // TODO: update
        $query = 'insert into erlass (aktenzeichen, datum, institution, verfasser, nfd, text)'
                . ' values ('
                . '"' . $input['aktenzeichen'] . '",'
                . '"' . $input['datum'] . '",'
                . '"' . $input['institution'] . '",'
                . '"' . $input['verfasser'] . '",'
                . '"' . $input['nfd'] . '",'
                . '"' . $input['text'] . '")'
                . ';';
        $result = mysql_query($query);
        if ($result && mysql_affected_rows($result)) {
            $this->template->addSubtemplate('erlassAdded');
        } else {
            $this->template->addSubtemplate('erlassNotAdded');
        }
    }

    public function showPage() {
        echo $this->template->result();
    }

    private function querySize() {
        $query = 'select count(*) from Erlass;';
        $result = mysql_query($query);
        $row = mysql_fetch_row($result);
        $this->size = $row[0];
    }

    private function showNewest() {
        $query = 'select id, aktenzeichen from erlass order by datum desc;'; // TODO: nfd?
        $result = mysql_query($query);
        if (mysql_num_rows($result) == 0)
            return;
        $newest = $this->template->addSubtemplate('newest');
        while ($erlassArray = mysql_fetch_array($result)) {
            $item = $newest->addSubtemplate('erlassItem');
            $item->assign('Aktenzeichen', $erlassArray['aktenzeichen']);
            $item->assign('id', $erlassArray['id']);
            // TODO: htmlquote
        }
    }

    private function searchForm($search = '') {
        $form = $this->template->addSubtemplate('search');
        $form->assign('search', $search);
    }

}

?>
