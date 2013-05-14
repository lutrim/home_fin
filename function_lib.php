<?php 

FUNCTION connect_to_db($db_name, $host, $user, $pass)
{
global $link, $db;
//законнектимся - получаем link-идентификатор или вывод номера и текста ошибки
//с последующим прерыванием работы скрипта (die())
$link=mysqli_connect($host,$user,$pass) or die(mysqli_errno($link).mysqli_error($link));
//выбираем базу данных BOOKS, созданную нами ранее
$db=mysqli_select_db($link,$db_name) or die(mysqli_errno($link).mysqli_error($link));
//установка региональных настроек, кодировка, часовой пояс
mysqli_set_charset($link,'utf8'); 
$result=mysqli_query($link,"SET time_zone='+4:00';") or die(mysqli_errno($link).mysqli_error($link));
return $retval;
}

FUNCTION inverse_date($date_m_d_Y)
{
$date_tmp=explode(" ",$date_m_d_Y);
$date_invers=$date_tmp[2];
	for ($i=1;$i>=0;$i--){
		if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
		$date_invers .="-".$date_tmp[$i];
	}
return $date_invers;
}

FUNCTION add_operation($int_POST)
{
	global $link,$db;

//преобразуем дату в удобоваримый формат YYYY-MM-DD
$date_invers=inverse_date($int_POST["op_date"]);

//определяем знак операции нулевая группа (удаление) или приход/расход 
if ($int_POST["op_group"] == 0) {}
	elseif ($int_POST["op_group"] > 0) { $int_POST["op_summ"] = abs($int_POST["op_summ"]);}
		else {$int_POST["op_summ"] = -1 * abs($int_POST["op_summ"]);};
//вставим данные в таблицы истории.
//если поле новой группы пустое, вставляем как обычно
	if ($int_POST["new_group"]=="") {
		//echo "popal null";
		mysqli_query($link,"INSERT INTO main_history (n_op, op_date, op_summ, comment, priznak)
		VALUES (NULL, '".$date_invers."' , '".$int_POST["op_summ"]."', '".$int_POST["op_comm"]."', '".$int_POST["op_group"]."')") 
		or die(mysqli_errno($link)." : ".mysqli_error($link));
	} 
		else { //если поле непустое, сначала вставим новую строку в справочник, потом добавим в историю
			//echo "popal gemor : ".$int_POST["new_group"]." </br>";
			//проверим, возможно такая группа уже существует
			$result=mysqli_query($link,"SELECT priznak from dir_pr where UCASE(priznak_text) = UCASE('".$int_POST["new_group"]."')",$link)
			or die("ошибка ".mysqli_errno($link)." Текст: ".mysqli_error($link));
			$op_group=mysqli_fetch_row($result);
			if ($op_group <> "") {$int_POST["op_group"]=$op_group[0]; print_r($int_POST); echo " </br>";} else { //Если такой группы действительно нет, то добавляем
			mysqli_query($link,"	INSERT INTO dir_pr (priznak, priznak_text)
							VALUES (NULL,'".$int_POST["new_group"]."')") or die("ошибка ".mysqli_errno($link)." Текст: ".mysqli_error($link));
			}
			mysqli_query($link,"	INSERT INTO main_history (n_op, op_date, op_summ, comment, priznak)
							VALUES (NULL, '".$date_invers."' , '".$int_POST["op_summ"]."', '".$int_POST["op_comm"]."', 
							(select priznak from dir_pr where UCASE(priznak_text) = UCASE('".$int_POST["new_group"]."')))")
							or die(" ошибка ".mysqli_errno($link)." Текст: ".mysqli_error($link));
			
		};
//вставка данный в таблицу остатков по алгоритму выбора
//сначала получаем максимальную дату в таблице
$result=mysqli_query($link,"SELECT max(r_date) FROM rests") or die(mysqli_errno($link).mysqli_error($link));
$max_r_date=mysqli_fetch_row($result);
//echo $max_r_date[0]." максимальная дата остатка </br>";
//echo $date_invers." Дата операции </br>";

//если дата операции равна максимальной дате, то просто увеличиваем последний остаток.
	if ($date_invers == $max_r_date[0]) {
		// echo "today </br>";
		mysqli_query($link,"UPDATE rests set rest_summ=rest_summ+".$int_POST["op_summ"]." where r_date='".$date_invers."'")
		or die(mysqli_errno($link).mysqli_error($link));
	} //если дата операции меньше максимальной даты, то 
		elseif ($date_invers < $max_r_date[0]) {
			//echo "YESterday </br>";
			$result=mysqli_query($link,"select * from rests where r_date = '".$date_invers."' ")
					or die("ошибка ".mysqli_errno($link)." Текст: ".mysqli_error($link));
			//проверяем есть ли в таблице остаков остаток за введенную дату? 
				if (!mysqli_fetch_row($result)) { //если нет, то добавляем строчку с нужной датой и значением остатка, равному предыдущей дате
					//echo "Вставка </br>";
					$result=mysqli_query($link,"select rest_summ from rests where r_date in ( select max(r_date) from rests where r_date < '".$date_invers."') ")
							or die(mysqli_errno($link).mysqli_error($link));
					$max_l_rest=mysqli_fetch_row($result);
					//print_r($max_l_rest); echo " максимальный последний остаток </br>";
					mysqli_query($link,"insert into rests (r_date,rest_summ) VALUES ('".$date_invers."' , ".$max_l_rest[0].")")
					or die(mysqli_errno($link).mysqli_error($link));
				};
			//и прибавляем сумму операции ко всем остаткам больше или равным (по дате) введенному
			mysqli_query($link,"UPDATE rests set rest_summ=(rest_summ+(".$int_POST["op_summ"].")) where r_date >= '".$date_invers."'")
			or die(mysqli_errno($link)." : ".mysqli_error($link));
		} //если дата операции больше максимальной, то добавляем новую строчку в таблицу остатков, с остатком добавленным к последнему.
			elseif ($date_invers > $max_r_date[0]) {
				//echo "current </br>";
				$result=mysqli_query($link,"SELECT rest_summ FROM rests where r_date in (select max(r_date) from rests)")
				or die(mysqli_errno($link)." : ".mysqli_error($link));
				$max_rest=mysqli_fetch_row($result);
				//print_r($max_rest); echo " + ".$int_POST["op_summ"]." максимальная остаток и сколько прибавляем</br>";
				mysqli_query($link,"INSERT INTO rests (r_date,rest_summ) VALUES ( '".$date_invers."' , ".($max_rest[0]+$int_POST["op_summ"]).") ")
				or die(mysqli_errno($link)." : ".mysqli_error($link));
			}

//print_r($result); echo " последнее значание запроса скул </br>";
return $retval;
}

?>