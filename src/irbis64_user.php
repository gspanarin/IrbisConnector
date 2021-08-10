<?php
//if(!defined('XWP6ipAcTzGo2sFedFZd29q0TWpspEUt')) die(include $_SERVER['DOCUMENT_ROOT'].'/404.html');

class osl_user{
   //public $fields;
   public $login;
   public $password;
   public $irbis_login;
   public $irbis_password;
   public $sigla;
   public $adress;
   public $fio;
   public $position;
   public $email;
   public $phone;
   public $type;
   
   public $organization;
   
   public $error;   
   public $mfn;
   public $status;
   public $ver;
   
   /*
      язык по умолчанию
   */
   
   
   
	function __destruct() { }
   
	function __construct() { }

   
   public static function login($login,$password,$irbis){
      $osl = new osl_user();
      
      if ((empty($login)) or (empty($password))){
         $osl->error = 'Ошибка создания объекта: не переданы логин или пароль';
      }
      else{
         $osl->login = $login;
         $osl->password = $password;
         $osl->read_user($irbis);
          
      }
      return $osl;
   }
   
   function check_login($irbis){  
  
      $ret = $irbis->search("users",mb_strtoupper ('"login='.$this->login.'"'), 1, 1,$irbis->fields_pft);

      if ($ret['found']!='0'){
         $this->error_code = '-11111';
         $this->error_message='Ошибка проверки логина пользователя: такой логин уже есть'; 
         return false;
      }
      else{
         return true;
      } 
   }

   

   
   
   function read_user($irbis){



      $ret = $irbis->search("users",mb_strtoupper ('"login='.$this->login.'"*"password='.$this->password.'"'), 1, 1,$irbis->fields_pft);

      if ($ret['found']!='0'){         
         if (array_key_exists(200,$ret['records'][0]->fields)) {
            $this->irbis_login = $ret['records'][0]->fields[200][0];
         }
         if (array_key_exists(201,$ret['records'][0]->fields)) {
            $this->irbis_password = $ret['records'][0]->fields[201][0];
         }
         if (array_key_exists(101,$ret['records'][0]->fields)) {
            $this->sigla = $ret['records'][0]->fields[101][0];
         }
         if (array_key_exists(102,$ret['records'][0]->fields)) {
            $this->adress = $ret['records'][0]->fields[102][0];
         }
         if (array_key_exists(103,$ret['records'][0]->fields)) {
            $this->fio = $ret['records'][0]->fields[103][0];
         }
         if (array_key_exists(106,$ret['records'][0]->fields)) {
            $this->position = $ret['records'][0]->fields[106][0];
         }
         if (array_key_exists(104,$ret['records'][0]->fields)) {
            $this->email = $ret['records'][0]->fields[104][0];
         }
         if (array_key_exists(105,$ret['records'][0]->fields)) {
            $this->phone = $ret['records'][0]->fields[105][0];
         }
         if (array_key_exists(921,$ret['records'][0]->fields)) {
            $this->type = $ret['records'][0]->fields[921][0];
         }
         
         $this->mfn = $ret['records'][0]->mfn;
         $this->status = $ret['records'][0]->status;
         $this->ver = $ret['records'][0]->ver;
         
         //$user = osl_user::login($login,$password);
         
         $this->organization = osl_organization::login($this->sigla,$irbis);
      }
      else{
         $this->error_code = '-11111';
         $this->error_message='Ошибка чтения записи пользователя: пользователь с такими логином и паролем не найдены в системе';
      }
    
      
   }

   
   function write_user($irbis){
         
      $rec = new irbis64_record();
      
      $rec->mfn = $this->mfn;
      $rec->status = $this->status;
      $rec->ver = $this->ver;
      
      $rec->fields = array(
         101 => array($this->sigla),
         102 => array($this->adress),
         103 => array($this->fio),
         106 => array($this->position),
         104 => array($this->email),
         105 => array($this->phone),
         200 => array($this->irbis_login),
         201 => array($this->irbis_password), 
         202 => array($this->login),
         203 => array($this->password), 
         921 => array($this->type), 
         920 => array('USER')
         
      );
      
      $ret = $irbis->record_write("users",$rec);
      
      if ($ret!='0'){
         $this->error = 'Ошибка сохранения записи пользователя: '.$ret;
      }
      
      $this->refresh($irbis);
   

   }
   
