<?php

require_once 'HtmlTemplate.php';

/**
 * Authenticates the user and manages user accounts.
 */
class User {

    private $id = null;
    private $level = -1;

    public function __construct() {
        $this->checkUser();
    }

    public function assignToTemplate(HtmlTemplate $tmpl) {
        $tmpl->assign('user', $this->id);
        $tmpl->assign('status', self::labelOfLevel($this->level));
        $tmpl->assign('request', self::userRequest());
        if ($this->level >= 0) {
            $tmpl->addSubtemplate('loggedIn');
        }
    }

    private function checkUser() {
        if (self::userPresent()) {
            $user = $_SERVER['PHP_AUTH_USER']; // TODO: check input
            $passwd = $_SERVER['PHP_AUTH_PW'];
            $this->id = $user;
            $this->level = self::levelFor($user, $passwd);
            if ($passwd == '' && self::newAuth()) {
                self::authenticate();
            }
            if ($passwd != '' && $this->level < 1) {
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

    private static function newAuth() {
        if (isset($_POST['oldUser'])) {
            return $_POST['oldUser'] == $_SERVER['PHP_AUTH_USER'];
        } else {
            return false;
        }
    }

    private static function levelFor($user, $passwd) {
        if ($passwd) {
            $query = 'select Stufe from Kunde where id="' . $user
                    . '" and Passwort=sha1("' . $passwd . '");';
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
