<?php
$date_tmp=explode(" ",$_POST["op_date"]);
$date_invers=$date_tmp[2];
for ($i=1;$i>=0;$i--){
if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
$date_invers .="-".$date_tmp[$i];
}

//$date_invers = Date_create($date_invers);
//echo $date_invers." create"; die;

//print_r($_POST); 
//echo " ПОСТ </br></br></br>";

$db_name="lut_fin";	//база данных
$host="mysql.lutrim.com";	//хост
$user="fin_root";    //логин
$pass="fin_prog";		//password

//законнектимся - получаем link-идентификатор или вывод номера и текста ошибки
//с последующим прерыванием работы скрипта (die())
$link=mysql_connect($host,$user,$pass) or die(mysql_errno($link).mysql_error($link));
//выбираем базу данных fin, созданную нами ранее
$db=mysql_select_db($db_name,$link) or die(mysql_errno($link).mysql_error($link));
//установка региональных настроек, кодировка, часовой пояс
mysql_set_charset('utf8',$link); 
$result=mysql_query("SET time_zone='+4:00';",$link) or die(mysql_errno($link).mysql_error($link));
//вставим данные в таблицы истории.
//если поле новой группы пустое, вставляем как обычно
 if ($_POST["new_group"]=="") {
  //echo "popal null";
  mysql_query("INSERT INTO main_history (n_op, op_date, op_summ, comment, priznak) VALUES (NULL, '".$date_invers."' , '".$_POST["op_summ"]."', '".$_POST["op_comm"]."', '".$_POST["op_group"]."')",$link) or die(mysql_errno($link).mysql_error($link));
 } else { //если поле непустое, сначала вставим новую строку в справочник, потом добавим в историю
//		echo "popal gemor : ".$_POST["new_group"]." </br>";
		//проверим, возможно такая группа уже существует
		$result=mysql_query("SELECT priznak from dir_pr where UCASE(priznak_text) = UCASE('".$_POST["new_group"]."')",$link) or die("ошибка ".mysql_errno($link)." Текст: ".mysql_error($link));
		$op_group=mysql_fetch_row($result);
		if ($op_group <> "") {$_POST["op_group"]=$op_group[0]; print_r($_POST); echo " </br>";} else { //Если такой группы действительно нет, то добавляем
		mysql_query("	INSERT INTO dir_pr (priznak, priznak_text)
						VALUES (NULL,'".$_POST["new_group"]."')",$link) or die("ошибка ".mysql_errno($link)." Текст: ".mysql_error($link));
		}
		mysql_query("	INSERT INTO main_history (n_op, op_date, op_summ, comment, priznak)
						VALUES (NULL, '".$date_invers."' , '".$_POST["op_summ"]."', '".$_POST["op_comm"]."', 
						(select priznak from dir_pr where UCASE(priznak_text) = UCASE('".$_POST["new_group"]."')))",$link) or die(" ошибка ".mysql_errno($link)." Текст: ".mysql_error($link));
		
	};
//вставка данный в таблицу остатков по алгоритму выбора
//сначала получаем максимальную дату в таблице
$result=mysql_query("SELECT max(r_date) FROM rests",$link) or die(mysql_errno($link).mysql_error($link));
$max_r_date=mysql_fetch_row($result);
//echo $max_r_date[0]." максимальная дата остатка </br>";
//echo $date_invers." Дата операции </br>";

//если дата операции равна максимальной дате, то просто увеличиваем последний остаток.
if ($date_invers == $max_r_date[0]) {
// echo "today </br>";
 mysql_query("UPDATE rests set rest_summ=rest_summ+".$_POST["op_summ"]." where r_date='".$date_invers."'",$link) or die(mysql_errno($link).mysql_error($link));
 } //если дата операции меньше максимальной даты, то 
		elseif ($date_invers < $max_r_date[0]) {
//		 echo "YESterday </br>";
		 $result=mysql_query("select * from rests where r_date = '".$date_invers."' ",$link) or die("ошибка ".mysql_errno($link)." Текст: ".mysql_error($link));
		  //проверяем есть ли в таблице остаков остаток за введенную дату? 
		  if (!mysql_fetch_row($result)) { //если нет, то добавляем строчку с нужной датой и значением остатка, равному предыдущей дате
//		   echo "Вставка </br>";
		   $result=mysql_query("select rest_summ from rests where r_date in ( select max(r_date) from rests where r_date < '".$date_invers."') ",$link) or die(mysql_errno($link).mysql_error($link));
		   $max_l_rest=mysql_fetch_row($result);
//		   print_r($max_l_rest); echo " максимальный последний остаток </br>";
		   mysql_query("insert into rests (r_date,rest_summ) VALUES ('".$date_invers."' , ".$max_l_rest[0].")",$link) or die(mysql_errno($link).mysql_error($link));
		  };
		 //и прибавляем сумму операции ко всем остаткам больше или равным (по дате) введенному
		 mysql_query("UPDATE rests set rest_summ=(rest_summ+(".$_POST["op_summ"].")) where r_date >= '".$date_invers."'",$link) or die(mysql_errno($link).mysql_error($link));
		} //если дата операции больше максимальной, то добавляем новую строчку в таблицу остатков, с остатком добавленным к последнему.
			elseif ($date_invers > $max_r_date[0]) {
//			 echo "current </br>";
			 $result=mysql_query("SELECT rest_summ FROM rests where r_date in (select max(r_date) from rests)",$link) or die(mysql_errno($link).mysql_error($link));
			 $max_rest=mysql_fetch_row($result);
//			 print_r($max_rest); echo " + ".$_POST["op_summ"]." максимальная остаток и сколько прибавляем</br>";
			 mysql_query("INSERT INTO rests (r_date,rest_summ) VALUES ( '".$date_invers."' , ".($max_rest[0]+$_POST["op_summ"]).") ",$link) or die(mysql_errno($link).mysql_error($link));
			}

//print_r($result); echo " последнее значание запроса скул </br>";
mysql_close($link);
header('Location: http://lutrim.com/fin/index.php');

?>