   function refresh($irbis){
      $this->read_user($irbis);
   }
   
}




class osl_organization{
   //public $fields;
   
   public $name;
   public $short_name;
   public $school_number;
   
   public $sigla;
   public $adress = array (
               'zip' =>'',            //^A
               'country' =>'',        //^B
               'region' =>'',         //^C
               'area' =>'',           //^D
               'city' =>'',           //^E
               'citydistrict' =>'',   //^F
               'locality' =>'',       //^G
               'address' =>'');       //^H

   public $email;
   public $phone;
   
   public $bank_details;
   public $inn;
   public $kpp;
   public $legal_address = array (
               'zip' =>'',            //^A
               'country' =>'',        //^B
               'region' =>'',         //^C
               'area' =>'',           //^D
               'city' =>'',           //^E
               'citydistrict' =>'',   //^F
               'locality' =>'',       //^G
               'address' =>'');       //^H;
   public $domen_names;
   public $modules;
   public $db;
   public $industry;
   
   public $error;
   public $mfn;
   public $status;
   public $ver;
   
   
   
   function __destruct() { unset($this);}

   function __construct() { }

   public static function login($sigla,$irbis){
      $osl = new osl_organization();
      if ($sigla!=''){
         $osl->read_org_by_sigla($sigla,$irbis);
      }
      else{
         $osl->error = 'Ошибка создания объекта: не передана сигла организации';
      }
      return $osl;
   }
   
