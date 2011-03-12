<?php

require_once 'HtmlTemplate.php';
require_once 'FieldList.php';

/**
 * Handles simple and extended search requests.
 *
 * @author maikel
 */
class Search {

    private $fields = array('search', 'extended', 'periodStart', 'periodEnd',
        'Aktenzeichen'
    );

    public function assignToTemplate(HtmlTemplate $tmpl) {
        foreach ($this->fields as $field) {
            $tmpl->assign($field);
        }
        $kategorien = new FieldList('Kategorie');
        $kategorien->assignToTemplate($tmpl);
        $herkunften = new FieldList('Herkunft');
        $herkunften->assignToTemplate($tmpl);
        $autoren = new FieldList('Autor');
        $autoren->assignToTemplate($tmpl);
        $themen = Themen::fromDatabase();
        $tmpl->assignHtml('themen', $themen->getHtml());
    }

}

?>
