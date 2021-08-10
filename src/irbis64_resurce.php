<?php 
//if(!defined('XWP6ipAcTzGo2sFedFZd29q0TWpspEUt')) die(include $_SERVER['DOCUMENT_ROOT'].'/404.html');

/*

Объект irbis64_ws
Объект полей рабочих листов

Объект irbis64_field
Объект поле.

Объект irbis64_subfield
Объект рабочего листа подполей

Объект irbis64_mnu
Объект справочника
             
*/


/*
Возможные значения input_type

0 - без дополнительной обработки (просто инпут)
1 - ввод через меню (комбобокс с заранее загруженными значениями)
2 - ввод через словарь (запрос к базе по заданному словарю)
3 - (не используем) ввод через рубрикатор
4 - ввод через многостроковое окно (типа ареа)
5 - табличный ввод (это поле с подполями, может быть только в рабочем листе плей)
6 - (не используем) ввод через иерархическое меню
7 - ввод через переключатель (радиобуттан, значения загружаются через справочник)
8 - (не используем) ввод через внешгюю программу
9 - ввод через маску
10 - (не используем) ввод через авторитетный файл
11 - (не используем) ввод через тезаурус
12 - (не используем???) ввод через внешний файл (загрузка содержимого текстового файла в поле)
13 - (не используем) ввод на основе навигатора
14 - (не используем) ввод через режим пользователя
15 - (не используем) ввод через динамический справочник
16 - (не используем) ввод через ИРБИС-ресурс
110 - Тип ввод для календаря
*/


/* Функция для записи чего-нибудь в файл. Для отдалки. МОжно вести лог */
function object2file($value, $filename)
{
    $filename =$_SERVER['DOCUMENT_ROOT'].$filename;
   
    //$str_value = serialize($value);
    $str_value = $value;
    $f = fopen($filename, 'a');
    fwrite($f, $str_value."\n");
    fclose($f);
}

//Определяем тип сортировки у справочника
function sort_type($file_name){
   if (stripos($file_name,'\\')!=0) {
         return substr($file_name, stripos($file_name,'\\')+2);
   }
   else{
      return '';
   }
      
}

//Нормализация имени - убираем из имени файла лишнии данные (сортировки и т.д.)
function file_name_normalization($file_name){
   if (stripos($file_name,'\\')!=0) {
         return substr($file_name, 0, stripos($file_name,'\\'));
   }
   else{
      return $file_name;
   }
}



