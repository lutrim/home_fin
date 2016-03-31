<!DOCTYPE html>
<html lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Проба пера. Учет финансов семьи.</title>
	<!-- Bootstrap -->
	<link href="Tools/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<!-- addons bootstrap-->
	<link href="Tools/datepicker/css/datepicker.css" rel="stylesheet" media="screen">
	<link href="Tools/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" media="screen">
	<script src="http://code.jquery.com/jquery-latest.min.js"></script>
	<script src="Tools/bootstrap/js/bootstrap.min.js"></script>	
	<script src="Tools/datepicker/js/bootstrap-datepicker.js"></script>
	<script charset="UTF-8" src="Tools/datepicker/js/locales/bootstrap-datepicker.ru.js"></script>
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
<SCRIPT>
var xmlhttp;
function loadXMLDoc(url,elid) //функция вызываемая при нажатии обновления остатков.
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

function r_get_url() //функция формирующая GET запрос для обновления остатков.
	{
		var arg_value = document.getElementById(arguments[1]).value;
		var str = arguments[0]+"?";
		str = str + arguments[1] + "=" + arg_value; 
			for (var i=2; i < arguments.length; i++) {
				arg_value = document.getElementById(arguments[i]).value;
				str = str + "&" + arguments[i] + "=" + arg_value;
			};
//		alert(str);
		return str;
	}

function delete_button_click() {//нажатие кнопки удаления записей
	var fields = $("input[class=delete_checkbox]");
	var data_checkbox = {};
	jQuery.each( fields, function( i, field ) {
	    if (field.checked) {data_checkbox[field.name] = field.value};
	});
	$.post(
  		"delete_pack_op.php",
  		data_checkbox,
  		onAjaxSuccess_delete
	);
}

function onAjaxSuccess_delete(data)  //Функция после успешного аякса на удаление(удаляет строки в таблице считает итого)
{
 	// Здесь мы получаем данные, отправленные сервером и выводим их на экран.
 	debugger;
 	//$('#test_div').text(data);//венуть в текстовый див значение
 	var string_for_delete = JSON.parse(data);
 	var sum_total = parseFloat($('#sum_total').text());
 	$(string_for_delete.map(function(i){
 		var cell_id = 'c' + i;
 		sum_total = sum_total - parseFloat($('#' + cell_id).text());
 		return '#' + i
 	}).join(',')).remove();
 	$('#sum_total').text(Math.round(sum_total*100)/100);
}

</SCRIPT>	
</head>
<BODY>
<?php
$link;
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
date_default_timezone_set('Europe/Minsk');
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);
?>
<DIV class="row-fluid">
	<div class="span5">
		<div class="well">

<!--**********************************************************ФОМА ВВОДА ДАННЫХ*********************************************************-->	

			<form class="form-horizontal" action="put_to_tbl.php" method="post">
				<div class="control-group">
					<label class="control-label" for="inputDate">Дата:</label>
						<div class="controls">
							<input class = "datepicker input-small" id="inputDate" type="text" name="op_date" value="<?php echo date("j m Y")?>"/>
						</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="ChooseGroup">Группа:</label>
					<div class="controls">
						<select class="selectpicker" id="ChooseGroup" name="op_group">
							<?php
								//выберем данные для статьи расхода
								echo "<optgroup label='Расход'>";
								$result=mysqli_query($link,"SELECT * FROM dir_pr where priznak < 0") or die(mysqli_errno($link)." : ".mysqli_error($link));
								//выведем результаты в HTML-документ
									while($data_pr=mysqli_fetch_row($result)) {
										echo "<option value=",$data_pr[0],">",$data_pr[1]."</option>";
									}
								echo "</optgroup>";
								//выберем данные для статьи Прихода
								echo "<optgroup label='Приход'>";
								$result=mysqli_query($link,"SELECT * FROM dir_pr where priznak > 0") or die(mysqli_errno($link)." : ".mysqli_error($link));
								//выведем результаты в HTML-документ
									while($data_pr=mysqli_fetch_row($result)) {
										echo "<option value=",$data_pr[0],">",$data_pr[1]."</option>";
									}
								echo "</optgroup>";
							?>		
						</select>
						<input type='checkbox' name='credit_check' class='credit_check_class'>
					</div>
				</div>
				<div class="control-group">
						<label class="control-label" for="inputSumm">Сумма:</label>
						<div class="controls">
							<span class="add-on" id="input-summ-sign"><i class="icon-minus"></i></span><input class = "input-medium" id="inputSumm" type="text" name="op_summ" />
						</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputComments">Комментарии:</label>
					<div class="controls">
						<textarea id="inputComments" rows=4 name="op_comm" style="resize: none;" > </textarea>
					</div>
				</div>
				<div class="control-group">
					<label class="control-label" for="inputGroup">Новая группа:</label>
					<div class="controls">
						<input id="inputGroup" type="text" name="new_group"/>
					</div>
					<div class="controls div-new-group-radio">
						<label class="radio inline">
							<input type="radio" name="new-group-radio" value="credit" checked> Расход
						</label>
						<label class="radio inline">
							<input type="radio" name="new-group-radio" value="debet"> Приход
						</label>
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary" id="save-operation" disabled="true">Схоронить</button>
					</div>
				</div>
			</form>
		</div>
		<div class="well">

