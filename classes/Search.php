<?php

require_once 'HtmlTemplate.php';
require_once 'FieldList.php';

/**
 * Handles simple and extended search requests.
 *
 * @author maikel
 */
class Search {

    private static $fields = array('search', 'extended', 'periodStart',
        'periodEnd', 'Aktenzeichen');
    private static $listNames = array('Kategorie', 'Herkunft', 'Autor');

    public static function filterFromPost() {
        if (!isset($_POST['filter'])) {
            return '';
        }
        $name = $_POST['filter'];
        if (!in_array($name, self::$listNames)) {
            return '';
        }
        $list = new FieldList($name);
        $listArrays = array();
        foreach (self::$listNames as $listName) {
            if ($listName == $name) {
                continue;
            }
            $listArrays[] = $list->filteredArrayOf($listName);
        }
        return implode("\n", array_merge($listArrays[0], $listArrays[1]));
    }

    private $data = array();
    private $lists = array();
    private $dataGiven = false;

    public function __construct() {
        foreach (self::$fields as $field) {
            if (isset($_POST[$field])) {
                $this->data[$field] = $_POST[$field];
                $this->dataGiven = true;
            } else {
                $this->data[$field] = '';
            }
        }
        foreach (self::$listNames as $name) {
            $this->lists[] = new FieldList($name);
        }
    }

    public function assignExtendedToForm($form) {
        foreach ($this->data as $field => $value) {
            $form->assign($field, $value);
        }
        foreach (self::$listNames as $listName) {
            $list = new FieldList($listName);
            $list->assignToTemplate($form->addSubtemplate('CheckboxList'));
        }
        $themen = Themen::fromDatabase();
        $form->assignHtml('themen', $themen->getHtmlWithPost());
    }

    public function search() {
        $query = 'select id, Betreff from Erlass'
                . ' where match(Betreff, Dokument)'
                . ' against ("' . $this->data['extended'] . '" in boolean mode)'
                . ' order by Datum';
        return mysql_query($query);
    }

}

?>
