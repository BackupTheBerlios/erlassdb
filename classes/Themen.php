<?php

/**
 * Manages Themen.
 *
 * @author maikel
 */
class Themen {
    const ROOT_NAME = '';

    public static function fromDatabase() {
        $themen = new Themen();
        $query = 'select parent, Name from Thema order by parent, Name;';
        $result = mysql_query($query);
        while (list($parent, $child) = mysql_fetch_row($result)) {
            $themen->add($parent, $child);
        }
        return $themen;
    }

    public static function insertFromPost() {
        if (!isset($_POST['thema']) || !isset($_POST['parent'])) {
            return;
        }
        $thema = $_POST['thema'];
        if (!$thema) {
            return;
        }
        $parent = $_POST['parent'];
        $query = 'insert into Thema (parent, Name) values'
                . ' ("' . $parent . '", "' . $thema . '");';
        mysql_query($query);
        if (mysql_affected_rows() == 1) {
            return;
        }
        $query = 'update Thema set parent="' . $parent . '"'
                . ' where Name="' . $thema . '";';
        mysql_query($query);
    }

    public static function deleteFromPost() {
        $themen = self::listFromPost();
        if (sizeof($themen) < 1) {
            return;
        }
        $queryParts = array();
        foreach ($themen as $i => $thema) {
            $queryParts[] = 'Name="' . $thema . '"';
        }
        $query = 'delete from Thema where ' . implode(' or ', $queryParts);
        mysql_query($query);
    }

    public static function listOf($erlassId) {
        $list = array();
        if ($erlassId > 1) {
            $query = 'select Thema from betrifft where Erlass="'
                    . (int) $erlassId . '";';
            $result = mysql_query($query);
            while (list($thema) = mysql_fetch_row($result)) {
                $list[] = $thema;
            }
        }
        return $list;
    }

    public static function setFromPostFor($erlassId) {
        $themen = self::listFromPost();
        $query = 'delete from betrifft where Erlass="' . $erlassId . '";';
        if (sizeof($themen) < 1) {
            return;
        }
        foreach ($themen as $thema) {
            $query = 'insert into betrifft (Erlass, Thema) values ('
                    . '"' . $erlassId . '", "' . $thema . '");';
            mysql_query($query);
        }
    }

    private static function listFromPost() {
        if (!isset($_POST['themen'])) {
            return array();
        }
        $themen = $_POST['themen'];
        if (!is_array($themen)) {
            exit;
        }
        return $themen;
    }

    private $themen = array(self::ROOT_NAME => array());

    public function add($parent, $child) {
        if (!$this->contains($parent)) {
            $this->themen[self::ROOT_NAME][] = $parent;
        }
        $childs = &$this->getChildsOf($parent);
        $childs[] = $child;
    }

    /**
     * Builds a tree containing all Thema entries.
     *
     * <code>
     * $themen = new Themen();
     * echo sizeof($themen->getTree()); /// 0
     * $themen->add('', 'Topic A');
     * $themen->add('', 'Topic B');
     * $themen->add('Topic A', 'Topic C');
     * $tree = $themen->getTree();
     * echo sizeof($tree); /// 2
     * echo sizeof($tree['Topic A']); /// 1
     * echo sizeof($tree['Topic B']); /// 0
     * </code>
     *
     * @param string $parent name of the parent
     * @return array deep associative array
     */
    private function &getTree($parent = self::ROOT_NAME) {
        // TODO: remove if not used
        $tree = array();
        $childs = &$this->getChildsOf($parent);
        foreach ($childs as $child) {
            $tree[$child] = &$this->getTree($child);
        }
        return $tree;
    }

    public function getHtml($given = array(), $parent = self::ROOT_NAME) {
        $tmpl = HtmlTemplate::fromFile('themen.html');
        $tmpl->assign('parent', $parent);
        $childs = &$this->getChildsOf($parent);
        foreach ($childs as $child) {
            $sub = $tmpl->addSubtemplate('thema');
            $checked = '';
            if (in_array($child, $given)) {
                $checked = '" checked="checked';
            }
            $sub->assign('id', 'thema' . $child);
            $sub->assign('Name', $child);
            $sub->assignHtml('checked', $checked);
            $sub->assignHtml('childs', $this->getHtml(&$given, $child));
        }
        return $tmpl->result();
    }

    public function getHtmlWithPost() {
        $given = array();
        if (isset($_POST['themen']) && is_array($_POST['themen'])) {
            foreach ($_POST['themen'] as $thema) {
                $given[] = stripslashes($thema);
            }
        }
        return $this->getHtml($given);
    }

    private function &getChildsOf($parent) {
        if (!isset($this->themen[$parent])) {
            $this->themen[$parent] = array();
        }
        return $this->themen[$parent];
    }

    private function contains($name) {
        if ($name == self::ROOT_NAME) {
            return true;
        }
        foreach ($this->themen as $parent => $childs) {
            if (in_array($name, $childs)) {
                return true;
            }
        }
        return false;
    }

}

?>
