<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Проба пера. Учет финансов семьи.</title>


	<script type="text/javascript" src="Tools/jquery_menu/jquery.min.js"></script>
    <script type="text/javascript" src="Tools/jquery_menu/jquery.date_input.js"></script>
    <script type="text/javascript">$($.date_input.initialize);</script>
	<link rel="stylesheet" href="Tools/jquery_menu/date_input.css" type="text/css"/>	

</head>
<BODY>
<TABLE>
<tr><td><form action="put_to_tbl.php" method="post">
  Date: <input type="text" name="op_date" class="date_input" /> 
  Summ: <input type="text" name="op_summ" />
  Comments: <input type="text" name="op_comm" /> </br>
  Group: 
		<select size=1 name="op_group">
<?php
$db_name="lut_fin";	//база данных
$table="dir_pr";	//таблица
$host="mysql.lutrim.com";	//хост
$user="fin_root";		//логин
$pass="fin_prog";		//password
//законнектимся - получаем link-идентификатор или вывод номера и текста ошибки
//с последующим прерыванием работы скрипта (die())
$link=mysql_connect($host,$user,$pass) or die(mysql_errno($link).mysql_error($link));
//выбираем базу данных BOOKS, созданную нами ранее
$db=mysql_select_db($db_name,$link) or die(mysql_errno($link).mysql_error($link));
mysql_set_charset('utf8',$link); 
//выберем данные
$result=mysql_query("SELECT * FROM ".$table, $link) or die(mysql_errno($link).mysql_error($link));
//выведем результаты в HTML-документ

while($data_pr=mysql_fetch_row($result)) {
	echo "<option value=",$data_pr[0],">",$data_pr[1];
	$i++;
}
?>		
        </select>  
  <input type="submit" value="Submit" />
</form></td>
<td> Таблица остатков за текущий месяц: </br>
<table>
 <?php
  $result=mysql_query("select * from rests  
						where r_date >= (curdate()-30) 
						order by r_date",$link) or die(mysql_errno($link)." ".mysql_error($link));
  while ($rests=mysql_fetch_row($result)) {
	echo "<tr><td>".$rests[0]."</td><td>".$rests[1]."</td></tr>";
	}
 ?>
</table>
</td>
<td>
Таблица операций за сегодняшний день: </br>
<table>
 <?php
  $result=mysql_query("select a.op_date,a.op_summ,a.comment,b.priznak_text 
						from main_history as a INNER JOIN dir_pr as b
							on a.priznak=b.priznak
						where a.op_date = curdate()",$link) or die(mysql_errno($link)." ".mysql_error($link));
  while ($oper=mysql_fetch_row($result)) {
	echo "<tr><td>".$oper[0]."</td><td>".$oper[1]."</td><td>".$oper[2]."</td><td>".$oper[3]."</td></tr>";
	}
 ?>
</table>
</td>
</tr>
</TABLE>
<?php mysql_close($link); ?>
</BODY>
</html>