   function check_sigla($irbis){
      

      $ret = $irbis->search("users",'"sigla_org='.$this->sigla.'"', 1, 1,$irbis->fields_pft);
      
      if ($ret['found']!=0){
         $this->error_code = '-11111';
         $this->error_message='Ошибка проверки сиглы организации: такая сигла уже есть'; 
         return false;
      }else{
         return true;
      }
  
   }
   
   
   static function read_org($str){
      $mfn_status = '';
      $ver = '';
      $rec = new irbis64_record;
      $org = new osl_organization;
      
      $ret = explode(chr(31), trim($str)); //НАВЕРНО ТАК ПРАВИЛЬНЕЕ
	   $c = count($ret);
      
      preg_match ("/(\d+?)#(.*?)/U", $ret[0], $mfn_status); 
      $rec->mfn = $mfn_status[1]; 
      $rec->status = $mfn_status[2]; 
      preg_match ("/(\d+?)#(.*?)/U", $ret[1], $ver); 
      $rec->ver = $ver[2]; 

	   for ($i = 2; $i < $c; $i++) 
	   {
         if ($ret[$i]!=''){	
            preg_match("/(\d+?)#(.*?)/U", $ret[$i], $matches);
			 
            $field_num = (int)$matches[1];
            $field_val = $matches[2];

            if ($field_num!='') {
              if (stristr( $field_val, '^')){
                  if (mb_substr($field_val,0,1)!='^'){
                     $field_val = '^*'.$field_val;
                  } 
                  $prefield = explode('^', $field_val);
                  $prefields = '';
                  foreach ($prefield as $val){
                     if (mb_substr($val,1)!=''){
                        $prefields[mb_substr($val,0,1)] = mb_substr($val,1);
                     }
                  }
                  $rec->fields[$field_num][] = $prefields;
               }
               else{
                  $rec->fields[$field_num][] = $field_val;
               }
            }
         }
      }
      

      if (array_key_exists(100,$rec->fields)) {
         $org->name = $rec->fields[100][0];
      }
      if (array_key_exists(110,$rec->fields)) {
         $org->short_name = $rec->fields[110][0];
      }
      if (array_key_exists(111,$rec->fields)) {
         $org->school_number = $rec->fields[111][0];
      }
      
      if (array_key_exists(101,$rec->fields)) {
         $org->sigla = $rec->fields[101][0];
      }
      if (array_key_exists(102,$rec->fields)) {
         //$org->adress = $rec->fields[102][0];
         
         if (array_key_exists('A',$rec->fields[102][0])) {
            $org->adress['zip'] = $rec->fields[102][0]['A'];
         }
         if (array_key_exists('B',$rec->fields[102][0])) {
            $org->adress['country'] = $rec->fields[102][0]['B'];
         }
         if (array_key_exists('C',$rec->fields[102][0])) {   
            $org->adress['region'] = $rec->fields[102][0]['C'];
         }
         if (array_key_exists('D',$rec->fields[102][0])) {   
            $org->adress['area'] = $rec->fields[102][0]['D'];
         }
         if (array_key_exists('E',$rec->fields[102][0])) {   
            $org->adress['city'] = $rec->fields[102][0]['E'];
         }
         if (array_key_exists('F',$rec->fields[102][0])) {   
            $org->adress['citydistrict'] = $rec->fields[102][0]['F'];
         }
         if (array_key_exists('G',$rec->fields[102][0])) {   
            $org->adress['locality'] = $rec->fields[102][0]['G'];
         }
         if (array_key_exists('H',$rec->fields[102][0])) {   
            $org->adress['address'] = $rec->fields[102][0]['H'];
         }
         
         
         
      }
      if (array_key_exists(104,$rec->fields)) {
         $org->email = $rec->fields[104][0];
      }
      if (array_key_exists(105,$rec->fields)) {
         $org->phone = $rec->fields[105][0];
      }
      if (array_key_exists(130,$rec->fields)) {
         $org->inn = $rec->fields[130][0];
      }
      if (array_key_exists(131,$rec->fields)) {
         $org->kpp = $rec->fields[131][0];
      }
      if (array_key_exists(132,$rec->fields)) {
         //$org->legal_address = $rec->fields[132][0];
         if (array_key_exists('A',$rec->fields[132][0])) {
            $org->legal_address['zip'] = $rec->fields[132][0]['A'];
         }
         if (array_key_exists('B',$rec->fields[132][0])) {
            $org->legal_address['country'] = $rec->fields[132][0]['B'];
         }
         if (array_key_exists('C',$rec->fields[132][0])) {   
            $org->legal_address['region'] = $rec->fields[132][0]['C'];
         }
         if (array_key_exists('D',$rec->fields[132][0])) {   
            $org->legal_address['area'] = $rec->fields[132][0]['D'];
         }
         if (array_key_exists('E',$rec->fields[132][0])) {   
            $org->legal_address['city'] = $rec->fields[132][0]['E'];
         }
         if (array_key_exists('F',$rec->fields[132][0])) {   
            $org->legal_address['citydistrict'] = $rec->fields[132][0]['F'];
         }
         if (array_key_exists('G',$rec->fields[132][0])) {   
            $org->legal_address['locality'] = $rec->fields[132][0]['G'];
         }
         if (array_key_exists('H',$rec->fields[132][0])) {   
            $org->legal_address['address'] = $rec->fields[132][0]['H'];
         }
         
      }
      if (array_key_exists(133,$rec->fields)) {
         $org->bank_details = $rec->fields[133][0];
      }

      if (array_key_exists(300,$rec->fields)) {
         $org->modules = $rec->fields[300];
      }
      if (array_key_exists(401,$rec->fields)) {
         $org->domen_names = $rec->fields[401];
      }
      if (array_key_exists(400,$rec->fields)) {
         $org->db = $rec->fields[400][0];
      }
      if (array_key_exists(106,$rec->fields)) {
         $org->industry = $rec->fields[106][0];
      }
      $org->mfn = $rec->mfn;
      $org->status = $rec->status;
      $org->ver = $rec->ver;

      return $org;
   } 
   
