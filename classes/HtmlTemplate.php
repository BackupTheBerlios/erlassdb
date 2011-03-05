<?php

require_once 'Template.php';

class HtmlTemplate extends Template {

    /**
     * Loads the content of a HTML template file relative the html directory.
     * @param string $filename path to the template file
     * @return HtmlTemplate instance with the content of the template file
     * @throws Exception if $filename doesn't point to a file or reading fails
     * (plus Exception from the constructor)
     */
    public static function fromFile($filename) {
        $filename = 'html/' . $filename;
        if (!is_file($filename)) {
            throw new Exception("That's no file: " + $filename);
        }
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new Exception("Could not read template file: " + $filename);
        }
        return new self($content);
    }

    public static function text2html($text) {
        $quoteStyle = ENT_QUOTES;
        $charset = 'UTF-8';
        return htmlentities($text, $quoteStyle, $charset);
    }

    /**
     * Replaces a template tag with the given value.
     * @param string $name tag identifier in the template
     * @param string $value new value in the document
     */
    public function assign($name, $value = '') {
        parent::assign($name, self::text2html($value));
    }

    /**
     * Inserts $value as raw string without encoding as HTML.
     *
     * Example:
     * <code>
     * $t = new HtmlTemplate('<input name="\'foo\'" value="\'html:foo\'"/>');
     * $t->assign('foo', 'a&b');
     * echo $t->result();
     * /// <input name="a&amp;b" value="'html:foo'"/>
     * $t->assignHtml('foo', 'bar" checked="checked');
     * echo $t->result();
     * /// <input name="a&amp;b" value="bar" checked="checked"/>
     * </code>
     * 
     * Warning: this function allows inserting active HTML code, which can be
     * used for Cross-Site-Scripting.
     *
     * @param string $name name of the html variable
     * @param string $value HTML to interprete by the browser
     */
    public function assignHtml($name, $value) {
        parent::assign('html:' . $name, $value);
    }

    /**
     * Assigns all values of the given array to the keys of the array.
     * @param array $associativeArray of the form array( 'tag_name' => 'value' )
     */
    public function assignArray($associativeArray) {
        foreach ($associativeArray as $name => $value) {
            $this->assign($name, $value);
        }
    }

}

?>
