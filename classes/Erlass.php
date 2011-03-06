<?php

require_once 'HtmlTemplate.php';

/**
 * Manages data of one Erlass.
 *
 * @author maikel
 */
class Erlass {

    /**
     * Fetches one Erlass from the database.
     *
     * @param int $id of the Erlass
     * @return Erlass object build from database or null
     */
    public static function fromDB($id) {
        $query = 'select id, Bestellnummer, Kategorie, Herkunft, Autor, Datum,'
                . ' Aktenzeichen, Betreff, NfD, Dokument from Erlass'
                . ' where id="' . $id . '";';
        $result = mysql_query($query);
        if (mysql_num_rows($result) != 1) {
            return null;
        }
        $array = mysql_fetch_array($result);
        return new self($array);
    }

    public static function fromPost() {
        $fields = array('id', 'Bestellnummer', 'Kategorie', 'Herkunft',
            'Autor', 'Datum', 'Aktenzeichen', 'Betreff', 'NfD', 'Dokument');
        $data = array();
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            } else {
                if ($field == 'NfD') {
                    $data['NfD'] = 0;
                } else {
                    return null;
                }
            }
        }
        unset($fields[0]);
        $setStrings = array();
        foreach ($fields as $field) {
            $setStrings[] = $field . '="' . $data[$field] . '"';
        }
        $query = 'update Erlass set ' . implode(', ', $setStrings)
                . ' where id="' . $data['id'] . '";';
        mysql_query($query);
        return self::fromDB((int) $data['id']);
    }

    private $data;

    public function get($field) {
        return $this->data[$field];
    }

    private function __construct($data) {
        $this->data = $data;
    }

    public function assignToTmpl(HtmlTemplate $tmpl) {
        foreach ($this->data as $key => $value) {
            $tmpl->assign($key, $value);
        }
        $tmpl->assignText('Dokument', $this->data['Dokument']);
        $nfd = '1';
        if ($this->data['NfD']) {
            $nfd .= '" checked="checked';
        }
        $tmpl->assignHtml('NfD', $nfd);
    }

}

?>
