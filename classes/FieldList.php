<?php

require_once 'HtmlTemplate.php';

/**
 * Provides access to a list of values of one field column in the database.
 *
 * @author maikel
 */
class FieldList {

    private $name;
    private $result;
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
        $query = 'select distinct `' . $fieldName . '` from Erlass order by `'
                . $fieldName . '`;';
        $this->result = mysql_query($query);
    }

    public function assignToTemplate(HtmlTemplate $tmpl) {
        while (list($value) = mysql_fetch_row($this->result)) {
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

}

?>
