<?php
/*
 * This file is part of uBook - a website to buy and sell books.
 * It's licensed under the GNU General Public License.
 * Copyright (C) 2010 Maikel Linke
*/

/**
 * Substitutes special template tags with given content.
 *
 * This is just another template class for HTML/XML templates. BUT it is the
 * result of studying several other template engines and tries to merge all
 * advantages. This text explains you two things:
 *
 * 1. How To Use - some examples
 * 2. Why This Way - good reasons for the syntax and the API
 *
 * You can download this class as archive with documentation:
 * - {@link http://ubook.berlios.de/template/Template.zip}
 * Or you can directly browse in the source code at BerliOS:
 * - {@link http://svn.berlios.de/svnroot/repos/ubook/branches/template/}
 * - {@link http://svn.berlios.de/viewvc/ubook/branches/template/}
 *
 * Feedback is very welcome: ubook-info@lists.berlios.de
 *
 * <b>How To Use</b>
 *
 * Begin to create pure HTML. It could be like this:
 * <pre>
 *     &lt;p&gt;Hello, this is normal text. But it is also possible to insert
 *        variable text here. For example the time: 'time'.&lt;/p&gt;
 *     &lt;p&gt;Okay, this is easy. It's just a string replacement. But now follows
 *        a subtemplate. It allows you to repeat some code over and over again,
 *        everytime with different content. For example a list of names:&lt;/p&gt;
 *     &lt;ul&gt;
 *         &lt;!-- BEGIN item --&gt;
 *         &lt;li&gt;'name'&lt;/li&gt;
 *         &lt;!-- END item --&gt;
 *     &lt;/ul&gt;
 * </pre>
 *
 * Now we have the template. Let's write code to fill it with data.
 *
 * <code>
 *     $template = new Template($theHtmlCodeAsString);
 *     // or
 *     $template = Template::fromFile($filenameOfTemplate);
 *
 *     // replace a variable with a value
 *     $template->assign('time', time());
 *
 *     // use the subtemplate to generate three list items
 *     $sub = $template->addSubtemplate('item');
 *     $sub->assign('name', 'Andrea');
 *     $sub = $template->addSubtemplate('item');
 *     $sub->assign('name', 'Andy');
 *     $sub = $template->addSubtemplate('item');
 *     $sub->assign('name', 'Anna');
 *
 *     // ready...
 *     echo $template->result();
 * </code>
 *
 * <b>Why This Way</b>
 *
 * This class was written for a simple project. It's just one class, not a whole
 * framework with caching and so on. It supports everything, that a simple
 * template engine should support. But not more.
 * Keep it simple.
 *
 * <i>Why do you use single quotes for variables?</i>
 *
 * There are three reasons:
 * <ol>
 * <li> It's easy to write on most keyboards.</li>
 * <li> Valid HTML will stay valid, when you insert single quotes in the text.
 * It's also no problem to insert single quotes into a double quoted parameter
 * value.</li>
 * <li> You can prevent unintended replacements by encoding the user data via
 * htmlentities() before inserting into the template (see example below).</li>
 * </ol>
 *
 * Besides, if you like another syntax, just change the constants in the script.
 * It's free software.
 *
 * <i>Why don't you recommend the assignArray-method?</i>
 *
 * It's no good idea to store everything in an array. The more complex your
 * application becomes, the harder it is to remember all the array structures.
 * Additionally some IDEs (e.g. NetBeans) support the correct renaming of
 * variables, but not of array indices, of course.
 *
 * Assigning one variable to the template is not more complicated than a new
 * entry in an array.
 *
 * And most <i>important</i>:
 * Perhaps you have a full array of data from mysql_fetch_array(). But you
 * should not insert this into a template. Normally you have more data in there
 * than you want to present the user. And you have to encode the user data to
 * prevent code injection. So depending on your application, perhaps you can do
 * something like this:
 * <code>
 *     $arr = mysql_fetch_array($result);
 *     foreach ($arr as $key => $value) {
 *         $encoded = htmlentities($value, ENT_QUOTES, 'UTF-8');
 *         $template->assign($key, $encoded);
 *     }
 * </code>
 *
 * But this depends on your application.
 *
 * <i>Wouldn't it be nice to define conditions for subtemplates or provide
 * something like a for-loop syntax?</i>
 *
 * No. It's a good idea to seperate PHP and HTML cleanly. So you have all your
 * application logic in your PHP code. Your template files only define, what can
 * be displayed and how. If you really want to script within your HTML, then you
 * can just use PHP itself:
 * - {@link http://articles.sitepoint.com/article/beyond-template-engine}
 * - {@link http://php-coding-standard.de/php_template_engine.php} (german)
 *
 * <i>What is the other way?</i>
 *
 * Other interesting template classes:
 * - {@link http://articles.sitepoint.com/article/beyond-template-engine}
 * - {@link http://template.ecoware.de/en/}
 * - {@link http://www.phpbar.de/w/P.E.T.} (german)
 * - {@link http://kuerbis.org/asap/article/12/}
 *
 * @author Maikel Linke (ubook-info@lists.berlios.de)
 * @version 2010-05-24
*/
class Template {

