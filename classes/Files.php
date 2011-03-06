<?php

/**
 * Provides uploadable files for one Erlass.
 *
 * @author maikel
 */
class Files {

    const INDEX = 'doc';
    const DIR = 'files/';
    
    private static $extensions = array('rtf', 'doc', 'docx', 'pdf');

    private $id;

    public function __construct($id) {
        $this->id = (int) $id;
    }

    public function upload() {
        if (count($_FILES) != 1) {
            return;
        }
        if (!isset($_FILES[self::INDEX])) {
            return;
        }
        $fileInfo = $_FILES[self::INDEX];
        if ($fileInfo['error'] != UPLOAD_ERR_OK) {
            return;
        }
        $tmp_name = $fileInfo['tmp_name'];
        if (!is_uploaded_file($tmp_name)) {
            return;
        }
        $name = $fileInfo['name'];
        $extension = null;
        foreach (self::$extensions as $ext) {
            if ($ext == substr($name, -strlen($ext))) {
                $extension = $ext;
                break;
            }
        }
        if (!$extension) {
            return;
        }
        move_uploaded_file($tmp_name, self::DIR . $this->id . '.' . $extension);
    }

}
?>
