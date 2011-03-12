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
        'periodEnd', 'Aktenzeichen'
    );
    private static $listNames = array('Kategorie', 'Herkunft', 'Autor');
    
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
