<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Круговая диаграмма.</title>
	<!-- Bootstrap -->
	<link href="Tools/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
	<!-- addons bootstrap-->
	<link href="Tools/datepicker/css/datepicker.css" rel="stylesheet" media="screen">
	<link href="Tools/bootstrap-select/bootstrap-select.min.css" rel="stylesheet" media="screen">
	<script src="http://code.jquery.com/jquery-latest.js"></script>
	<script src="Tools/bootstrap/js/bootstrap.min.js"></script>	
	<script src="Tools/datepicker/js/bootstrap-datepicker.js"></script>
	<script charset="UTF-8" src="Tools/datepicker/js/locales/bootstrap-datepicker.ru.js"></script>
	<script src="Tools/bootstrap-select/bootstrap-select.min.js"></script>
	<script type="text/javascript" src="Tools/canvasjs/canvasjs.min.js"></script>
<!-- добавляем оверстайл, для изменения некоторых стилей bootstrap -->    
	<style>
    	body { background: #fcf8e3; }
		.table-nonfluid { width: auto; }
		.lut_header{background: #CDCDCD;}
		.bootstrap-select {float: left !important;}
	</style>
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
<TABLE class="table table-bordered">
<?php
//print_r($_POST); echo "</br>";
//print_r($_POST["op_group"]); echo "</br>"; die; 
?>
<TR><TD>
	<form class="form-horizontal" action="show_pie.php" method="post"> 
		<label>Показать круговой график за указннный интервал:</label>
		<div class="control-group">
			<div class="controls controls-row">
				<input class="datepicker input-small span2" type="text" name="s_op_date" value="<?php echo $_POST['s_op_date']?>"/> 
				<input class="datepicker input-small span2" type="text" name="f_op_date" value="<?php echo $_POST['f_op_date']?>"/> </br>
			</div>
		</div>
		<label>показывать в графике указанные группы:</label>
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
				<a href="#" class="span1 history-select"><i class="icon-ok"></i></a> <a href="#" class="span1 history-deselect"><i class="icon-remove"></i></a>							
			</div>
			<div class="controls">
				<button class="btn btn-primary" type="submit">Рисовать</button>				
			</div>
		</div>
	</form>
</TD></TR>
<TR><TD>
<script>
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

</script>
<?php
//блок преобразования дат в удобоваримый для php & mysql
$s_date_invers=inverse_date($_POST["s_op_date"]);
$f_date_invers=inverse_date($_POST["f_op_date"]);

//блок преобразования дат завершен

//echo date_create($f_date_invers)->format('d-m-Y')."   <    ".date_create($s_date_invers)->format('d-m-Y')."</br>";
//блок проверок. Проверка на правильность параметров или на их отсутствие:
$flag_null=0; //флаг отсусттвия концов интервала(левого, правого или обоих, для формирования последующего запроса)
if ($_POST["s_op_date"] == "") { $flag_null=1;}
	else {
	$data=date_create($s_date_invers);
		if (!$data) {
			echo "Дата начала интервала введена неверно. Еще раз? </br>";
			echo "<a class='btn btn-info' href='index.php'> Вернуться на главную страницу</a>";
			die;
		};
	}
if ($_POST["f_op_date"] == "") { $flag_null += 2;}
	else {
	$data=date_create($f_date_invers);
		if (!$data) {
			echo "Дата конца интервала введена неверно. Еще раз? </br>";
			echo "<a class='btn btn-info' href='index.php'> Вернуться на главную страницу</a>";
			die;
		};
	}	
//проверка на правильность интервала:
if ($flag_null == 0) {
	if (date_create($f_date_invers) < date_create($s_date_invers)) {
		echo "Дата окончания интервала  меньше даты начала интервала, внимательнее надо быть. Введи еще раз параметры </br>";
		echo "<a class='btn btn-info' href='index.php'> Вернуться на главную страницу</a>";
		die;
	};
}

//проверка на присутствие выбранных групп
if (empty($_POST["op_group"])){
	echo "Не выбрана ни одна из групп операция для отображения Введи еще раз параметры </br>";
	echo "<a class='btn btn-info' href='index.php'> Вернуться на главную страницу</a>";
	die;
}

//блок проверок завершен
 
//$date_invers = Date_create($_POST["s_op_date"]);
//echo $date_invers->format('Y-m-d H:i:s')." create ".$s_date_invers; die;

?>
<a class="btn btn-info" href="index.php"> Вернуться на главную страницу</a></br></br>
<?php 
	/*echo "select sum(op_summ) from main_history 
							where (op_date between '".$s_date_invers."' and '".$f_date_invers."') 
							group by op_summ </br>"*/
?>	
<?php
/*сase для выбора запроса, в зависимости от указанного интервала*/
switch ($flag_null) {
	case 0: //обе даты
		$result=mysqli_query($link,"select sum(a.op_summ) as total_summ,b.priznak_text
								from main_history as a INNER JOIN dir_pr as b
									on a.priznak=b.priznak
								where (a.op_date between '".$s_date_invers."' and '".$f_date_invers."') and 
								(a.priznak in ('".implode("','",$_POST["op_group"])."')) 
								group by b.priznak_text
								having sum(a.op_summ) <> 0") 
								or die(mysqli_errno($link)." : ".mysqli_error($link));
		break;
	case 1: //дата только справа
		$result=mysqli_query($link,"select sum(a.op_summ) as total_summ,b.priznak_text
								from main_history as a INNER JOIN dir_pr as b
									on a.priznak=b.priznak
								where (a.op_date <= '".$f_date_invers."') and 
								(a.priznak in ('".implode("','",$_POST["op_group"])."')) 
								group by b.priznak_text
								having sum(a.op_summ) <> 0") 
								or die(mysqli_errno($link)." : ".mysqli_error($link));
		break;
	case 2: //дата только слева
		$result=mysqli_query($link,"select sum(a.op_summ) as total_summ,b.priznak_text
								from main_history as a INNER JOIN dir_pr as b
									on a.priznak=b.priznak
								where (a.op_date >= '".$s_date_invers."') and 
								(a.priznak in ('".implode("','",$_POST["op_group"])."')) 
								group by b.priznak_text
								having sum(a.op_summ) <> 0") 
								or die(mysqli_errno($link)." : ".mysqli_error($link));
		break;
	case 3: //дат нет совсем
		$result=mysqli_query($link,"select sum(a.op_summ) as total_summ,a.comment,b.priznak_text
								from main_history as a INNER JOIN dir_pr as b
									on a.priznak=b.priznak
								where (a.priznak in ('".implode("','",$_POST["op_group"])."')) 
								group by b.priznak_text
								having sum(a.op_summ) <> 0") 
								or die(mysqli_errno($link)." : ".mysqli_error($link));
		break;
}
$total=0.0;
?>
<script type="text/javascript">
	window.onload = function () {
	var chart = new CanvasJS.Chart("chartContainer",
	{
		title:{
			text: "Статистика трат по статьям"
		},
		backgroundColor: "#fcf8e3",
		animationEnabled: true,
		legend:{
			verticalAlign: "center",
			horizontalAlign: "left",
			fontSize: 20,
			fontFamily: "Helvetica"        
		},
		data: [
		{
			type: "pie",
			showInLegend: true,
			legendText: "{indexLabel} {y}",
			indexLabel: "{indexLabel}",
			toolTipContent:"{y} - #percent %",
			dataPoints: [
			<?php 
			$i=1;
			while ($i < mysqli_num_rows($result)) {
				$oper=mysqli_fetch_row($result);
				echo "{ y: ".abs($oper[0]).", indexLabel: '".$oper[1]."' },";
				$i++;			
			}
				$oper=mysqli_fetch_row($result);
				echo "{ y: ".abs($oper[0]).", indexLabel: '".$oper[1]."' }";
			?>
			]
		}
		]
	});
	chart.render();
}
</script>
<?php mysqli_close($link); ?>
	<div id="chartContainer" style="height: 750px; width: 100%;"></div>
</TD></TR>
</TABLE>
	<a class="btn btn-info" href="index.php"> Вернуться на главную страницу</a></br></br></br>
</BODY>
</html>
