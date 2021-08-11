<?php 
namespace gspanarin\IrbisConnector;

class Irbis64Record{
    public $fields;
    public $mfn;
    public $status;
    public $ver;

    function __destruct() {}
    function __construct() {}
		
    public static function read_record($recordString){
        $rec = new irbis64_record();
        $ret = explode(chr(31), trim($recordString)); //НАВЕРНО ТАК ПРАВИЛЬНЕЕ
        $fieldsCount = count($ret);
        $mfn_status = [];
        preg_match ("/(\d+?)#(.*?)/U", $ret[0], $mfn_status); 
        $rec->mfn = $mfn_status[1]; 
        $rec->status = $mfn_status[2]; 
        $ver = [];
        preg_match ("/(\d+?)#(.*?)/U", $ret[2], $ver); 
        $rec->ver = $ver[2]; 
        for ($i = 2; $i < $fieldsCount; $i++) {
            if ($ret[$i]!=''){	
                $matches =[];
                preg_match("/(\d+?)#(.*?)/U", $ret[$i], $matches);
                $field_num = (int)$matches[1];
                $field_val = $matches[2];
                if ($field_num!='') {
                    if (mb_substr($field_val,0,1) != '^'){
                        $field_val = '^'.chr(29).$field_val;
                    } 
                    $prefield = explode('^', $field_val);
                    $prefields = [];
                    foreach ($prefield as $val){
                        if (mb_substr($val,1)!=''){
                           $prefields[mb_substr($val,0,1)] = mb_substr($val,1);
                        }
                    }
                    $rec->fields[$field_num][] = $prefields;
                }
            }
        }
        return $rec;
    } 

    /**
    * Серелизуем данные в строку
    **/
    function serialize(){
        $str = "";
        $str .= $this->mfn.'#'.$this->status;
        $str .= chr(30).chr(31);
        $str .= '0#'.$this->ver;
        $str .= chr(30).chr(31);
        /*Массив по всем полям*/
        foreach ($this->fields as $fieldsnum => $fieldsvalue){
            /*массив по повторениям поля*/
            if (isset($fieldsvalue) && is_array($fieldsvalue))
            foreach ($fieldsvalue as $field){
                if (is_array($field)){
                    /*массив по подполям*/
                    $str .= $fieldsnum.'#';
                    foreach ($field as $prefieldkey => $prefildvalue){
                        if ($prefieldkey==chr(29)){
                            $str .= $prefildvalue;
                        } else {
                            $str .= '^'.$prefieldkey.$prefildvalue;
                        }
                    }
                    $str .= chr(31);
                } else {
                    $str .= $fieldsnum.'#'.$field;
                    $str .= chr(31);
                }
            }
        }
        return $str;
    }
    
    
    function serialize_field($field){
        $str = "";
        foreach ($field as $fieldsnum => $fieldsvalue){
            /*массив по повторениям поля*/
            if (isset($fieldsvalue) && is_array($fieldsvalue))
            foreach ($fieldsvalue as $field){
                if (is_array($field)){
                    /*массив по подполям*/
                    $str .= $fieldsnum.'#';
                    foreach ($field as $prefieldkey => $prefildvalue){
                        if ($prefildvalue=='*'){
                            $str .= $prefildvalue;
                        } else {
                            $str .= '^'.$prefieldkey.$prefildvalue;
                        }
                    }
                    $str .= chr(31);
                } else {
                    $str .= $fieldsnum.'#'.$field;
                    $str .= chr(31);
                }
            }
        }
        return $str;
    }
}