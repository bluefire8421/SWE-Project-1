<?php
require './vendor/autoload.php';

$server = 'cs2.mwsu.edu';
$username = 'sbeaver';
$password = 'sbeaver2019!!!';
$db = 'sbeaver';
// Create connection server, username, password, database
$con = mysqli_connect($server, $username, $password, $db);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$result = $con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID, round(avg(azbioResults.Score)) as azbioScore, 
round(avg(bkbResults.`SNR-50`),1) as SNR50 from azbioResults join bkbResults on azbioResults.PatientID=bkbResults.PatientID and azbioResults.ConditionsID =bkbResults.ConditionsID 
and azbioResults.TestDate = bkbResults.TestDate and azbioResults.Score!=0 and bkbResults.`SNR-50`> -23 where azbioResults.TestDate!=0000 and 
bkbResults.`SNR-50` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");

$data1 = [];
$dataPoints1=[];

//while rows, get row and add data to corresponding array
while ($row = $result->fetch_assoc()) {
    $data1[$row['azbioScore']][] = $row['SNR50'];
}

foreach ($data1 as $key => $value) {
    $avg = round(array_sum($value) / count($value));
    $data1[$key] = $avg;
}

foreach ($data1 as $key => $value) {
	
    array_push($dataPoints1, (array("x" => doubleval($key), "y" => intval($value))));
}


?>
<!DOCTYPE HTML>
<html>
<head>
<script>
window.onload = function () {

var chart = new CanvasJS.Chart("chartContainer", {
	animationEnabled: true,
	title:{
		text: "Scores Relation"
	},
	axisX: {
		title:"AzBio Scores"
	},
	axisY:{
		title: "BKB Scores"
	},
	legend:{
		cursor: "pointer",
		itemclick: toggleDataSeries
	},
	data: [
	{
		type: "scatter",
		name: "BKB Score",
		markerType: "square",
		showInLegend: true,
		dataPoints: <?php echo json_encode($dataPoints1); ?>,
        color: "Indigo"
	}],
    
	/*{
		type: "scatter",
		name: "Words with 3 Phonemes Correct",
		markerType: "triangle",
		showInLegend: true,
		dataPoints: 
        <?php 
        // echo json_encode($dataPoints2); ?>
	}*/
	
});

chart.render();

function toggleDataSeries(e){
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else{
		e.dataSeries.visible = true;
	}
	chart.render();
}

}
</script>
</head>
<body>
<div id="chartContainer" style="height: 370px; width: 100%;"></div>

<!-- <div><?php 
// foreach ($dataPoints1 as $key=>$value){
// 	print_r($dataPoints1[$key]);
// }
?></div> -->
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>