//Объект рабочего листа полей
class irbis64_field{
   //Номер поля
   public $number;
   //Заголовок описания поля
   public $description;
   //Повторяемость
   public $repeat;
   //Номер индекса в файле справки
   public $help_index;
   //Тип ввода
   public $input_type;
   //Имя файла ввода
   public $input_name;
   //Сортировка файла для ввода
   public $input_sort;
   //Формально-логический контроль данных в поле
   public $flk;
   //Текст подсказки для поля
   public $help_text;
   //Значение поля по умолчанию
   public $default_value;
   //Дополнительная информация (используется при некоторых методах ввода)
   public $additional_information;
   //Страница (вкладка), к которой относится поле
   public $pages;
   //Название файла рабочего листа полей, из которого взято данное подполе
   public $irbis_ws;
   
   
   // Конструктор объекта
   function __construct($db='',$irbis='',$str='') {
      if (!empty($db) and !empty($irbis) and !empty($str)){
         $this->set_value($db,$irbis,$str);
      }
   }
   

   
   // Установка значений свойств объекта
   function set_value($db,$irbis,$str) {
      
      $this->number = $str[0];
      $this->description = $str[1];
      $this->repeat = $str[2];
      $this->help_index = $str[3];
      $this->input_type = $str[4];
      $this->input_name = file_name_normalization($str[5]);
      $this->input_sort = sort_type($str[5]);
      
      switch ($str[4]) {
         case 5:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$this->input_name])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $this->input_file = new irbis64_subfield();
               $tmp = new irbis64_resurce();
               $irbis->registry_input_file[$this->input_name] = $tmp->read_wss($db,$irbis,$this->input_name);
               $tmp = null;
            }
            //Передаем объект файла ввода для данного режима
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            break;
         case 1:
            $this->input_file = new irbis64_mnu($db,$irbis,$str[5]);
            //Выполняем сортировку по столбцу, указанному в РЛ
            if ($this->input_sort==1){
               $this->input_file->sorting_keys();
            }
            elseif ($this->input_sort==2){
               $this->input_file->sorting_values();   
            }
            
            break;
         case 7:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$this->input_name])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $irbis->registry_input_file[$str[5]] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            break;
         default:
            $this->input_file = '';
      }
      
      $this->flk = $str[6];
      $this->help_text = $str[7];
      $this->default_value = $str[8];
      $this->additional_information = $str[9];
      //Добавляем название страницы в список, если оно туда еще не внесено
      if (!in_array($str[10], $this->pages)){
         $this->pages[] = $str[10];
      }
      //Добавляем название файла источника в список, если оно туда еще не внесено
      if (!in_array($str[11], $this->irbis_ws)){
         $this->irbis_ws[] = $str[11];
      }
      
   
   }
   
   
   
   
   
   
   // Формирование данных о поле без считывания подполей и прочих ресурсов
   function set_value0($db,$irbis,$str) {
      
      $this->number = $str[0];
      $this->description = $str[1];
      $this->repeat = $str[2];
      $this->help_index = $str[3];
      $this->input_type = $str[4];
      $this->input_name = file_name_normalization($str[5]);
      $this->input_sort = sort_type($str[5]);
      
      
      $this->flk = $str[6];
      $this->help_text = $str[7];
      $this->default_value = $str[8];
      $this->additional_information = $str[9];
      $this->pages[] = $str[10];
      $this->irbis_ws[] = $str[11];
   
   }
 
 
 
  
   function refresh_input_name($db,$irbis,$resurce) {
      //print_r($this);
      switch ($this->input_type) {
         case 5:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$this->input_name])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $this->input_file = new irbis64_subfield();
               $tmp = new irbis64_resurce();
               $irbis->registry_input_file[$this->input_name] = $tmp->read_wss_list($db,$irbis,$this->input_name);
               $tmp = null;
            }
            //Передаем объект файла ввода для данного режима
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            break;
         case 1:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            }
            else{
               $irbis->registry_input_file[$this->input_name] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            //Заполняем значение поля из реестра файлов
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            //Выполняем сортировку по столбцу, указанному в РЛ
            if ($this->input_sort==1){
               $this->input_file->sorting_keys();
            }
            elseif ($this->input_sort==2){
               $this->input_file->sorting_values();   
            }
            
            break;
         case 7:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            }
            else{
               $irbis->registry_input_file[$this->input_name] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            //Заполняем значение поля из реестра файлов
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            break;
         default:
            $this->input_file = '';
      }
      
   }
   
   
   
   
   
   
   function refresh_input_name2($db,$irbis,$resurce) {
      //print_r($this);
      switch ($this->input_type) {
         case 5:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$this->input_name])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $this->input_file = new irbis64_subfield();
               $tmp = new irbis64_resurce();
               $irbis->registry_input_file[$this->input_name] = $tmp->read_wss($db,$irbis,$this->input_name);
               $tmp = null;
            }
            //Передаем объект файла ввода для данного режима
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            break;
         case 1:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            }
            else{
               $irbis->registry_input_file[$this->input_name] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            //Заполняем значение поля из реестра файлов
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            //Выполняем сортировку по столбцу, указанному в РЛ
            if ($this->input_sort==1){
               $this->input_file->sorting_keys();
            }
            elseif ($this->input_sort==2){
               $this->input_file->sorting_values();   
            }
            
            break;
         case 7:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            }
            else{
               $irbis->registry_input_file[$this->input_name] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            //Заполняем значение поля из реестра файлов
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            break;
         default:
            $this->input_file = '';
      }
      
   }
   
}






