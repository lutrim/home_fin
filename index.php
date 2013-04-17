<!DOCTYPE html>
<html lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Проба пера. Учет финансов семьи.</title>
	<link rel="stylesheet" href="style.css" type="text/css"/>
	<!-- Bootstrap -->
	<link href="Tools/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<!-- addons bootstrap-->
	<link href="Tools/datepicker/css/datepicker.css" rel="stylesheet" media="screen">
	<link href="Tools/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" media="screen">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="Tools/bootstrap/js/bootstrap.min.js"></script>	
	<script src="Tools/datepicker/js/bootstrap-datepicker.js"></script>
	<script src="Tools/bootstrap-select/bootstrap-select.min.js"></script>
<!-- добавляем оверстайл, для изменения стиля body от bootstrap -->    
	<style>
    	body
			{ 
			background: #fcf8e3;
			padding: 20px; 
			}
		.lut_header{background: #CDCDCD;}
	</style>	
<script>
var xmlhttp;
function loadXMLDoc(url,elid)
{
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function ()
		{
			if (xmlhttp.readyState==4 && xmlhttp.status==200)
				{
				document.getElementById(elid).innerHTML=xmlhttp.responseText;
				}
		};
xmlhttp.open("GET",url,true);
xmlhttp.send();
}

		function r_get_url()
{
	var s_date=document.getElementById("r_s_op_date").value;
	var f_date=document.getElementById("r_f_op_date").value;
	var str = "ajax_rests.php?s_op_date=" + s_date + "&f_op_date=" + f_date;
//	alert(str);
	return str;
}
</script>	
</head>
<BODY>
<?php
$link;
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
putenv("TZ=Europe/Moscow");
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);
?>
<DIV class="row-fluid">
	<div class="span9">
		<div class="row-fluid">
			<div class="span6 well">
	<!-- Ввод данных и текущие данные-->
			<form class="form-horizontal" action="put_to_tbl.php" method="post">
				<div class="control-group">
					<label class="control-label" for="inputDate">Дата:</label>
						<div class="controls">
							<input class = "datepicker input-small" id="inputDate" type="text" name="op_date" value="<?php echo date("j m Y")?>" />
						</div>
				</div>
				<div class="control-group">
						<label class="control-label" for="inputSumm">Сумма:</label>
						<div class="controls">
							<input class = "input-medium" id="inputSumm" type="text" name="op_summ" />
						</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputComments">Комментарии:</label>
					<div class="controls">
						<textarea id="inputComments" rows=4 name="op_comm" style="resize: none;" > </textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="ChooseGroup">Группа:</label>
					<div class="controls">
						<select class="selectpicker" id="ChooseGroup" name="op_group">

<?php
//выберем данные
	$result=mysql_query("SELECT * FROM dir_pr", $link) or die(mysql_errno($link).mysql_error($link));
//выведем результаты в HTML-документ

	while($data_pr=mysql_fetch_row($result)) {
		echo "<option value=",$data_pr[0],">",$data_pr[1];
}
?>		
					</select>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputGroup">Новая группа:</label>
					<div class="controls">
						<input id="inputGroup" type="text" name="new_group"/>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn">Сохранить</button>
					</div>
				</div>
			</form>
			</div>
			<div class="span6 well">
			<form action="delete_pack_op.php" method="post">
			<table class="table table-condensed table-bordered" id="operation_table">
			<?php
				echo "<caption>Таблица операций за сегодняшний день ".date("d.m.Y").":</caption>";
			?>			
