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

$result = $con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID,
	round(avg(azbioResults.Score)) as azbioScore, round(avg(cncResults.`Phonemes Correct`)) as PhonemesCorrect,
	round(avg(cncResults.`Words with 3 Phonemes Correct`)) as ThreeWordsCorrect from azbioResults
	join cncResults on azbioResults.PatientID=cncResults.PatientID and azbioResults.ConditionsID =cncResults.ConditionsID and azbioResults.TestDate = cncResults.TestDate and azbioResults.Score!=0 and `cncResults`.`Words with 3 Phonemes Correct`!=0 and `cncResults`.`Phonemes Correct`!=0
	where azbioResults.TestDate!=0000 and `cncResults`.`Words with 3 Phonemes Correct` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");
$dataPoints1 = array();
$dataPoints2 = array();

while ($row = $result->fetch_assoc()) {
    $data1[$row['azbioScore']][] = $row['PhonemesCorrect'];
    $data2[$row['azbioScore']][] = $row['ThreeWordsCorrect'];
    // $x[] = $row['azbioScore'];
    // $y[] = $row['PhonemesCorrect'];
    // $y2[] = $row['ThreeWordsCorrect'];
    // $Result[] = $row;
}


//injecting divs for styling
foreach ($data1 as $key => $value) {
    // print_r($value);
    $avg = round(array_sum($value) / count($value));
    $data1[$key] = $avg;
}
foreach ($data2 as $key => $value) {
    // print_r($value);
    $avg = round(array_sum($value) / count($value));
    $data2[$key] = $avg;
}
foreach ($data1 as $key => $value) {
	
    array_push($dataPoints1, (array("x" => doubleval($key), "y" => intval($value))));
}
foreach ($data2 as $key => $value) {
    array_push($dataPoints2, (array("x" => doubleval($key), "y" => intval($value))));
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
		title:"BKB Scores"
	},
	axisY:{
		title: "CNC Scores"
	},
	legend:{
		cursor: "pointer",
		itemclick: toggleDataSeries
	},
	data: [
	{
		type: "scatter",
		name: "Phonemes Correct",
		markerType: "square",
		showInLegend: true,
		dataPoints: <?php echo json_encode($dataPoints1); ?>
	},
	{
		type: "scatter",
		name: "Words with 3 Phonemes Correct",
		markerType: "triangle",
		showInLegend: true,
		dataPoints: <?php echo json_encode($dataPoints2); ?>
	}
	]
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

<!-- <div>
	<?php 
// 	foreach ($dataPoints1 as $key=>$value){
// 	print_r($dataPoints1[$key]);
// }
?>
</div> -->
<script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>
</html>