//Объект рабочего листа подполей
class irbis64_subfield{
   //Метка поля
   public $mark;
   public $description;
   public $repeat;
   public $help_index;
   public $input_type;
   public $input_name;
   public $input_sort;
   public $flk;
   public $help_text;
   public $default_value;
   public $additional_information;
   public $file_name;
   
   //function __construct($db,$irbis,$str) {
   //   $this->set_value($db,$irbis,$str);
   //}
   
   function __construct() {}
   
   function set_value($db,$irbis,$str){   
      $this->mark = $str[0];
      $this->description = $str[1];
      $this->repeat = $str[2];
      $this->help_index = $str[3];
      $this->input_type = $str[4];
      $this->input_name = file_name_normalization($str[5]);
      $this->input_sort = sort_type($str[5]);
      
      switch ($str[4]) {
         case 1:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$this->input_name])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $irbis->registry_input_file[$this->input_name] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            //Выполняем сортировку по столбцу, указанному в РЛ
            if ($this->input_sort==1){
               $this->input_file->sorting_keys();
            }
            elseif ($this->input_sort==2){
               $this->input_file->sorting_values();   
            }
            break;
         case 7:
            //Реестр загруженных файлов
            if (array_key_exists($str[5],$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$str[5]])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $irbis->registry_input_file[$str[5]] = new irbis64_mnu($db,$irbis,$str[5]);
            }
            $this->input_file = $irbis->registry_input_file[$str[5]];
            break;
         default:
            $this->input_file = '';
      }
      
      $this->flk = $str[6];
      $this->help_text = $str[7];
      $this->default_value = $str[8];
      $this->additional_information = $str[9];
      $this->file_name = $str[10];
      
   }
   
   
   
   
   function set_value0($db,$irbis,$str){   
      $this->mark = $str[0];
      $this->description = $str[1];
      $this->repeat = $str[2];
      $this->help_index = $str[3];
      $this->input_type = $str[4];
      $this->input_name = file_name_normalization($str[5]);
      $this->input_sort = sort_type($str[5]);
      
      $this->flk = $str[6];
      $this->help_text = $str[7];
      $this->default_value = $str[8];
      $this->additional_information = $str[9];
      $this->file_name = $str[10];
      
   }
   
   function refresh_input_name($db,$irbis,$str){ 
  
      switch ($this->input_type) {
         case 1:
          
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$this->input_name])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $irbis->registry_input_file[$this->input_name] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            //Выполняем сортировку по столбцу, указанному в РЛ
            if ($this->input_sort==1){
               $this->input_file->sorting_keys();
            }
            elseif ($this->input_sort==2){
               $this->input_file->sorting_values();   
            }
            
            
            break;
            
         case 7:
            //Реестр загруженных файлов
            if (array_key_exists($this->input_name,$irbis->registry_input_file)) {
            //if (is_object($irbis->registry_input_file[$str[5]])) {
               //$irbis->registry_input_file[$ret[$j]] -> set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
            }
            else{
               $irbis->registry_input_file[$this->input_name] = new irbis64_mnu($db,$irbis,$this->input_name);
            }
            $this->input_file = $irbis->registry_input_file[$this->input_name];
            break;
         default:
            $this->input_file = '';
      }
   }
}





//Объект справочника
class irbis64_mnu{

   public $values;

   function __construct($db_name,$irbis,$mnu_name) {
       
      if (stripos($mnu_name,'\\')!=0) {
         $mnu_name = substr($mnu_name, 0, stripos($mnu_name,'\\'));
      }

      $packet = implode("\n", array('L', $irbis->arm, 'L', $irbis->id, $irbis->seq, '', '', '', '', '', '10.'.$db_name.'.'.$mnu_name));
      $packet = strlen($packet) . "\n" . $packet;
      
      $answer = $irbis->send($packet);
      
      if ($answer === false) return false;
      $irbis->error_code = $answer[10];
      
      $answer[10] = iconv('windows-1251', 'UTF-8', $answer[10]);
      $ret = explode(chr(31).chr(30), trim($answer[10]));
      $i=0;

      for($i; $i<count($ret); $i = $i + 2){
         if ((trim($ret[$i]) == '*****') or ($i+1>=count($ret))){
            $i=count($ret)+1;
         }
         else{
            $this->values[$ret[$i]] = $ret[$i+1];
         }
      }   
   }
   
