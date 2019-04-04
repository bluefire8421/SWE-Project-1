
<?php
require './.config.php';


// Create connection server, username, password, database
$con = mysqli_connect($HOST, $DBUSERNAME, $DBPASSWORD, $DBNAME);

// Check connection
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
//query
$result = $con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID, 
    round(avg(azbioResults.Score)) as azbioScore, round(avg(cncResults.`Phonemes Correct`)) as PhonemesCorrect,
    round(avg(cncResults.`Words with 3 Phonemes Correct`)) as ThreeWordsCorrect from azbioResults 
    join cncResults on azbioResults.PatientID=cncResults.PatientID and azbioResults.ConditionsID =cncResults.ConditionsID and azbioResults.TestDate = cncResults.TestDate and azbioResults.Score!=0 and `cncResults`.`Words with 3 Phonemes Correct`!=0 and `cncResults`.`Phonemes Correct`!=0 
    where azbioResults.TestDate!=0000 and `cncResults`.`Words with 3 Phonemes Correct` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");
$data=[];
$data2=[];
//while rows, get row and add data to corresponding array
while ($row = $result->fetch_assoc()) {
    $data[$row['azbioScore']][]=$row['PhonemesCorrect'];
    $data2[$row['azbioScore']][]=$row['ThreeWordsCorrect'];
    // $x[] = $row['azbioScore'];
    // $y[] = $row['PhonemesCorrect'];
    // $y2[] = $row['ThreeWordsCorrect'];
    // $Result[] = $row;
    // print_r("{".$row['azbioScore'].",".$row['PhonemesCorrect']."},");
}

foreach($data as $key=>$value)
    {
        // print_r($value);
        $avg=round(array_sum($value)/count($value));
        $data[$key]=$avg;
    }
foreach($data2 as $key=>$value)
{
    // print_r($value);
    $avg=round(array_sum($value)/count($value));
    $data2[$key]=$avg;
}
$x=array_keys($data);
$y=array_values($data);
$y2=array_values($data2);
$line=linear_regression($x,$y);
$line2=linear_regression($x,$y2);

print_r("Line 1 P= ".$line['slope'] ." A + ".$line['intercept']."\n");
print_r("Line 2 W= ".$line2['slope'] ." A + ".$line2['intercept']."\n\n");

$content="{";
$remedial="{";

foreach($data as $key=>$value)
{
    $content.="{".$key.",".$value."},";
    $answer=abs($data[$key]-round($line['slope']*$key+$line['intercept']));
    $remedial.="{".$key.",".$answer."},";
}

$content.="}";
$remedial.="}";
// file_put_contents("points.txt", $content);
file_put_contents("residuals.txt", $content);



// $answer=linear_regression($x,$y);
$line = linear_regression($x, $y);
$line2 = linear_regression($y, $x);
print_r("Line 1 C= ".$line['slope'] ." A + ".$line['intercept']."\n");
print_r("Line 2 A= ".$line2['slope'] ." C + ".$line2['intercept']."\n\n");
$slope=1/$line['slope'];
$intercept=$line['intercept']/$line['slope'];
print_r("Line 1 converted A= ".$slope." C - ".$intercept."\n");



$slope=1/$line2['slope'];
$intercept=$line2['intercept']/$line2['slope'];

print_r("Line 2 converted C= " . $slope." A - ".$intercept."\n");

//uses equation below
//(NΣXY - (ΣX)(ΣY)) / (NΣX2 - (ΣX)2)
function linear_regression($x, $y)
{

    $n = count($x); // number of items in the array
    $x_sum = array_sum($x); // sum of all X values
    $y_sum = array_sum($y); // sum of all Y values

    $xx_sum = 0;
    $xy_sum = 0;

    for ($i = 0; $i < $n; $i++) {
        $xy_sum += ($x[$i] * $y[$i]);
        $xx_sum += ($x[$i] * $x[$i]);
    }

    // Slope
    $slope = (($n * $xy_sum) - ($x_sum * $y_sum)) / (($n * $xx_sum) - ($x_sum * $x_sum));

    // calculate intercept
    $intercept = ($y_sum - ($slope * $x_sum)) / $n;

    return array(
        'slope' => $slope,
        'intercept' => $intercept,
    );
}


?>

