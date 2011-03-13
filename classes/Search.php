<?php

require_once 'HtmlTemplate.php';
require_once 'FieldList.php';

/**
 * Handles simple and extended search requests.
 *
 * @author maikel
 */
class Search {
    const FILTER_MODE_ADD = 'add';
    const FILTER_MODE_REMOVE = 'remove';

    private static $fields = array('search', 'extended', 'periodStart',
        'periodEnd', 'Aktenzeichen');
    private static $listNames = array('Kategorie', 'Herkunft', 'Autor');

    public static function filterFromPost() {
        if (!isset($_POST['filter']) || !isset($_POST['mode'])) {
            return '';
        }
        $name = $_POST['filter'];
        $mode = $_POST['mode'];
        if (isset($_POST['checked']) && is_array($_POST['checked'])) {
            $checked = $_POST['checked'];
        } else {
            $checked = array();
        }
        if ($mode == self::FILTER_MODE_ADD) {
            $list = '';
            $query = 'select distinct Herkunft from Erlass;';
            $result = mysql_query($query);
            while (list($herkunft) = mysql_fetch_row($result)) {
                $list .= 'Herkunft' . $herkunft . "\n";
            }
            return $list;
        } else {
            $list = '';
            $query = 'select distinct Herkunft from Erlass'
                    . ' where Kategorie="Kategorie";';
            $result = mysql_query($query);
            while (list($herkunft) = mysql_fetch_row($result)) {
                $list .= 'Herkunft' . $herkunft . "\n";
            }
            return $list;
        }
    }

    private $data = array();
    private $lists = array();

    public function __construct() {
        foreach (self::$fields as $field) {
            if (isset($_POST[$field])) {
                $this->data[$field] = $_POST[$field];
            } else {
                $this->data[$field] = '';
            }
        }
        foreach (self::$listNames as $name) {
            $this->lists[] = new FieldList($name);
        }
    }

    public function assignToTemplate(HtmlTemplate $tmpl) {
        foreach ($this->data as $field => $value) {
            $tmpl->assign($field, $value);
        }
        $kategorien = new FieldList('Kategorie');
        $kategorien->assignToTemplate($tmpl);
        $herkunften = new FieldList('Herkunft');
        $herkunften->assignToTemplate($tmpl);
        $autoren = new FieldList('Autor');
        $autoren->assignToTemplate($tmpl);
        $themen = Themen::fromDatabase();
        $tmpl->assignHtml('themen', $themen->getHtmlWithPost());
    }

}

?>
