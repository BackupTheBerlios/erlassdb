<?php
function quote(&$var) {
    if (is_array($var)) {
        $var = array_map('quote',$var);
    }
    else {
        $var = addslashes($var);
    }
    return $var;
}
if (!ini_get('magic_quotes_gpc')) {
    quote($_GET);
    quote($_POST);
}
?>