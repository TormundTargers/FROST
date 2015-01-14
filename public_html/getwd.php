<?php
/**
 * @return string location of the directory that this file is stored in.
 */
function getwd() {
    return (str_replace($_SERVER["DOCUMENT_ROOT"],'', str_replace('\\','/',__DIR__ ) ));
}
