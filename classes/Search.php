<?php

require_once 'HtmlTemplate.php';

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
        $themen = Themen::fromDatabase();
        $tmpl->assignHtml('themen', $themen->getHtml());
    }

}

?>
