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
        move_uploaded_file($tmp_name, $this->pathTo($extension));
    }

    public function assignToTmpl(HtmlTemplate $erlassTmpl) {
        $avail = $this->availExts();
        if (sizeof($avail) < 1) {
            return;
        }
        $tmpl = $erlassTmpl->addSubtemplate('downloadMenu');
        foreach ($avail as $ext) {
            $item = $tmpl->addSubtemplate('downloadItem');
            $item->assign('ext', $ext);
        }
    }

    public function send($ext) {
        if (!in_array($ext, self::$extensions)) {
            exit;
        }
        $path = $this->pathTo($ext);
        $mtime = filemtime($realpath);
        header("Content-type: application/force-download");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="Erlass_'
                . basename($path) . '"; modification-date="'
                . date('r', $mtime) . '";');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    private function pathTo($extension) {
        return self::DIR . $this->id . '.' . $extension;
    }

    private function availExts() {
        $avail = array();
        foreach (self::$extensions as $ext) {
            $path = $this->pathTo($ext);
            if (is_readable($path)) {
                $avail[] = $ext;
            }
        }
        return $avail;
    }

}

?>