<!--заголовки -->
				<tr class="lut_header">
					<th> Сумма </br> операции</th>
					<th> Комментарии</th>
					<th> Группа операции</th>
					<th> </th>
				</tr>
 <?php
	$result=mysql_query("select a.op_summ,a.comment,b.priznak_text,a.n_op 
						from main_history as a INNER JOIN dir_pr as b
							on a.priznak=b.priznak
						where (a.op_date = curdate()) and a.priznak <> 0",$link) or die(mysql_errno($link)." ".mysql_error($link));
			while ($oper=mysql_fetch_row($result)) {
			echo "<tr class='info'><td>".$oper[0]."</td><td>".$oper[1]."</td><td>".$oper[2]."</td>
			<td> <input type='checkbox' name='".$oper[3]."'/></td></tr>";
		}
 ?>
			</table>
			<button type="submit" class="btn">удалить отмеченные</button>
			</form>
			</div>
		</div>
		<div class="row-fluid">
<!-- Фильтры и разрезы-->
			<div class="span6 well">
		<!-- Форма вывода операций-->
			<form class="form-horizontal" action="show_history.php" method="post"> 
				<div class="control-group">
					<label>Показать все операции за указннный интервал:</label>
						<div class="controls controls-row">
<!--							<label class="control-label span1" for="start_history_interval">от</label>-->
							<input class="datepicker input-small span6" id="start_history_interval" type="text" name="s_op_date" value="1 <?php echo date("m Y") ?>" />
<!--							<label class="control-label span1" for="end_history_interval">до</label>-->
							<input class="datepicker input-small span6" id="end_history_interval" type="text" name="f_op_date" value="<?php echo date("j m Y")?>" />
						</div>
				</div>			
				<div class="control-group">
					<label class="control-label" for="history_groups">по группам</label>
						<div class="controls">						
							<select class="selectpicker" name="op_group[]" multiple data-selected-text-format="count>2" data-size="6"> 
<?php  
	$result=mysql_query("SELECT * FROM dir_pr", $link) or die(mysql_errno($link).mysql_error($link));
	//выведем результаты в HTML-документ
	$exclde_array = array('16','17');
	while($data_pr=mysql_fetch_row($result)) {
		if (!in_array($data_pr[0],$exclde_array)) {echo "<option selected value=".$data_pr[0].">".$data_pr[1]."</option>";}
			else {echo "<option value=".$data_pr[0].">".$data_pr[1]."</option>";};
}
?>	   
							</select>
						</div>	
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn" >Показать</button>
					</div>
				</div>
			</form>
			</div>
			<div class="span6 well">
		<!-- Форма вывода остатков-->
			<form class="form-hoirizontal">
				<div class="control-group">
					<label>Показать все изменения остатка за указннный интервал:</label>
					<div class="controls controls-row">
						<input class="datepicker input-small span6" type="text" id="r_s_op_date" name="s_op_date" value="1 <?php echo date("m Y") ?>"/>
						<input class="datepicker input-small span6" type="text" id="r_f_op_date" name="f_op_date" value="<?php echo date("j m Y")?>"/> </br> 
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button class="btn" type="button" onclick="loadXMLDoc(r_get_url(),'rests_table')"> Показать </button>
					</div>
				</div>
			</form>
			</div>
		</div>
	</div>
<!-- Таблица остатков -->
	<div class="span3">
			<table class="table table-condensed table-bordered" id="rests_table">
			<caption>Таблица остатков за текущий месяц:</caption>
				<!--заголовки -->
				<tr class="lut_header">
					<th > Дата</th>
					<th > Остаток на дату</th>
				</tr>
 <?php
	$result=mysql_query("select DATE_FORMAT(r_date,'%d-%m-%Y'),rest_summ from rests  
						where r_date >= (curdate()-15) 
						order by r_date",$link) or die(mysql_errno($link)." ".mysql_error($link));
		while ($rests=mysql_fetch_row($result)) {
			echo "<tr class='info'><td >".$rests[0]."</td><td >".$rests[1]."</td></tr>";
		}
 ?>
			</table>
	</div>
<script> 
$('.datepicker').datepicker({
    format: 'd mm yyyy',
	weekStart: 1
});
$('.selectpicker').selectpicker();
$('#inputGroup').tooltip({'trigger':'focus', 'placement':'right', 'title': 'Заполнять только если надо добавить новую группу'});
</script>
</DIV>
<?php mysql_close($link); ?>
</BODY>
</html>