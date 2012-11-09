<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Изменение остатка за период</title>


	<script type="text/javascript" src="Tools/jquery_menu/jquery.min.js"></script>
    <script type="text/javascript" src="Tools/jquery_menu/jquery.date_input.js"></script>
    <script type="text/javascript">$($.date_input.initialize);</script>
	<link rel="stylesheet" href="Tools/jquery_menu/date_input.css" type="text/css"/>	
	<link rel="stylesheet" href="style.css" type="text/css"/>	
	
</head>
<BODY>
<?php
$link;
putenv("TZ=Europe/Moscow");
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);
?>
<TABLE class="main">
<TR><TD>
  <form action="show_rests.php" method="post"> 
  Показать все изменения остатка за указннный интервал: </br>
  от <input type="text" name="s_op_date" class="date_input" value="<?php echo $_POST['s_op_date']?>"/> 
  до <input type="text" name="f_op_date" class="date_input" value="<?php echo $_POST['f_op_date']?>"/> </br> 
     <input type="submit" value="Показать" />
  </form>
</TR></TD>
<TR><TD>
<?php
//блок преобразования дат в удобоваримый для php & mysql
$date_tmp=explode(" ",$_POST["s_op_date"]);
$s_date_invers=$date_tmp[2];
for ($i=1;$i>=0;$i--){
if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
$s_date_invers .="-".$date_tmp[$i];
}
$date_tmp=explode(" ",$_POST["f_op_date"]);
$f_date_invers=$date_tmp[2];
for ($i=1;$i>=0;$i--){
if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
$f_date_invers .="-".$date_tmp[$i];
}
//блок преобразования дат завершен

//echo date_create($f_date_invers)->format('d-m-Y')."   <    ".date_create($s_date_invers)->format('d-m-Y')."</br>";
//блок проверок. Проверка на правильность параметров:
$data=date_create($f_date_invers);
if (!$data) {
	echo "Один из параметров введен не верно. Еще раз? </br>";
	echo "<a href=index.php> Вернуться на главную страницу</a>";
	die;
}
$data=date_create($s_date_invers);
if (!$data) {
	echo "Один из параметров введен не верно. Еще раз? </br>";
	echo "<a href=index.php> Вернуться на главную страницу</a>";
	die;
}
//проверка на правильность интервала:
if (date_create($f_date_invers) < date_create($s_date_invers)) {
	echo "Дата окончания интервала  меньше даты начала интервала, внимательнее надо быть. Введи еще раз параметры </br>";
	echo "<a href=index.php> Вернуться на главную страницу</a>";
	die;
}
//блок проверок завершен
 
//$date_invers = Date_create($_POST["s_op_date"]);
//echo $date_invers->format('Y-m-d H:i:s')." create ".$s_date_invers; die;

//print_r($_POST); 
//echo " ПОСТ </br></br></br>";

?>

<!-- таблица остатков -->
	<table class='info'>
	<!--заголовки -->
		<tr>
			<th class='info'> Дата</th>
			<th class='info'> Остаток на дату</th>
		</tr>
	<?php
	$result=mysql_query("select DATE_FORMAT(r_date,'%d-%m-%Y'),rest_summ from rests  
							where r_date between '".$s_date_invers."' and '".$f_date_invers."'
							order by r_date",$link) or die(mysql_errno($link)." ".mysql_error($link));
	while ($rests=mysql_fetch_row($result)) {
		echo "<tr><td class='info'>".$rests[0]."</td><td class='info'>".$rests[1]."</td></tr>";
		}
	?>
	</table>


<?php mysql_close($link); ?>
</TR></TD>
</TABLE>
	<a href=index.php> Вернуться на главную страницу</a>
</BODY>
</html>