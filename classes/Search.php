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
    private $themen = array();
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
        if (isset($_POST['themen']) && is_array($_POST['themen'])) {
            $this->themen = $_POST['themen'];
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
        $conditions = array();
        if ($this->data['extended']) {
            $conditions[] = 'match(Erlass.Betreff, Erlass.Dokument) against ("'
                    . $this->data['extended'] . '" in boolean mode)';
        }
        foreach (self::$listNames as $listName) {
            $list = new FieldList($listName);
            $list->putConditionsInto($conditions);
        }
        if ($this->data['periodStart']) {
            $conditions[] =
                    'Erlass.Datum>="' . $this->data['periodStart'] . '"';
        }
        if ($this->data['periodEnd']) {
            $conditions[] = 'Erlass.Datum<="' . $this->data['periodEnd'] . '"';
        }
        if ($this->data['Aktenzeichen']) {
            $conditions[] =
                    'Erlass.Aktenzeichen="' . $this->data['Aktenzeichen'] . '"';
        }
        if (sizeof($this->themen) > 0) {
            $themaConditions = array();
            foreach ($this->themen as $thema) {
                $themaConditions[] = 'betrifft.Thema="' . $thema . '"';
            }
            $conditions[] = '(' . implode(' or ', $themaConditions) . ')';
        }
        if (sizeof($conditions) < 1) {
            return null;
        }
        $query = 'select Erlass.id id, Erlass.Betreff Betreff from Erlass'
                . ' left join betrifft on Erlass.id=betrifft.Erlass'
                . ' where ' . implode(' and ', $conditions)
                . ' group by id'
                . ' order by Datum;';
        return mysql_query($query);
    }

}

?>
