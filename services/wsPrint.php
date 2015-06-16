<?php
error_reporting(E_ERROR);
require_once "../config/config.php";
require_once LIBDIR."utils.class.php";
require_once LIBDIR."print.class.php";

$server = new nusoap_server; 
$server->soap_defencoding = 'UTF-8';
$server->configureWSDL('wsPrint', SERVICE_URL);

$server->wsdl->addComplexType('file','complexType','struct','all','',Array(
    "oldname"=>Array("name"=>"filename","type"=>"xsd:string"),
    "newname"=>Array("name"=>"filename","type"=>"xsd:string"),
    "file"=>Array("name"=>"file","type"=>"xsd:string")
));
$server->wsdl->addComplexType(
    'files',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    Array(
        Array(
            "name"=>"elencofile",
            "type"=>"tns:file"
        )
    ),
    Array( 
        Array( 
            "ref" => "SOAP-ENC:arrayType",
            "wsdl:arrayType" => "tns:file[]"
        )
    ),
    "tns:file"
);
$server->wsdl->addComplexType(
    'strArray','complexType','array','',
    'SOAP-ENC:Array',
    array(),
    array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')));

$server->wsdl->schemaTargetNamespace = 'urn:wsprint';
/******************************************************************************/
/*                                                                            */
/******************************************************************************/
$server->register('convertiFiles',
    Array(
        "elencofile"=>"tns:files",
        "data" => "xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "messages"=>"tns:strArray",
        "errors" =>"tns:strArray" ,
        "files"=>"tns:files"
    ),
    'urn:wsprint',
    'urn:praticaweb#convertFiles',
    'rpc',
    'encoded',
    'Conversione dei file docx/odt in PDF'
);

$result = Array(
    "success"=>0,
    "message"=>Array(),
    "errors"=>Array(),
    "files"=>Array()
);
$server->register('creaFiles',
    Array(
        "elencofile"=>"tns:files",
        "data" => "xsd:string"
    ),
    Array(
        "success"=>"xsd:int",
        "messages"=>"tns:strArray",
        "errors" =>"tns:strArray" ,
        "files"=>"tns:files"
    ),
    'urn:wsprint',
    'urn:praticaweb#createFiles',
    'rpc',
    'encoded',
    'Conversione dei file docx/odt in PDF'
);

$result = Array(
    "success"=>0,
    "message"=>Array(),
    "errors"=>Array(),
    "files"=>Array()
);
function convertiFiles($files,$data=Array()){
    utils::debug("convertiFiles.debug","START CONVERSIONE");
    $result = Array("success"=>0,"messages"=>Array(),"errors"=>Array(),"files"=>Array());
    if (!is_array($files) || count($files)==0) {
        $result["errors"][]="Nessun File in Input";
        return $result;
    }
    $create=0;
    if (is_array($data) && $data) {
        $r = gwPrint::getData($data);
        $d=$r["data"];
        $create=1;
    }
    for($i=0;$i<count($files);$i++){
        $file = $files[$i];
        $b64Testo=$file["file"];
        $testo =  base64_decode($b64Testo,TRUE);
        if ($testo===FALSE){
            $result["errors"][]=sprintf("If File %s non è codificato in base 64",$file["oldname"]);
        }
        else{
            if ($create==1){
                $infoFile=pathinfo($file["oldname"]);
                $r = gwPrint::createDoc($testo,$infoFile["extension"],$d);
                if ($r["message"]) $result["messages"][]=$r["message"];
                if ($r["errors"]) $result["errors"][]=$r["errors"];
                if ($r["file"] && $r["size"]>0) {
                    $testo=$r["file"];
                }
            }
            $infoFile=pathinfo($file["oldname"]);
            $r = gwPrint::convertToPDF($testo,$infoFile["extension"]);
            $result["success"]=(int)($result["success"] || $r["success"]);
            if ($r["message"]) $result["messages"][]=$r["message"];
            if ($r["errors"]) $result["errors"][]=$r["errors"];
            if ($r["file"]) {
                $file["file"]=$r["file"];
                
                $result["files"][]=$file;
            }
        }
    }
    utils::debug('RESULT-CONVERT.debug', $result);
    return $result;
}
function creaFiles($files,$data=Array()){
    $result = Array("success"=>0,"messages"=>Array(),"errors"=>Array(),"files"=>Array());
    if (!is_array($files) || count($files)==0) {
        $result["errors"][]="Nessun File in Input";
        return $result;
    }
    $r = gwPrint::getData($data);
    if($r["success"]==1) $d=$r["data"];
    else{
        $result["errors"][]=$r["message"];
        return $result;
    }
    for($i=0;$i<count($files);$i++){
        $file = $files[$i];
        $b64Testo=$file["file"];
        $testo =  base64_decode($b64Testo,TRUE);
        if ($testo===FALSE){
            $result["errors"][]=sprintf("If File %s non è codificato in base 64",$file["oldname"]);
        }
        else{
            $infoFile=pathinfo($file["oldname"]);
            $r = gwPrint::createDoc($testo,$infoFile["extension"],$d);
            $result["success"]=(int)($result["success"] || $r["success"]);
            if ($r["message"]) $result["messages"][]=$r["message"];
            if ($r["errors"]) $result["errors"][]=$r["errors"];
            if ($r["file"] && $r["size"]>0) {
                $file["file"]=$r["file"];
                $result["files"][]=$file;
            }
        }
    }
    return $result;
}

$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>