    /**
     * The beginning of a tag to substitute. Tag example: 'foo'
     */
    const TAG_START = "'";
    /**
     * The beginning of a tag to substitute. Tag example: 'bar'
     */
    const TAG_END = "'";
    /**
     * Regular expression for subtemplates.
     *
     * It uses the following pattern modifiers:
     * - i - caseless
     * - m - multiline
     * - s - dotall
     */
    const SUB_PATTERN = '/<!--\s*BEGIN\s+([a-z0-9_\-]+)\s*-->(.*?)<!--\s*END\s+\\1\s*-->/ims';

    private $content;
    private $assignments = array();
    private $subTemplates = array();
    private $subTemplateKeys = array();
    private $subTemplateSources = array();

    /**
     * Loads the content of a template file.
     * @param string $filename path to the template file
     * @return Template instance with the content of the template file
     * @throws Exception if $filename doesn't point to a file or reading fails
     * (plus Exception from the constructor)
     */
    public static function fromFile($filename) {
        if (!is_file($filename)) {
            throw new Exception("That's no file: " + $filename);
        }
        $content = file_get_contents($filename);
        if ($content === false) {
            throw new Exception("Could not read template file: " + $filename);
        }
        return new self($content);
    }

    /**
     * Creates a new instance with given string as source (not filename).
     * @param string $content containing the code of the template
     * @throws Exception if there are ambiguous subtemplates
     */
    public function __construct($content) {
        $this->content = $content;
        $this->initSubtemplates();
    }

    /**
     * Replaces a template tag with the given value.
     * @param string $name tag identifier in the template
     * @param string $value new value in the document
     */
    public function assign($name, $value = '') {
        $this->assignments[(string) $name] = $value;
    }

    /**
     * Assigns all values of the given array to the keys of the array.
     * <b>Warning:</b> This function is seldom usefull. Don't assign arrays from
     * your database without encoding the data. See documentation above.
     * @param array $associativeArray of the form array( 'tag_name' => 'value' )
     */
    public function assignArray($associativeArray) {
        $this->assignments = array_merge($this->assignments, $associativeArray);
    }

    /**
     * Subtemplates are defined once and will appear in the result zero or more
     * times. Varying content and also sub-subtemplates are possible.
     * @param string $name identifyer of the subtemplate
     * @return Template a full template instance to customize this subtemplate
     * @throws Exception if their is no such subtemplate
     */
    public function addSubtemplate($name) {
        if (!isset($this->subTemplates[$name])) {
            throw new Exception('This subtemplate is not available: ' . $name);
        }
        $source = $this->subTemplateSources[$name];
        if (is_a($source, 'Template')) {
            $sub = clone $source;
        } else {
            $sub = new self($source);
            $this->subTemplateSources[$name] = clone $sub;
        }
        $this->subTemplates[$name][] = $sub;
        return $sub;
    }

    /**
     * Returns the parsed result.
     * @return string actual result with all given replacements
     */
    public function result() {
        $c = $this->assignSubtemplates($this->content);
        $c = $this->assignAtoms($c);
        return $c;
    }

    /**
     * Searches for subtemplates and stores them.
     * @throws Exception if there are ambiguous subtemplates
     */
    private function initSubtemplates() {
        preg_match_all(self::SUB_PATTERN, $this->content, $matches);
        for ($i = 0; $i < sizeof($matches[0]); $i++) {
            $key = $matches[0][$i];
            $name = $matches[1][$i];
            $substring = $matches[2][$i];
            if (isset($this->subTemplates[$name])) {
                throw new Exception('This template contains more than one'
                        . ' subtemplate with one name: ' + $name);
            }
            $this->subTemplateKeys[$name] = $key;
            $this->subTemplateSources[$name] = $substring;
            $this->subTemplates[$name] = array();
        }
    }

    private function assignAtoms($content) {
        foreach ($this->assignments as $name => $value) {
            $search = self::TAG_START . $name . self::TAG_END;
            $content = str_replace($search, $value, $content);
        }
        return $content;
    }

    private function assignSubtemplates($content) {
        foreach ($this->subTemplateKeys as $name => $key) {
            $replace = '';
            foreach ($this->subTemplates[$name] as $n => $sub) {
                $replace .= $sub->result();
            }
            $content = str_replace($key, $replace, $content);
        }
        return $content;
    }

}
?>
