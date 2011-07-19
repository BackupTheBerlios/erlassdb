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
        $listNames = array('Kategorie', 'Herkunft', 'Autor');
        $kategorieList = new FieldList('Kategorie');
        $kategorieList->assignToTemplate($form->addSubtemplate('CheckboxList'));
        $kategorieList = new FieldList('Herkunft');
        $kategorieList->assignToTemplate($form->addSubtemplate('CheckboxList'));
        $kategorieList = new FieldList('Autor');
        $kategorieList->assignToTemplate($form->addSubtemplate('DropdownList'));
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
            $start = Erlass::standardizeDate($this->data['periodStart']);
            $conditions[] = 'Erlass.Datum>="' . $start . '"';
        }
        if ($this->data['periodEnd']) {
            $end = Erlass::standardizeDate($this->data['periodEnd']);
            $conditions[] = 'Erlass.Datum<="' . $end . '"';
        }
        if ($this->data['Aktenzeichen']) {
            $conditions[] =
                    'Erlass.Aktenzeichen="' . $this->data['Aktenzeichen'] . '"';
        }
        if (sizeof($this->themen) > 0) {
            $themaConditions = array();
            foreach ($this->themen as $thema) {
                if ($thema == '') {
                    continue;
                }
                $themaConditions[] = 'betrifft.Thema="' . $thema . '"';
            }
            if (sizeof($themaConditions) > 0) {
                $conditions[] = '(' . implode(' or ', $themaConditions) . ')';
            }
        }
        if (sizeof($conditions) < 1) {
            return null;
        }
        $query = 'select Erlass.id id, Erlass.Datum Datum,'
                . ' Erlass.Betreff Betreff, Erlass.Status Status from Erlass'
                . ' left join betrifft on Erlass.id=betrifft.Erlass'
                . ' where ' . implode(' and ', $conditions)
                . ' group by id'
                . ' order by Datum;';
        return mysql_query($query);
    }

}

?>