<!--********************************************ФОМА РАЗРЕЗОВ ГРАФИКОВ И ИСТОРИЙ*********************************************************-->	

			<form name="show_hist" class="form-horizontal" action="show_history.php" method="post"> 
				<div class="control-group">
					<label>Показать все операции за указннный интервал:</label>
						<div class="controls controls-row">
							<input class="datepicker input-small span6" id="start_history_interval" type="text" name="s_op_date" value="1 <?php echo date("m Y") ?>" />
							<input class="datepicker input-small span6" id="end_history_interval" type="text" name="f_op_date" value="<?php echo date("j m Y")?>" />
						</div>
				</div>			
				<div class="control-group">
					<label class="control-label" for="history_groups">по группам</label>
						<div class="controls controls-row">
							<a href="#" class="span1 history-select"><i class="icon-ok"></i></a> <a href="#" class="span1 history-deselect"><i class="icon-remove"></i></a>							
							<select class="span10 selectpicker history-view" name="op_group[]" multiple data-selected-text-format="count>2" data-size="6"> 
								<?php 
									//выберем данные для статьи расхода
									echo "<optgroup label='Расход'>";
									$result=mysqli_query($link,"SELECT * FROM dir_pr where priznak < 0") or die(mysqli_errno($link)." : ".mysqli_error($link));
									//выведем результаты в HTML-документ
										while($data_pr=mysqli_fetch_row($result)) {
											echo "<option selected value=",$data_pr[0],">",$data_pr[1]."</option>";
										}
									echo "</optgroup>";
									//выберем данные для статьи Прихода
									echo "<optgroup label='Приход'>";
									$result=mysqli_query($link,"SELECT * FROM dir_pr where priznak > 0") or die(mysqli_errno($link)." : ".mysqli_error($link));
									//выведем результаты в HTML-документ
										while($data_pr=mysqli_fetch_row($result)) {
											echo "<option value=",$data_pr[0],">",$data_pr[1]."</option>";
										}
									echo "</optgroup>";
								?>	   
							</select>
						</div>	
				</div>
				<div class="control-group">
					<div class="controls">
						<button type="submit" class="btn btn-primary">Показать табличку</button>
						<button type="submit" class="btn btn-primary" onclick="show_hist.action='show_pie.php';  return true;">Рисовать кружок</button>
					</div>
				</div>
			</form>
		</div>
	</div>
	<div class="span4">
		<div class="well">