   // Сортировка значений по кодам (ключам)
   function sorting_keys()
   {
      ksort($this->values);
   }
   
   //Сортировка значений по расшифровкам (значениям)
   function sorting_values()
   {
      asort($this->values);
   }
   
   function write_mnu($db_name,$irbis,$mnu_name){
      
      $values_str = '';
      foreach($this->values as $key=>$value){
         $values_str .= $key.''.$value.'';
      }
      
      $values_str = iconv('UTF-8', 'windows-1251', $values_str);
      
      $packet = implode("\n", array('L', $irbis->arm, 'L', $irbis->id, $irbis->seq, '', '', '', '', '', '10.'.$db_name.'.&'.$mnu_name.'&'.$values_str));
      $packet = strlen($packet) . "\n" . $packet;
      
      $answer = $irbis->send($packet);
      
      if ($answer === false) return false;
      $irbis->error_code = $answer[10];

   }
   
   
}





class irbis64_resurce{

   
   public $fields = array();

	function __destruct() { }

	/**
	* Конструктор объекта
	*/
	function __construct() { } 

	

// Чтение рабочего листа
function read_ws($db_name, $irbis, $recurce) {
	//Это функция для отладки. Пишет объект/переменную в файл
   //object2file($recurce,'/avd/log.txt');
   
   $packet = implode("\n", array('L', $irbis->arm, 'L', $irbis->id, $irbis->seq, '', '', '', '', '', '10.'.$db_name.'.'.$recurce));
	$packet = strlen($packet) . "\n" . $packet;
	
	$answer = $irbis->send($packet);
	if ($answer === false) return false;
	$irbis->error_code = $answer[10];
	
   $answer[10] = iconv('windows-1251', 'UTF-8', $answer[10]);
   $ret = explode(chr(31).chr(30), trim($answer[10]));

   //Количество вкладок(страниц)
   $zakladka_count=(int)$ret[0];
   
   //считываем название страниц и количество полей не странице
   for ($i=1;$i<=$zakladka_count;$i++){
      for ($j=1; $j<=(int)$ret[$zakladka_count+$i]; $j++){
         $pages[] = $ret[$i];
      }
   }   
   
   //Начало описания полей
   $j=(int)$ret[0]*2+1;
   
   //номер поля по порядку
   $field_index=0;
   
   //Считываем поля
   while ($j<=count($ret)){
      //Проверяем что бы в файле было описание дял очередного поля
      //проверяем наличие метки поля
      if ($ret[$j]!=''){
         if (array_key_exists($ret[$j],$this->fields)) {
         //if (is_object($this->fields[$ret[$j]])) {
            $this->fields[$ret[$j]]->set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
         }
         else{
            $this->fields[$ret[$j]]= new irbis64_field($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$pages[$field_index],$recurce));
         }

         $field_index = $field_index + 1;
      }
      $j = $j + 10;
   }
}



