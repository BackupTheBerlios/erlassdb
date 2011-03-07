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
        if(!isset($_POST['themen'])) {
            return;
        }
        $themen = $_POST['themen'];
        if (!is_array($themen)) {
            return;
        }
        $queryParts = array();
        foreach ($themen as $i => $thema) {
            $queryParts[] = 'Name="' . $thema . '"';
        }
        $query = 'delete from Thema where ' . implode(' or ', $queryParts);
        mysql_query($query);
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

    public function getHtml($parent = self::ROOT_NAME) {
        $tmpl = HtmlTemplate::fromFile('themen.html');
        $tmpl->assign('parent', $parent);
        $childs = &$this->getChildsOf($parent);
        foreach ($childs as $child) {
            $sub = $tmpl->addSubtemplate('thema');
            $sub->assign('id', 'thema' . $child);
            $sub->assign('Name', $child);
            $sub->assignHtml('childs', $this->getHtml($child));
        }
        return $tmpl->result();
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
