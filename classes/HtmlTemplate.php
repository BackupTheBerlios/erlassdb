<?php

require_once 'Template.php';

class HtmlTemplate {

    /**
     * Loads the content of an HTML template file.
     *
     * @param string $filename path to the template file relativ to the html
     *  directory
     * @return HtmlTemplate instance with the content of the template file
     * @throws Exception see Template
     */
    public static function fromFile($filename) {
        $tmpl = Template::fromFile('html/' . $filename);
        return new self($tmpl);
    }

    public static function text2html($text) {
        $quoteStyle = ENT_QUOTES;
        $charset = 'UTF-8';
        return htmlentities($text, $quoteStyle, $charset);
    }

    private $template;

    /**
     * Stores $tmpl to wrap around.
     * @param Template to wrap around
     * @throws Exception see Template class
     */
    public function  __construct(Template $tmpl) {
        $this->template = $tmpl;
    }

    /**
     * Replaces a template tag with the given value.
     * @param string $name tag identifier in the template
     * @param string $value new value in the document
     */
    public function assign($name, $value = '') {
        $this->template->assign($name, self::text2html($value));
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
        $this->template->assign('html:' . $name, $value);
    }

    /**
     * Encodes given text in HTML an add &alt;br /&gt; tags at newlines.
     *
     * @param string $name name of the br variable
     * @param string $value text with newlines
     */
    public function assignText($name, $value) {
        $encoded = nl2br(self::text2html($value));
        $this->template->assign('br:' . $name, $encoded);
    }

    /**
     * See Template.
     * @param string $name see Template
     * @return HtmlTemplate see Template
     */
    public function addSubtemplate($name) {
        return new self($this->template->addSubtemplate($name));
    }

    /**
     * See Template.
     * @return string see Template
     */
    public function result() {
        return $this->template->result();
    }

}

?>
