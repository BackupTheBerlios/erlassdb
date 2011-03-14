<?php

require_once 'HtmlTemplate.php';

/**
 * Provides access to a list of values of one field column in the database.
 *
 * @author maikel
 */
class FieldList {

    private $name;
    private $checked = array();

    /**
     * Constructs a new list from the database.
     * @param string $fieldName name of a column of the table Erlass
     */
    public function __construct($fieldName) {
        $this->name = $fieldName;
        if (isset($_POST[$fieldName]) && is_array($_POST[$fieldName])) {
            foreach ($_POST[$fieldName] as $checkedValue) {
                $this->checked[] = stripslashes($checkedValue);
            }
        }
    }

    public function assignToTemplate(HtmlTemplate $tmpl) {
        $query = 'select distinct `' . $this->name . '` from Erlass order by `'
                . $this->name . '`;';
        $result = mysql_query($query);
        while (list($value) = mysql_fetch_row($result)) {
            $li = $tmpl->addSubtemplate($this->name . 'Checkbox');
            if (in_array($value, $this->checked)) {
                $li->assignHtml('checked', '" checked="checked');
            } else {
                $li->assignHtml('checked', '');
            }
            $li->assign($this->name, $value);
            $li->assign($this->name . 'Id', $this->name . $value);
        }
    }

    public function filteredArrayOf($field) {
        $array = array();
        $query = 'select distinct `' . $field . '` from Erlass';
        if (sizeof($this->checked) > 0) {
            $conditions = array();
            foreach ($this->checked as $checkedValue) {
                $conditions[] = '`' . $this->name . '`="' . $checkedValue . '"';
            }
            $query .= ' where ' . implode(' or ', $conditions);
        }
        $result = mysql_query($query);
        while (list($value) = mysql_fetch_row($result)) {
            $array[] = $field . $value;
        }
        return $array;
    }

}

?>
