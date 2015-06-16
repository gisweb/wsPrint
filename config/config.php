<?php

$info = pathinfo(__FILE__);
define('DIR',dirname($info['dirname']).DIRECTORY_SEPARATOR);
define('LIBDIR',DIR."lib".DIRECTORY_SEPARATOR);
define('DBGDIR',DIR."debug".DIRECTORY_SEPARATOR);
define('SERVICE_URL',"http://webservice.gisweb.it/wsprint/wsPrint.php?wsdl");
define("TMPDIR",DIR.'tmp/');
define('LIBREOFFICEDIR',"/opt/libreoffice3.6/program/");

ini_set('memory_limit','1024M');
require_once LIBDIR."utils.class.php";
require_once LIBDIR."print.class.php";
require_once LIBDIR."nusoap".DIRECTORY_SEPARATOR."nusoap.php";


?>