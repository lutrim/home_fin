<?php

//putenv("TZ=Europe/Moscow");

$db_name="lut_fin";	//база данных
$host="mysql.lutrim.com";	//хост
$user="fin_root";    //логин
$pass="fin_prog";		//password

//законнектимся - получаем link-идентификатор или вывод номера и текста ошибки
//с последующим прерыванием работы скрипта (die())
$link=mysql_connect($host,$user,$pass) or die(mysql_errno($link).mysql_error($link));
//выбираем базу данных fin, созданную нами ранее
$db=mysql_select_db($db_name,$link) or die(mysql_errno($link).mysql_error($link));
mysql_set_charset('utf8',$link);
$result=mysql_query("SET time_zone='+4:00';",$link) or die(mysql_errno($link).mysql_error($link));

$result=mysql_query("select curdate();",$link);
$curdate=mysql_fetch_row($result);

$result=mysql_query("select curtime();",$link);
$curtime=mysql_fetch_row($result);

echo "DateMySQL = ".$curdate[0]." Time ".$curtime[0]." </br>";

echo "Date PHP = ".date("c")." </br>";

mysql_close($link);

?>