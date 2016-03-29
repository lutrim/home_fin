window.onload = function () {
	var chart = new CanvasJS.Chart("chartContainer",
	{
		title:{
			text: "Статистика трат по статьям"
		},
		legend: {
			maxWidth: 800,
			itemWidth: 200
		},
		data: [
		{
			type: "pie",
			showInLegend: true,
			legendText: "{indexLabel}",
			dataPoints: [
			<?php 
			while ($oper=mysqli_fetch_row($result)) {
			echo "{ y: ".$oper[0].", indexLabel: '".$oper[1]." (".abs($oper[0]).")' },"			
			?>
			{ y: 0, indexLabel: 'Nope'}
			]
		}
		]
	});
	chart.render();
}
