<?php

require_once 'HtmlTemplate.php';
require_once 'Themen.php';

/**
 * Manages data of one Erlass.
 *
 * @author maikel
 */
class Erlass {

    private static $fields = array('id', 'Bestellnummer', 'Kategorie',
        'Herkunft', 'Autor', 'Datum', 'Aktenzeichen', 'Betreff', 'NfD',
        'Dokument');

    private static function standardizeDate($date) {
        if (strstr($date, '.')) {
            $parts = explode('.', $date);
            $parts = array_reverse($parts);
            $date = implode('-', $parts);
        }
        return $date;
    }

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
        $fields = array('Bestellnummer', 'Kategorie', 'Herkunft',
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
        $data['Datum'] = self::standardizeDate($data['Datum']);
        if (!isset($_POST['id'])) {
            return;
        }
        $id = (int) $_POST['id'];
        if ($id > 0) {
            $id = (int) $_POST['id'];
            $setStrings = array();
            foreach ($fields as $field) {
                $setStrings[] = $field . '="' . $data[$field] . '"';
            }
            $query = 'update Erlass set ' . implode(', ', $setStrings)
                    . ' where id="' . $id . '";';
            mysql_query($query);
        } else {
            $query = 'insert into Erlass (' . implode(', ', $fields) . ')'
                    . ' values ("' . implode('", "', $data) . '");';
            $result = mysql_query($query);
            if ($result && mysql_affected_rows()) {
                $id = mysql_insert_id();
            }
        }
        Themen::setFromPostFor($id);
        return self::fromDB($id);
    }

    private $data;

    public function get($field) {
        return $this->data[$field];
    }

    public function __construct($data = null) {
        if ($data) {
            $this->data = $data;
        } else {
            foreach (self::$fields as $f) {
                $this->data[$f] = '';
            }
        }
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
