<?php

require_once 'HtmlTemplate.php';
require_once 'AdminMail.php';

/**
 * Authenticates the user and manages user accounts.
 */
class User {

    /**
     * Tries to start the registration process.
     *
     * @return string name of the first missing but required field in $_POST
     */
    public static function registration() {
        $neededFields = array(
            'nachname',
            'vorname',
            'adresse',
            'mail',
            'passwort',
            'passwortB'
        );
        $optionalFields = array(
            'inst',
            'nutzung',
            'sonstigerZweck',
            'newsletter'
        );
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
    }

    private $id = null;
    private $level = -1;
    private $adminMail;

    public function __construct() {
        $this->adminMail = new AdminMail();
        $this->checkUser();
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
    }

    private function checkUser() {
        if (self::userPresent()) {
            $user = $_SERVER['PHP_AUTH_USER']; // TODO: check input
            $passwd = $_SERVER['PHP_AUTH_PW'];
            $this->id = $user;
            $this->level = self::levelFor($user, $passwd);
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

    private static function levelFor($user, $passwd) {
        if ($passwd) {
            $query = 'select Stufe from Kunde where id="' . $user
                    . '" and binary Passwort=sha1("' . $passwd . '");';
            $result = mysql_query($query);
            $row = mysql_fetch_row($result);
            if ($row) {
                return $row[0];
            }
        }
        return 0;
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
            default:
                throw new Exception('User has unkown level: ' . $level);
        }
    }

}

?>
