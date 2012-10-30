<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Проба пера. Учет финансов семьи.</title>


	<script type="text/javascript" src="Tools/jquery_menu/jquery.min.js"></script>
    <script type="text/javascript" src="Tools/jquery_menu/jquery.date_input.js"></script>
    <script type="text/javascript">$($.date_input.initialize);</script>
	<link rel="stylesheet" href="Tools/jquery_menu/date_input.css" type="text/css"/>	
	<link rel="stylesheet" href="style.css" type="text/css"/>	
	
</head>
<BODY>
<?php
//------------------------> Подключение к БД
putenv("TZ=Europe/Moscow");

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
//установка региональных настроек, кодировка, часовой пояс
mysql_set_charset('utf8',$link); 
$result=mysql_query("SET time_zone='+4:00';",$link) or die(mysql_errno($link).mysql_error($link));
?>
<TABLE class="main">

<!-- Ввод данных и текущие данные-->

	<tr class="main">
		<td>
			<form action="put_to_tbl.php" method="post">
				Date: <input type="text" name="op_date" class="date_input" /> 
				Summ: <input type="text" name="op_summ" /> </br>
				Comments: <textarea rows=3 cols=50 name="op_comm"> </textarea> </br>
				Group: 
					<select size=1 name="op_group">
<?php
//выберем данные
	$result=mysql_query("SELECT * FROM ".$table, $link) or die(mysql_errno($link).mysql_error($link));
//выведем результаты в HTML-документ

	while($data_pr=mysql_fetch_row($result)) {
		echo "<option value=",$data_pr[0],">",$data_pr[1];
}
?>		
					</select> </br>
				New Group: <input type="text" name="new_group" /> </br> Заполнить, только если надо добавить новую группу операций </br>
				<input type="submit" value="Сохранить" />
			</form>
		</td>
		<td> 
<?php
	echo "Таблица операций за сегодняшний день ".date("d.m.Y")." : </br>";
?>
			<table class="info">
<!--заголовки -->
				<tr>
					<th class='info'> Сумма </br> операции</th>
					<th class='info'> Комментарии</th>
					<th class='info'> группа операции</th>
				</tr>
 <?php
	$result=mysql_query("select a.op_date,a.op_summ,a.comment,b.priznak_text 
						from main_history as a INNER JOIN dir_pr as b
							on a.priznak=b.priznak
						where a.op_date = curdate()",$link) or die(mysql_errno($link)." ".mysql_error($link));
		while ($oper=mysql_fetch_row($result)) {
			echo "<tr><td class='info'>".$oper[1]."</td><td class='info'>".$oper[2]."</td><td class='info'>".$oper[3]."</td></tr>";
		}
 ?>
			</table>
		</td>
		<td>
			Таблица остатков за текущий месяц: </br>
			<table class='info'>
				<!--заголовки -->
				<tr>
					<th class='info'> Дата</th>
					<th class='info'> Остаток на дату</th>
				</tr>
 <?php
	$result=mysql_query("select DATE_FORMAT(r_date,'%d-%m-%Y'),rest_summ from rests  
						where r_date >= (curdate()-30) 
						order by r_date",$link) or die(mysql_errno($link)." ".mysql_error($link));
		while ($rests=mysql_fetch_row($result)) {
			echo "<tr><td class='info'>".$rests[0]."</td><td class='info'>".$rests[1]."</td></tr>";
		}
 ?>
			</table>
		</td>
	</tr>

<!-- Фильтры и разрезы-->

	<tr class="main">
		<td> <!-- Форма вывода операций-->
			<form action="show_history.php" method="post"> 
				Показать все операции за указннный интервал: </br>
				от <input type="text" name="s_op_date" class="date_input"/> до <input type="text" name="f_op_date" class="date_input"/> </br> 
				по группам:
					<select name="op_group[]" multiple size="6"> 
<?php  
	$result=mysql_query("SELECT * FROM ".$table, $link) or die(mysql_errno($link).mysql_error($link));
	//выведем результаты в HTML-документ
	$exclde_array = array('16','17');
	while($data_pr=mysql_fetch_row($result)) {
		if (!in_array($data_pr[0],$exclde_array)) {echo "<option selected value=".$data_pr[0].">".$data_pr[1]."</option>";}
			else {echo "<option value=".$data_pr[0].">".$data_pr[1]."</option>";};
}
?>	   
					</select>
					<input type="submit" value="Показать" />
			</form>
		</td>
		<td>  <!-- Форма вывода остатков-->
			<form action="show_rests.php" method="post"> 
				Показать все изменения остатка за указннный интервал: </br>
				от <input type="text" name="s_op_date" class="date_input"/> до <input type="text" name="f_op_date" class="date_input"/> </br> 
				<input type="submit" value="Показать" />
			</form>
		</td>
	</tr>

</TABLE>
<?php mysql_close($link); ?>
</BODY>
</html>