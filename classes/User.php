<?php

require_once 'HtmlTemplate.php';
require_once 'AdminMail.php';
require_once 'Mailer.php';
require_once 'WEBDIR.php';

/**
 * Authenticates the user and manages user accounts.
 */
class User {
    const ADMIN_LEVEL = 255;
    private static $checkboxFields = array(
        'nutzungStaatlich',
        'nutzungAnwalt',
        'nutzungBeratung',
        'nutzungSonstiges',
        'newsletter'
    );
    private $id = null;
    private $level = -1;
    private $adminMail;

    public function __construct() {
        $this->adminMail = new AdminMail();
        $this->checkUser();
    }

    public function isRegistered() {
        if ($this->level > 0) {
            return true;
        }
        return false;
    }

    public function hasNfd() {
        if ($this->level > 1) {
            return true;
        }
        return false;
    }

    public function isAdmin() {
        if ($this->level == self::ADMIN_LEVEL) {
            return true;
        }
        return false;
    }

    public function assignToTemplate(HtmlTemplate $tmpl) {
        // TODO: farbe für status
        $tmpl->assign('user', $this->id);
        $tmpl->assign('level', $this->level);
        $tmpl->assign('status', self::labelOfLevel($this->level));
        $tmpl->assign('request', self::userRequest());
        if ($this->level >= 0) {
            $tmpl->addSubtemplate('loggedIn');
        }
        if ($this->level < 1 && $this->adminMail->getAddress()) {
            $tmpl->addSubtemplate('registerLink');
        }
        if ($this->isAdmin()) {
            $tmpl->addSubtemplate('adminLink');
        }
    }

    public function assignRegistrationToTemplate(HtmlTemplate $tmpl) {
        $textFields = array(
            'nachname',
            'vorname',
            'inst',
            'adresse',
            'sonstigerZweck',
            'mail',
            'passwort',
            'passwortB'
        );
        foreach ($textFields as $field) {
            $tmpl->assign($field, stripslashes($_POST[$field]));
        }
        foreach (self::$checkboxFields as $field) {
            $htmlValue = '1';
            if (isset($_POST[$field]) && $_POST[$field]) {
                $htmlValue .= '" checked="checked';
            }
            $tmpl->assignHtml($field, $htmlValue);
        }
    }

    public function assignAdminToTemplate(HtmlTemplate $tmpl) {
        if ($this->adminMail->getAddress()) {
            $sub = $tmpl->addSubtemplate('adminMailInfo');
            $sub->assign('adminMail', $this->adminMail->getAddress());
            return;
        }
        if (is_writable('.')) {
            $tmpl->addSubtemplate('adminMailForm');
        } else {
            $sub = $tmpl->addSubtemplate('adminMailWrite');
            $sub->assign('pwd', getcwd());
        }
    }

    public function newAdminMail($adminMail, $adminPasswd) {
        $this->adminMail->update($adminMail);
        $query = 'insert into Kunde (id, Passwort) values ('
                . '"' . $adminMail . '", sha1("' . $adminPasswd . '"));';
        mysql_query($query);
    }

