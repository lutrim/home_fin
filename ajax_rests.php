<?php
$link;
putenv("TZ=Europe/Moscow");
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);

//блок преобразования дат в удобоваримый для php & mysql
$s_date_invers = inverse_date($_GET["begin_rest_interval_date"]);
$f_date_invers = inverse_date($_GET["end_rest_interval_date"]);
//$date_tmp=explode(" ",$_GET["begin_rest_interval_date"]);
//$s_date_invers=$date_tmp[2];
//for ($i=1;$i>=0;$i--){
//if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
//$s_date_invers .="-".$date_tmp[$i];
//}
//$date_tmp=explode(" ",$_GET["end_rest_interval_date"]);
//$f_date_invers=$date_tmp[2];
//for ($i=1;$i>=0;$i--){
//if (strlen($date_tmp[$i])==1) {$date_tmp[$i] = "0".$date_tmp[$i];};
//$f_date_invers .="-".$date_tmp[$i];
//}
//блок преобразования дат завершен
//echo $s_date_invers;
//echo date_create($f_date_invers)->format('d-m-Y')."   <    ".date_create($s_date_invers)->format('d-m-Y')."</br>";
//блок проверок. Проверка на правильность параметров:
$data=date_create($f_date_invers);
if (!$data) {
	echo "Один из параметров введен не верно. Еще раз? </br>";
	die;
}
$data=date_create($s_date_invers);
if (!$data) {
	echo "Один из параметров введен не верно. Еще раз? </br>";
	die;
}
//проверка на правильность интервала:
if (date_create($f_date_invers) < date_create($s_date_invers)) {
	echo "Дата окончания интервала  меньше даты начала интервала, внимательнее надо быть. Введи еще раз параметры </br>";
	die;
}
//блок проверок завершен
 
//$date_invers = Date_create($_GET["s_op_date"]);
//echo $date_invers->format('Y-m-d H:i:s')." create ".$s_date_invers; die;

//print_r($_GET); 
//echo " ПОСТ </br></br></br>";
?>
<!-- таблица остатков -->
	<!--заголовки -->
	<caption>Таблица остатков за выбранный интервал дат:</caption>
		<tr class="lut_header">
			<th > Дата</th>
			<th > Остаток на дату</th>
		</tr>
<?php
	$result=mysqli_query($link,"select DATE_FORMAT(r_date,'%d-%m-%Y'),rest_summ from rests  
							where r_date between '".$s_date_invers."' and '".$f_date_invers."'
							order by r_date") or die(mysqli_errno($link)." : ".mysqli_error($link));
	while ($rests=mysqli_fetch_row($result)) {
		echo "<tr class='info'><td >".$rests[0]."</td><td >".$rests[1]."</td></tr>";
		}

mysqli_close($link); ?>