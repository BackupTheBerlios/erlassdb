<?php

/**
 * Provides access to the mail address of the administrator.
 *
 * @author maikel
 */
class AdminMail {

    const FILE = 'adminMail.txt';
    private $mail;

    public function __construct() {
        if (is_readable(self::FILE)) {
            $this->mail = trim(file_get_contents(self::FILE));
        }
        // load file
    }

    public function getAddress() {
        return $this->mail;
    }

}

?>