    /**
     * Tries to start the registration process.
     *
     * @return string name of the first missing but required field in $_POST
     */
    public function registration() {
        $neededFields = array(
            'nachname',
            'vorname',
            'adresse',
            'mail',
            'passwort',
            'passwortB'
        );
        $optionalFields = self::$checkboxFields;
        $optionalFields[] = 'inst';
        $optionalFields[] = 'sonstigerZweck';
        $data = array();
        foreach ($neededFields as $field) {
            if (!(isset($_POST[$field]) && $_POST[$field])) {
                return $field;
            }
            $data[$field] = $_POST[$field];
        }
        foreach ($optionalFields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            } else {
                $data[$field] = '';
            }
        }
        if (!strstr($data['mail'], '@') || !Mailer::isValidAddress($data['mail'])) {
            return 'mail';
        }
        if ($data['passwort'] != $data['passwortB']) {
            return 'passwortB';
        }
        $query = 'insert into Kunde (id, Passwort) values ('
                . '"' . $data['mail'] . '", '
                . 'sha1("' . $data['passwort'] . '"));';
        // TODO: check, if id is used
        mysql_query($query);
        $subject = 'Registrierung';
        $content = "Hallo!\n\n"
                . "Es hat sich ein neuer Kunde registriert:\n\n"
                . "E-Mailadresse: " . $data['mail'] . "\n"
                . "Name: " . $data['vorname'] . ' ' . $data['nachname'] . "\n"
                . "Institution: " . $data['inst'] . "\n"
                . "Adresse:\n" . $data['adresse'] . "\n"
                . "Folgende Optionen wurden angegeben:\n";
        foreach (self::$checkboxFields as $field) {
            if (isset($data[$field]) && $data[$field])
                $content .= ' - ' . $field . "\n";
        }
        $content .= "\n\n"
                . "Status ändern:\n"
                . WEBDIR . "admin.php?setLevel=" . urlencode($data['mail']) . "\n";
        Mailer::mail($this->adminMail->getAddress(), $subject, $content, $data['mail']);
    }

    private function checkUser() {
        if (self::userPresent()) {
            $user = $_SERVER['PHP_AUTH_USER']; // TODO: check input
            $passwd = $_SERVER['PHP_AUTH_PW'];
            $this->id = $user;
            $this->level = $this->levelFor($user, $passwd);
            if (self::newAuth($this->level)) {
                self::authenticate();
            } elseif ($passwd != '' && $this->level < 1) {
                self::authenticate();
            }
        } else {
            self::authenticate();
        }
    }

    private static function authenticate() {
        $realm = 'E-Mailadresse und Passwort angeben, falls vorhanden';
        header('WWW-Authenticate: Basic realm="' . $realm . '"');
        header('HTTP/1.0 401 Unauthorized');
    }

    private static function userPresent() {
        return isset($_SERVER['PHP_AUTH_USER'])
        && isset($_SERVER['PHP_AUTH_PW']);
    }

    private static function userRequest() {
        // TODO
        return $_SERVER['QUERY_STRING'];
    }

    private static function newAuth($newLevel) {
        if (isset($_POST['oldUser']) && isset($_POST['oldLevel'])) {
            $nothingChanged = $_POST['oldUser'] == $_SERVER['PHP_AUTH_USER'];
            $nothingChanged &= $_POST['oldLevel'] == $newLevel;
            return $nothingChanged;
        } else {
            return false;
        }
    }

    private function levelFor($user, $passwd) {
        if ($this->adminMail->getAddress() === null) {
            return self::ADMIN_LEVEL;
        }
        if ($passwd) {
            $query = 'select Stufe from Kunde where id="' . $user
                    . '" and binary Passwort=sha1("' . $passwd . '");';
            $result = mysql_query($query);
            $row = mysql_fetch_row($result);
            if ($row) {
                if ($user == $this->adminMail->getAddress()) {
                    return self::ADMIN_LEVEL;
                }
                return $row[0];
            }
        }
        return 0;
    }

    public static function levelOf($user) {
        $query = 'select Stufe from Kunde where id="' . $user . '";';
        $result = mysql_query($query);
        $row = mysql_fetch_row($result);
        if ($row) {
            return $row[0];
        } else {
            return 0;
        }
    }

    private static function labelOfLevel($level) {
        switch ($level) {
            case -1:
                return 'unangemeldet';
            case 0:
                return 'anonym';
            case 1:
                return 'angemeldet';
            case 2:
                return 'angemeldet (NfD)';
            case 3:
                return 'angemeldet (NfD, doc)';
            case self::ADMIN_LEVEL:
                return 'Administration';
            default:
                throw new Exception('User has unkown level: ' . $level);
        }
    }

}

?>
