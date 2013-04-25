<!DOCTYPE html>
<html lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Утитлита смены индексов групп на новый лад (приход/расход)</title>
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
		.bootstrap-select {float: left !important;}
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
</script>		
</head>
<?php
	$link;
	$bdhost; $bdname; $bduser; $bdpass;
	include 'bdpass.php';
	include 'function_lib.php';
	putenv("TZ=Europe/Moscow");
	//-----------------------------> Connect on DB
	connect_to_db($bdname, $bdhost, $bduser, $bdpass);
		if (!empty($_POST)) {
		//$result=mysqli_query($link,"ALTER TABLE `dir_pr` CHANGE `priznak` `priznak` INT( 11 ) NOT NULL COMMENT 'индекс группы'")
		//or die(mysqli_errno($link).mysqli_error($link));
		//Задаем начальное значение индексов дебета и кредита. Начальное значение кредита -1 (0 для фора), начальное значение дебета = максимальное 
		//значение имеющегося индекса + 1. Сделано для того, чтобы не возникало коллизий при update table. Потом будет выполнено обратное преобразование.
		$result=mysqli_query($link,"select min(priznak) from dir_pr") or die(mysqli_errno($link)." : ".mysqli_error($link));
		$data_credit=mysqli_fetch_row($result);
		$flag_negative_credit = false;
		if ($data_credit[0] < 0){
			$temp_credit_index=$data_credit[0];
			$flag_negative_credit = true;
		}
				else {$temp_credit_index = 0;}
		$result=mysqli_query($link,"select max(priznak) from dir_pr") or die(mysqli_errno($link)." : ".mysqli_error($link));
		//print_r($result);
		$data_debet=mysqli_fetch_row($result);
		$temp_debet_index = $data_debet[0];
		echo "Кредит = ".$temp_credit_index."</br>Дебет = ".$temp_debet_index."</br>";
			foreach ($_POST as $key => $value){
				if ($value == "credit"){
					$temp_credit_index--;
					$update_db=mysqli_query($link,"UPDATE main_history set priznak=".$temp_credit_index." where priznak = ".substr($key,5)) or die(mysqli_errno($link)." ".mysqli_error($link));
					$update_db=mysqli_query($link,"UPDATE dir_pr set priznak=".$temp_credit_index." where priznak = ".substr($key,5)) or die(mysqli_errno($link)." ".mysqli_error($link));
					echo "старое: ".substr($key,5)." новое: ".$temp_credit_index."</br>";
				}
						else {
							$temp_debet_index++;
							$update_db=mysqli_query($link,"UPDATE main_history set priznak=".$temp_debet_index." where priznak = ".substr($key,5)) or die(mysqli_errno($link)." ".mysqli_error($link));
							$update_db=mysqli_query($link,"UPDATE dir_pr set priznak=".$temp_debet_index." where priznak = ".substr($key,5)) or die(mysqli_errno($link)." ".mysqli_error($link));
							echo "старое: ".substr($key,5)." новое: ".$temp_debet_index."</br>";
						}
			}
		echo "Кредит = ".$temp_credit_index."</br>Дебет = ".$temp_debet_index."</br> таблица обратной смены индексов дебета/кредита </br>";
		//выполняем обратное преобразование индексов дебета/кредита, чтобы они начинались от 1.
		$new_debet_index = 0;
			for ($i = $data_debet[0]+1; $i <= $temp_debet_index; $i++){
				$new_debet_index++;
				$update_db=mysqli_query($link,"UPDATE main_history set priznak=".$new_debet_index." where priznak = ".$i) or die(mysqli_errno($link)." ".mysqli_error($link));
				$update_db=mysqli_query($link,"UPDATE dir_pr set priznak=".$new_debet_index." where priznak = ".$i) or die(mysqli_errno($link)." ".mysqli_error($link));
				echo "старое: ".$i." новое: ".$new_debet_index."</br>";
			}
		if ($flag_negative_credit){
		$new_credit_index = 0;
			for ($i = $data_credit[0]-1; $i >= $temp_credit_index; $i--){
				$new_credit_index--;
				$update_db=mysqli_query($link,"UPDATE main_history set priznak=".$new_credit_index." where priznak = ".$i) or die(mysqli_errno($link)." ".mysqli_error($link));
				$update_db=mysqli_query($link,"UPDATE dir_pr set priznak=".$new_credit_index." where priznak = ".$i) or die(mysqli_errno($link)." ".mysqli_error($link));
				echo "старое: ".$i." новое: ".$new_credit_index."</br>";
			}	
		}
	}
?>
<BODY>
<form action="change_group_priznak.php" method="post">
	<table class="table table-condensed table-bordered table-hover">
		<thead>
			<tr class="lut_header">
				<th>индекс группы</th>
				<th>Описание группы</th>
				<th>Группа Расхода?</th>
				<th>Группа Прихода?</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$result=mysqli_query($link,"SELECT * FROM dir_pr") or die(mysqli_errno($link).mysqli_error($link));
				//выведем результаты в HTML-документ
					while($data_pr=mysqli_fetch_row($result)) {
						echo "<tr><td>".$data_pr[0]."</td><td>".$data_pr[1]."</td>
						<td><input type='radio' name='radio".$data_pr[0]."' value='credit' checked/></td>
						<td><input type='radio' name='radio".$data_pr[0]."' value='debet'/></td><td></tr>";
					}
			?>
		</tbody>
	</table>
	<input class="input-medium"/>
	<button class="btn" type="submit">Менять</button>
</form>

<DIV id="check_function_param">
	<form class="form-hoirizontal">
		<div class="control-group">
			<div class="controls controls-row">
				<select class="selectpicker span2 history-view" name="op_group[]" multiple data-selected-text-format="count>2" data-size="6">
					<?php 
						//выберем данные для статьи расхода
						echo "<optgroup label='Расход'>";
						$result=mysqli_query($link,"SELECT * FROM dir_pr where priznak < 0") or die(mysqli_errno($link)." : ".mysqli_error($link));
						//выведем результаты в HTML-документ
							while($data_pr=mysqli_fetch_row($result)) {
								if (in_array($data_pr[0],$_POST["op_group"])) {
									echo "<option selected value=".$data_pr[0].">".$data_pr[1]."</option>";}
									else {echo "<option value=".$data_pr[0].">".$data_pr[1]."</option>";};
							}
						echo "</optgroup>";
						//выберем данные для статьи Прихода
						echo "<optgroup label='Приход'>";
						$result=mysqli_query($link,"SELECT * FROM dir_pr where priznak > 0") or die(mysqli_errno($link)." : ".mysqli_error($link));
						//выведем результаты в HTML-документ
							while($data_pr=mysqli_fetch_row($result)) {
								if (in_array($data_pr[0],$_POST["op_group"])) {
									echo "<option selected value=".$data_pr[0].">".$data_pr[1]."</option>";}
									else {echo "<option value=".$data_pr[0].">".$data_pr[1]."</option>";};
							}
						echo "</optgroup>";
					?>	 			
				</select>
				<button class="btn span2" type="submit">Показать</button>
			</div>
			<div class="controls controls-row">
				<a href="#" class="btn btn-mini history-select">выбрать все</a> <a href="#" class="btn btn-mini history-deselect">снять выделение</a>
			</div>
		</div>
	</form>
</DIV>
<script> 
$('.selectpicker').selectpicker();
$('.history-select').click(function() {
	$('.history-view').selectpicker('selectAll');
});
$('.history-deselect').click(function() {
	$('.history-view').selectpicker('deselectAll');
});
</script>
</BODY>