<!--********************************************ФОМА ОПЕРАЦИЙ ЗА СЕГОДНЯШНИЙ ДЕНЬ*********************************************************-->	

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
						$result=mysqli_query($link,"select a.op_summ,a.comment,b.priznak_text,a.n_op 
							from main_history as a INNER JOIN dir_pr as b
							on a.priznak=b.priznak
							where (a.op_date = curdate()) and a.priznak <> 0
							order by a.n_op") or die(mysqli_errno($link)." : ".mysqli_error($link));
							$total = 0.0;
							while ($oper=mysqli_fetch_row($result)) {
								//удаляем префикс кредита, если есть, и красим строку в красный.
								$obs_class = "info";
								if (substr($oper[1],0,6) === "CrEdIt") {
									$obs_class = "error";
									$oper[1] = substr($oper[1], 6);
								};
								echo 	"<tr id='".$oper[3]."' class='".$obs_class."'><td id='c".$oper[3]."'>".$oper[0]."</td><td>".$oper[1]."</td><td>".$oper[2]."</td>
										<td> <input class='delete_checkbox' type='checkbox' name='".$oper[3]."'/></td></tr>";
								$total += $oper[0];
							}
						echo "<tfoot><tr class='info'><th id='sum_total'>".$total."</th><th colspan='3' align='left'>Итого</th></tr></tfoot>";
					?>
				</table>
				<button type="button" onclick="delete_button_click()" class="btn btn-danger">Убрать мусор</button>
			</form>
		</div>

<!--********************************************Таблица кредита*********************************************************-->	

		<table class="table table-condensed table-bordered" id="credit_table">	
			<caption> Таблица остатков по кредиту за 15 дней </caption>
				<tr class="lut_header">
					<th> дата </th>
					<th> сумма кредита на дату</th>
				</tr>
				<?php
				$result=mysqli_query($link,"select DATE_FORMAT(r_date,'%d-%m-%Y'),rest_summ from credit_rests  
					where r_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY) or r_date = (select max(r_date) from credit_rests)
					order by r_date") or die(mysqli_errno($link)." : ".mysqli_error($link));
					while ($rests=mysqli_fetch_row($result)) {
						echo "<tr class='info'><td >".$rests[0]."</td><td >".$rests[1]."</td></tr>";
					}
				$result_credit=mysqli_query($link,"select rest_summ from credit_rests 
					where r_date=(select max(r_date) from credit_rests)") or die(mysqli_errno($link)." : ".mysqli_error($link));
				$credit_rest = mysqli_fetch_row($result_credit);
				$result_rests=mysqli_query($link,"select rest_summ from rests 
					where r_date=(select max(r_date) from rests)") or die(mysqli_errno($link)." : ".mysqli_error($link));
				$rest = mysqli_fetch_row($result_rests);
				$total = $rest[0] + $credit_rest[0];
				$total_sms = 35000 + $credit_rest[0];
				echo "<tfoot><tr><th>".$total_sms."</th><th align='left'>Эта сумма должна быть в СМС</th></tr></tfoot>";
				echo "<tfoot><tr><th>".$total."</th><th align='left'>Остаток с вычетом суммы кредита</th></tr></tfoot>";
				?>
		</table>
	</div>
	<div class="span3">
		<div class="well">

<!--********************************************ФОМА ВЫВОДА ОСТАТКОВ*********************************************************-->	

			<form class="form-hoirizontal">
				<div class="control-group">
					<label>Показать все изменения остатка за указннный интервал:</label>
					<div class="controls controls-row">
						<input class="datepicker input-small span6" type="text" id="begin_rest_interval_date" value="1 <?php echo date("m Y") ?>"/>
						<input class="datepicker input-small span6" type="text" id="end_rest_interval_date" value="<?php echo date("j m Y")?>"/> </br> 
					</div>
				</div>
				<div class="control-group">
					<div class="controls">
						<button class="btn btn-primary" type="button" 
							onclick="loadXMLDoc(r_get_url('ajax_rests.php','begin_rest_interval_date','end_rest_interval_date'),'rests_table')"> 
								Показать 
						</button>
					</div>
				</div>
			</form>
		</div>
		<!-- Таблица остатков -->
		<table class="table table-condensed table-bordered" id="rests_table">
		<caption>Таблица остатков за 15 дней:</caption>
			<!--заголовки -->
			<tr class="lut_header">
				<th > Дата</th>
				<th > Остаток на дату</th>
			</tr>
			<?php
				$result=mysqli_query($link,"select DATE_FORMAT(r_date,'%d-%m-%Y'),rest_summ from rests  
					where r_date >= DATE_SUB(CURDATE(), INTERVAL 15 DAY) or r_date=(select max(r_date) from rests)
					order by r_date") or die(mysqli_errno($link)." : ".mysqli_error($link));
					while ($rests=mysqli_fetch_row($result)) {
						echo "<tr class='info'><td >".$rests[0]."</td><td >".$rests[1]."</td></tr>";
					}
			?>
		</table>
	</div>
<SCRIPT>
$('.datepicker').datepicker({
	format: "d mm yyyy",
	weekStart: 1,
	language: "ru",
	autoclose: true,
	todayBtn: "linked",
	todayHighlight: true
});

$('.selectpicker').selectpicker();
$('.history-select').click(function() {
	$('.history-view').selectpicker('selectAll');
});
$('.history-deselect').click(function() {
	$('.history-view').selectpicker('deselectAll');
});

$('.history-select').tooltip({'trigger':'hover', 'placement':'top', 'title': 'Выделить все'});
$('.history-deselect').tooltip({'trigger':'hover', 'placement':'top', 'title': 'Снять выделение'});
$('.credit_check_class').tooltip({'trigger':'hover', 'placement':'top', 'title': 'Отметить, если расход с кредитной карты'})

$('#inputGroup').tooltip({'trigger':'focus', 'placement':'right', 'title': 'Заполнять только если надо добавить новую группу'});

$('#ChooseGroup').change(function() {
	if (this.value > 0) 
		{$("#input-summ-sign").html("<i class='icon-plus'></i>")}
		else 
			{$("#input-summ-sign").html("<i class='icon-minus'></i>")};
});

$('input[name=new-group-radio]').change(function() {
	if ($('input:radio[name=new-group-radio]:checked').val() == "debet")
		{$("#input-summ-sign").html("<i class='icon-plus'></i>")}
		else 
			{$("#input-summ-sign").html("<i class='icon-minus'></i>")};
});

$('#inputSumm').keyup(function () {
	
	var str=$(this).val();
	
	if ( /^[0-9]+,[0-9]+$/.test(str) ) {
		str = str.replace(",",".");
		$(this).val(str);
	};
	
	if ( (/^[0-9]+(\.[0-9]+)?$/.test(str))) {
		$('#inputSumm').parents('.control-group').removeClass('error');
		$('#save-operation').prop('disabled', false);
	} 
		else {
			$('#inputSumm').parents('.control-group').addClass('error');
			$('#save-operation').prop('disabled', true);
		};		
});

</SCRIPT>
</DIV>
<?php mysqli_close($link); ?>
</BODY>
</html>