   function read_org_by_sigla($sigla,$irbis){
      
      
         $ret = $irbis->search("users",'"sigla_org='.$sigla.'"', 1, 1,$irbis->fields_pft);

         
         if ($ret['found']!=0){
            
            if (array_key_exists(100,$ret['records'][0]->fields)) {
               $this->name = $ret['records'][0]->fields[100][0];
            }
            if (array_key_exists(110,$ret['records'][0]->fields)) {
               $this->short_name = $ret['records'][0]->fields[110][0];
            }
            if (array_key_exists(111,$ret['records'][0]->fields)) {
               $this->school_number = $ret['records'][0]->fields[111][0];
            }
            
            if (array_key_exists(101,$ret['records'][0]->fields)) {
               $this->sigla = $ret['records'][0]->fields[101][0];
            }
            if (array_key_exists(102,$ret['records'][0]->fields)) {
               $this->adress = $ret['records'][0]->fields[102][0];
            }
            if (array_key_exists(104,$ret['records'][0]->fields)) {
               $this->email = $ret['records'][0]->fields[104][0];
            }
            if (array_key_exists(105,$ret['records'][0]->fields)) {
               $this->phone = $ret['records'][0]->fields[105][0];
            }
            if (array_key_exists(130,$ret['records'][0]->fields)) {
               $this->inn = $ret['records'][0]->fields[130][0];
            }
            if (array_key_exists(131,$ret['records'][0]->fields)) {
               $this->kpp = $ret['records'][0]->fields[131][0];
            }
            if (array_key_exists(132,$ret['records'][0]->fields)) {
               $this->legal_address = $ret['records'][0]->fields[132][0];
            }
            if (array_key_exists(133,$ret['records'][0]->fields)) {
               $this->bank_details = $ret['records'][0]->fields[133][0];
            }
      
            if (array_key_exists(300,$ret['records'][0]->fields)) {
               $this->modules = $ret['records'][0]->fields[300];
            }
            if (array_key_exists(401,$ret['records'][0]->fields)) {
               $this->domen_names = $ret['records'][0]->fields[401];
            }
            if (array_key_exists(400,$ret['records'][0]->fields)) {
               $this->db = $ret['records'][0]->fields[400][0];
            }
            if (array_key_exists(106,$ret['records'][0]->fields)) {
               $this->industry = $ret['records'][0]->fields[106][0];
            }
           
            
      
            
            $this->mfn = $ret['records'][0]->mfn;
            $this->status = $ret['records'][0]->status;
            $this->ver = $ret['records'][0]->ver;
            
            
            
         }
         else{
            $this->error = "Ошибка загрузки записи организации: не удалось найти организацию по заданной сигле";
         }
            
      
      
      
   } 
   
   
   function write_org($irbis){
      

      $rec = new irbis64_record();
      
      $rec->mfn = $this->mfn;
      $rec->status = $this->status;
      $rec->ver = $this->ver;
      
      $rec->fields = array(
         100 => array($this->name),
         101 => array($this->sigla),
         102 => array(
                     array(
                        'A' => $this->adress['zip'],
                        'B' => $this->adress['country'],
                        'C' => $this->adress['region'],
                        'D' => $this->adress['area'],
                        'E' => $this->adress['city'],
                        'F' => $this->adress['citydistrict'],
                        'G' => $this->adress['locality'],
                        'H' => $this->adress['address'],
                     )
                  ),
         104 => array($this->email),
         105 => array($this->phone),
         110 => array($this->short_name),
         111 => array($this->school_number),
         130 => array($this->inn),
         131 => array($this->kpp),
         132 => array(
                     array(
                        'A' => $this->legal_address['zip'],
                        'B' => $this->legal_address['country'],
                        'C' => $this->legal_address['region'],
                        'D' => $this->legal_address['area'],
                        'E' => $this->legal_address['city'],
                        'F' => $this->legal_address['citydistrict'],
                        'G' => $this->legal_address['locality'],
                        'H' => $this->legal_address['address'],
                     )
                  ),
         133 => array($this->bank_details),

         300 => $this->modules,
         
         400 => array($this->db),
         106 => array($this->industry),
         
         401 => $this->domen_names,
         
         920 => array('ORG')
      );


      $ret = $irbis->record_write("users",$rec);
      
      
      if ($ret!='0'){
         $this->error = 'Ошибка сохранения записи пользователя: '.$ret;
      }
      
      $this->refresh($irbis);

   }
   
   function refresh($irbis){
      $this->read_org_by_sigla($this->sigla,$irbis);
   }
   
}


