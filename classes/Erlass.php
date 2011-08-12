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
        'Status', 'Dokument');

    public static function standardizeDate($date) {
        if (strstr($date, '.')) {
            $parts = explode('.', $date);
            $parts = array_reverse($parts);
            $date = implode('-', $parts);
        }
        return $date;
    }

    public static function localizeDate($date) {
        return implode('.', array_reverse(explode('-', $date)));
    }

    /**
     * Fetches one Erlass from the database.
     *
     * @param int $id of the Erlass
     * @return Erlass object build from database or null
     */
    public static function fromDB($id) {
        $query = 'select ' . implode(', ', self::$fields) . ' from Erlass'
                . ' where id="' . $id . '";';
        $result = mysql_query($query);
        if (mysql_num_rows($result) != 1) {
            return null;
        }
        $array = mysql_fetch_array($result);
        return new self($array);
    }

    public static function fromPost() {
        $fields = self::$fields;
        unset($fields[0]); // delete 'id' field
        $data = array();
        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $data[$field] = $_POST[$field];
            } else {
                if ($field == 'NfD') {
                    $data['NfD'] = 0;
                } else {
                    echo '#';
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
        $this->data['DatumD'] = self::localizeDate($this->data['Datum']);
    }

    public function assignToTmpl(HtmlTemplate $tmpl) {
        foreach ($this->data as $key => $value) {
            $tmpl->assign($key, $value);
        }
        if (isset($this->data['Dokument'])) {
            $tmpl->assignText('Dokument', $this->data['Dokument']);
        }
        if (isset($this->data['NfD'])) {
            $nfd = '1';
            if ($this->data['NfD']) {
                $nfd .= '" checked="checked';
            }
            $tmpl->assignHtml('NfD', $nfd);
        }
        if (!$this->data['Betreff']) {
            $tmpl->assign('Betreff', 'ohne Betreff');
        }
    }

}

?>
