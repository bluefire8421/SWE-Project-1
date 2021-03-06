<html>
    <head>
        <link rel = "stylesheet" type="text/css" href="./template.css">
    </head>

<body class="pBack">
    <div class="mForm">
    <div class="formContent">
    <?php
    require './.config.php';


    // Create connection server, username, password, database
    $con = mysqli_connect($HOST, $DBUSERNAME, $DBPASSWORD, $DBNAME);

    // Check connection
    if (mysqli_connect_errno()) {
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
    }
    //query

    $result = $con->query("SELECT DISTINCT bkbResults.PatientID,bkbResults.TestDate, bkbResults.ConditionsID, round(avg(bkbResults.`SNR-50`),1) as bkbScore, 
    round(avg(cncResults.`Phonemes Correct`)) as PhonemesCorrect, round(avg(cncResults.`Words with 3 Phonemes Correct`)) as ThreeWordsCorrect 
    from bkbResults join cncResults on bkbResults.PatientID=cncResults.PatientID and bkbResults.ConditionsID =cncResults.ConditionsID and 
    bkbResults.TestDate = cncResults.TestDate and bkbResults.`SNR-50` > -23 and `cncResults`.`Words with 3 Phonemes Correct`!=0 and 
    `cncResults`.`Phonemes Correct`!=0 where bkbResults.TestDate!=0000 and `cncResults`.`Words with 3 Phonemes Correct` is NOT null group by 
    bkbResults.PatientID, bkbResults.TestDate, bkbResults.ConditionsID");
$data1 = [];
$data2 = [];
//while rows, get row and add data to corresponding array
while ($row = $result->fetch_assoc()) {
    $data1[$row['bkbScore']][] = $row['PhonemesCorrect'];
    $data2[$row['bkbScore']][] = $row['ThreeWordsCorrect'];
}

//injecting divs for styling
print("<div class=\"mForm\">");
print("<div class=\"formContent\">");
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

if (isset($_POST['BKB'])) {
  
    $x = array_keys($data1);
    $y = array_values($data1);
    $y2 = array_values($data2);

    $line = linear_regression($x, $y);
    $line2 = linear_regression($x, $y2);

    $linereverse = linear_regression($y, $x);
    $line2reverse = linear_regression($y2, $x);

    $slope=1/$linereverse['slope'];
    $intercept=$linereverse['intercept']/$linereverse['slope'];
    $linereverse['slope']=$slope;
    $linereverse['intercept']=$intercept;
    $slope=1/$line2reverse['slope'];
    $intercept=$line2reverse['intercept']/$line2reverse['slope'];
    $line2reverse['slope']=$slope;
    $line2reverse['intercept']=$intercept;
    
    //print_r("B = " . $line['slope'] . "C + " . $line['intercept'] . "\n");
    //print_r("B = " . $line2['slope'] . "C + " . $line2['intercept'] . "\n");
    
    $convert = $_POST['BKB'];
    $answer = floor($line['slope'] * intval($convert) + $line['intercept']);
    $answerreverse = ceil($linereverse['slope'] * intval($convert) - $linereverse['intercept']);
    print("<br><hr>");
    print("<br/>Phonemes Correct:<br/>");
    if ($answer <= $answerreverse) {
        print($answer . "-" . $answerreverse);
    } else {
        print($answerreverse . "-" . $answer);
    }

    $answer2 = floor($line2['slope'] * intval($convert) + $line2['intercept']);
    $answer2reverse = ceil($line2reverse['slope'] * intval($convert) - $line2reverse['intercept']);
    print("<br><hr>");
    print("<br/>Words with 3 Phonemes Correct:<br/>");
    if ($answer2 <= $answer2reverse) {
        print($answer2 . "-" . $answer2reverse);
    } else {
        print($answer2reverse . "-" . $answer2);
    }

    print("<br><hr><br>");

} else if (isset($_POST['CncPhonemes'])) {
    $x = array_keys($data1);
    $y = array_values($data1);
    $y2 = array_values($data2);
    $line = linear_regression($y, $x);
    $line2 = linear_regression($y2, $x);
    $linereverse = linear_regression($x, $y);
    $line2reverse = linear_regression($x, $y2);

    $slope=1/$linereverse['slope'];
    $intercept=$linereverse['intercept']/$linereverse['slope'];
    $linereverse['slope']=$slope;
    $linereverse['intercept']=$intercept;
    $slope=1/$line2reverse['slope'];
    $intercept=$line2reverse['intercept']/$line2reverse['slope'];
    $line2reverse['slope']=$slope;
    $line2reverse['intercept']=$intercept;

    $convert = $_POST['CncPhonemes'];

    $answer = floor($line['slope'] * intval($convert) + $line['intercept']);
    $answerreverse = ceil($linereverse['slope'] * intval($convert) - $linereverse['intercept']);
    print("<br><hr>");
    print("<br/>BKB from Phonemes:<br/>");
    $low1;
    $high1;
    if ($answer <= $answerreverse) {
        print($answer . "-" . $answerreverse);
        $low1=$answer;
        $high1=$answerreverse;
    } else {
        print($answerreverse . "-" . $answer);
        $low1=$ananswerreverseswer;
        $high1=$answer;
    }
    $convert = $_POST['CncWords'];
    //print_r("<br>B=".$line2['slope']."C + ".$line2['intercept']."<br>");
    //print_r("B=".$line2reverse['slope']."C - ".$line2reverse['intercept']."<br>");
    $answer2 = floor($line2['slope'] * intval($convert) + $line2['intercept']);
    $answer2reverse = ceil($line2reverse['slope'] * intval($convert) - $line2reverse['intercept']);
    print("<br><hr>");
    print("<br/>BKB from Words Correct:<br/>");

    $low2;
    $high2;
    if ($answer2 <= $answer2reverse) {
        print($answer2 . "-" . $answer2reverse);
        $low2=$answer2;
        $high2=$answer2reverse;
    } else {
        print($answer2reverse . "-" . $answer2);
        $low2=$answer2reverse;
        $high2=$answer2;
    }
    print("<br><hr>");
    print("<br/>BKB Score Range:<br/>");

    if($low1<=$low2)
    {
        print($low1." - ");
    }
    else{
        print($low2." - ");
    }

    if($high1>=$high2)
    {
        print($high1);
    }
    else{
        print($high2);
    }

    print("<br><hr><br>");
}
print("<br>");
print("<button onclick=\"location.href='http://cs2.mwsu.edu/~sbeaver/SWEProject/test.html';\">Return</button>");
print("</div>");
print("</div>");

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
<br>
    </body>
</html>