//Чтение группы рабочих листов
function read_ws_list($db_name, $irbis, $recurce) {
   if (empty($recurce)){
      return;
   }
   
   $recurce_list = '';
   
   foreach ($recurce as $value){
      $recurce_list = $recurce_list.'10.'.$db_name.'.'.$value."\n";   
   }
   
   
   $packet = implode("\n", array('L', $irbis->arm, 'L', $irbis->id, $irbis->seq, '', '', '', '', '', $recurce_list));
	$packet = strlen($packet) . "\n" . $packet;
	
	$answer = $irbis->send($packet);
	if ($answer === false) return false;
	$irbis->error_code = $answer[10];
	$tmp = '';
   
   for ($k=10; $k<count($answer); $k++){
      if (trim($answer[$k])!=''){
         
         $tmp = iconv('windows-1251', 'UTF-8', $answer[$k]);
         $ret = explode(chr(31).chr(30), trim($tmp));

         //Количество вкладок(страниц)
         $zakladka_count=(int)$ret[0];
         
         //Читаем перечень вкладок, количество полей на вкладке и формируем начальные номера полей на вкладке
         $pages = array();
         $fieldnum = 0;
         for ($i=1;$i<=$zakladka_count;$i++){
            $pages[] = array(
               "title" => $ret[$i], 
               "field_count" => $ret[$zakladka_count+$i],
               "start_num" => $fieldnum, 
               );
               $fieldnum = $fieldnum + $ret[$zakladka_count+$i];
         }   
  
         //Начало описания полей
         $j=(int)$ret[0]*2+1;
         
         /*============================================================*/
         
         $embedded_pages = array();
         
         //Цикл по всем вкладкам РЛ
         foreach ($pages as $page){
            //Если текущая вкладка - вложенный РЛ
            if (substr(trim($page['title']), 0, 1)=='@'){
               //$this->read_ws_list($db_name,$irbis, array(substr(trim($page['title']), 1).'.WS'));
               //Вложенные РЛ пишем в список $embedded_pages, что бы после основного цикла обратиться к ним оптом
               $embedded_pages[] = substr(trim($page['title']), 1).'.WS';
            } 
            //Если текущая вкладка обычная
            else{
               
               //Цикл по полям выбранной вкладки
               for ($field_num = $page['start_num']; $field_num < $page['field_count'] + $page['start_num']; $field_num++){
                  
                  //Проверка, если поле уже есть, то не создаем новый элемент массива
                  if (array_key_exists($ret[$j],$this->fields)) {}
                  else{
                     $this->fields[$ret[$j]]= new irbis64_field;   
                  }
                  //Установка значений свойств поля
                  $this->fields[$ret[$j]]->set_value0($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$page['title'],$recurce[$k-10]));
                  $j = $j + 10;
               }
            }
         }  
          
         //Выполняем загрузку вложенных РЛ
         if (!empty($embedded_pages)){
            $this->read_ws_list($db_name,$irbis, $embedded_pages);   
         }

      }
   }
}





function read_wss($db_name, $irbis, $recurce) {
   $fields = array();

   $packet = implode("\n", array('L', $irbis->arm, 'L', $irbis->id, $irbis->seq, '', '', '', '', '', '10.'.$db_name.'.'.$recurce));
	$packet = strlen($packet) . "\n" . $packet;
	
	$answer = $irbis->send($packet);
	if ($answer === false) return false;
	$irbis->error_code = $answer[10];
   
   $answer[10] = iconv('windows-1251', 'UTF-8', $answer[10]);
   $ret = explode(chr(31).chr(30), trim($answer[10]));

   if ((int)$ret[0]>0){
      $j=1;

      
      while ($j<=count($ret)){
         
         if ($ret[$j]!=''){
            $fields[$ret[$j]] = new irbis64_subfield;
            $fields[$ret[$j]]->set_value($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$recurce));
         }
         $j = $j+10;
      }
   }
   
   
	return $fields;
}



function read_wss_list($db_name, $irbis, $recurce) {
   $fields = array();

   $packet = implode("\n", array('L', $irbis->arm, 'L', $irbis->id, $irbis->seq, '', '', '', '', '', '10.'.$db_name.'.'.$recurce));
	$packet = strlen($packet) . "\n" . $packet;
	
	$answer = $irbis->send($packet);
	if ($answer === false) return false;
	$irbis->error_code = $answer[10];
   
   $answer[10] = iconv('windows-1251', 'UTF-8', $answer[10]);
   $ret = explode(chr(31).chr(30), trim($answer[10]));

   if ((int)$ret[0]>0){
      $j=1;

      
      while ($j<=count($ret)){
         
         if ($ret[$j]!=''){
            $fields[$ret[$j]] = new irbis64_subfield;
            $fields[$ret[$j]]->set_value0($db_name, $irbis,array($ret[$j],$ret[$j+1],$ret[$j+2],$ret[$j+3],$ret[$j+4],$ret[$j+5],$ret[$j+6],$ret[$j+7], $ret[$j+8],$ret[$j+9],$recurce));
         }
         $j = $j+10;
      }
   }
   
   
	return $fields;
}

   
}