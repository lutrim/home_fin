<?php
$link;
putenv("TZ=Europe/Moscow");
$bdhost; $bdname; $bduser; $bdpass;
include 'bdpass.php';
include 'function_lib.php';
//-----------------------------> Connect on DB
connect_to_db($bdname, $bdhost, $bduser, $bdpass);
//print_r($_POST); 
//echo " ПОСТ </br></br></br>";
?>
<!-- таблица остатков -->
	<!--заголовки -->
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

<?php mysqli_close($link); ?>