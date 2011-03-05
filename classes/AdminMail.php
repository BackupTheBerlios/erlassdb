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
        $this->read();
    }

    public function getAddress() {
        return $this->mail;
    }

    public function update($newAddress) {
        file_put_contents(self::FILE, $newAddress);
        $this->read();
    }

    private function read() {
        if (is_readable(self::FILE)) {
            $this->mail = trim(file_get_contents(self::FILE));
        }
    }

}

?>
