<?php
/**
 * Do everything nodbcms needs to load.
 */
require(ROOT . '/includes/documentParser.class.php');

if(empty($_GET['q']))
    $q = array('home_page');
else
    $q = explode('/', $_GET['q']);

new documentParser($q, ROOT.'/documents', ROOT.'/templates', ROOT.'/documents/errors');
