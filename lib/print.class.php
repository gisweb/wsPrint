<?php
require_once "../config/config.php";
class gwPrint{
    static function writeFile($name,$t){
        if (!$t){
            utils::debug("WRITEFILE.debug", "Nessu dato da scivere sul file $name");
            return 0;
        }
        $f=fopen($name,'w');
        if ($f===FALSE){
            utils::debug("WRITEFILE.debug", "Impossibile aprire il file $name");
            return 0;
        }
        $res = 0;
        $msg = sprintf("Errore nella scrittura del file %s",$name);
        if (fwrite($f,$t)) {
            $res = 1;
            $msg = sprintf("File %s scritto correttamente",$name);
        }
        utils::debug("WRITEFILE.debug", $msg);
        fclose($f);
        return $res;
    }
    static function readFile($name){
        $res = Array("success"=>0,"message"=>"","size"=>0,"file"=>"");
        if (file_exists($name)){
            $f=fopen($name,'r');
            $t = fread($f,filesize($name));
            $res["success"]=1;
            if (strlen($t)>0) {
                $msg = sprintf("File %s letto correttamente con dimensione %s",$name,(string)(filesize($name)));
                $res["size"]=filesize($name);
                $res["file"]=$t;
            }
            else{
                $msg = sprintf("File %s letto correttamente ma vuoto",$name);
            }
            fclose($f);
        }
        else{
            $msg = sprintf("File %s non esistente",$name);
        }
        $res["message"]=$msg;
        utils::debug("READFILE.debug", $msg);
        return $res;
    }
    static function convertToPDF($testo,$ext="docx"){
        $res = Array("success"=>0,"message"=>"","file"=>"");
        $name=sprintf("%s%s.%s",TMPDIR,utils::rand_str(12),$ext);
        utils::debug("CONVERT.debug","INIZIO PROCEDURA DI CONVERSIONE DEL FILE $name");
        if(self::writeFile($name, $testo)){
            $cmd=sprintf("export HOME=/tmp &&  %ssoffice --headless --invisible --nologo --convert-to pdf:writer_pdf_Export  %s --outdir /%s",LIBREOFFICEDIR,$name,TMPDIR);
            utils::debug("CONVERT.debug",$cmd);
            $r=shell_exec($cmd);
            $msg1="Overwriting:";// $dirname/$filename";
            $msg2="convert";// $dirname/$filename";
            if (stripos($r,$msg1)===FALSE and stripos($r,$msg2)===FALSE){
                utils::debug("ERROR-CONVERT.debug",$r);
                $msg=$r;
            }
            else{
                $t=self::readFile($name);
                if ($t["size"]>0){
                    $res["success"]=1;
                    $msg="";
                    $res["file"]=base64_encode($t["file"]);
                }
                else{
                    $msg=$t["message"];
                }
            }
        }
        else{
            $msg = sprintf("Impossibile scrivere il file %s",$name);
        }
        utils::debug("CONVERT.debug",$msg);
        $res["message"]=$msg;
        utils::debug("CONVERT.debug","FINE PROCEDURA DI CONVERSIONE DEL FILE $name");
        return $res;
    }
    private static function mergeFields($T,$data){
        foreach($data as $key=>$value){
            if(is_array($value)){
                    $T->MergeBlock($key, $value);
            }
            else{
                    $T->MergeField($key, $value);
            }
        }
    }

    static function getData($b64Data){
        $res = Array("success"=>0,"message"=>"","data"=>Array());
        $jsonData =  base64_decode($b64Data,TRUE);
        if($jsonData===FALSE){
            $res["message"]="Dati non codificati in base64";
            return $res;
        }
        $data = json_decode($jsonData,true);
        if ($data === NULL) {
            $res["message"]="Errore nella decodifica JSON";
            return $res;
        }
        if(!$data || ! is_array($data)) {
            $res["message"]="Nessun dato passato alla procedura";
            return $res;
        }
        $res["success"]=1;
        $res["data"]=$data;
        return $data;
    }
    static function createDoc($t,$data,$ext="docx"){
        $fName=sprintf("%s.%s",utils::rand_str(12),$ext);
        utils::debug("CONVERT.debug","INIZIO PROCEDURA DI CREAZIONE $ext DEL FILE $fName");
        $res = Array("success"=>0,"message"=>"","file"=>"","size"=>0);
        $name = sprintf("%s%s",TMPDIR,$fName);
        $modelName=sprintf("%sMODELLO-%s",TMPDIR,$bName);
        utils::debug("CONVERT.debug",$data);
        if(self::writeFile($modelName, $t)){
            $data["oggi"]=date('d/m/Y');
            $TBS->LoadTemplate($modelName);
            $TBS->SetOption('noerr',true);
            self::mergeFields($TBS,$data);
            $HeaderAndFooter = $TBS->PlugIn(OPENTBS_GET_HEADERS_FOOTERS);
            for($i=0;$i<count($HeaderAndFooter);$i++){
                $f=$HeaderAndFooter[$i];
                $TBS->LoadTemplate($f);
                self::mergeFields($TBS,$data);
            }
            $TBS->Show(OPENTBS_FILE,$name);
            $t = self::readFile($name);
            if ($t["size"]>0){
                $res["success"]=1;
                $msg="";
                $res["file"]=$t["file"];
                $res["size"]=$t["size"];
            }
            else{
                $msg=$t["message"];
            }
            
        }
        else{
            $msg = sprintf("Impossibile scrivere il modello %s",$modelName);
        }
        utils::debug("CREATE.debug",$msg);
        $res["message"]=$msg;
        utils::debug("CONVERT.debug","FINE PROCEDURA DI CREAZIONE $ext DEL FILE $fName");
        return $res;
    }
    
}

?>