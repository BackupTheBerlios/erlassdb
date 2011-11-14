<?php

class Mailer {

    /**
     * Simply sends a mail from ErlassDB.
     *
     * Currently known usages:
     *
     * @param string $to
     * @param string $subject
     * @param string $content
     * @return bool false on failure
     */
    public static function mail($to, $subject, $content, $reply_to = null) {
        if (!self::isValidAddress($to)) {
            return false;
        }
        $header = 'From: "ErlassDB" <noreply>' . "\n";
        if (isset($reply_to)) {
            $header .= 'Reply-To: ' . $reply_to . "\n";
        }
        $header .= 'Content-Type: text/plain; charset=UTF-8' . "\n";
        $subject = '[erlassdb] ' . $subject;
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        return mail($to, $encodedSubject, $content, $header);
    }

    /**
     * Checks for valid chars, but not for an address defined in RFC 2822.
     * 
     * <code>
     * $result = Mailer::isValidAddress('maikel@localhost');
     * var_dump($result); /// bool(true)
     * </code>
     * 
     * <code>
     * $result = Mailer::isValidAddress("maikel\t@localhost");
     * var_dump($result); /// bool(false)
     * </code>
     *
     * <code>
     * $result = Mailer::isValidAddress('"maikel "@localhost');
     * var_dump($result); /// bool(true)
     * </code>
     * @param $mailAddress address to check
     * @return boolean seems to be valid or not
     */
    public static function isValidAddress($mailAddress) {
        return (boolean) preg_match('/^[[:print:]]+$/', $mailAddress);
    }

}

?>
