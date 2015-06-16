<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
require_once "../config/config.php";
$client = new nusoap_client(SERVICE_URL, true);
$err = $client->getError();
$f = fopen("ciefavorevole.docx",'r');
$t = fread($f,filesize("ciefavorevole.docx"));
fclose($f);
$testo=  base64_encode($t);
$data=Array("elencofile"=>Array(Array("oldname"=>"ciefavorevole.docx","newname"=>"ciefavorevole.pdf","file"=>$testo)),"data"=>Array());
$result=$client->call("convertiFiles",$data);
echo "<pre>";print_r($result);
?>