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
  //  $result = $con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID, avg(azbioResults.Score) as azbioScore, 
   // avg(bkbResults.`SNR-50`) as SNR50 from azbioResults join bkbResults on azbioResults.PatientID=bkbResults.PatientID and azbioResults.ConditionsID =bkbResults.ConditionsID 
    //and azbioResults.TestDate = bkbResults.TestDate and azbioResults.Score!=0 and `bkbResults`.`SNR-50`!=0 where azbioResults.TestDate!=0000
    //and `bkbResults`.`SNR-50` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");

    $result = $con->query("SELECT DISTINCT azbioResults.PatientID,azbioResults.TestDate, azbioResults.ConditionsID, round(avg(azbioResults.Score)) as azbioScore, 
    round(avg(bkbResults.`SNR-50`),1) as SNR50 from azbioResults join bkbResults on azbioResults.PatientID=bkbResults.PatientID and azbioResults.ConditionsID =bkbResults.ConditionsID 
    and azbioResults.TestDate = bkbResults.TestDate and azbioResults.Score!=0 and bkbResults.`SNR-50`> -23 where azbioResults.TestDate!=0000 and 
    bkbResults.`SNR-50` is NOT null group by azbioResults.PatientID, azbioResults.TestDate, azbioResults.ConditionsID");
$data1 = [];

//while rows, get row and add data to corresponding array
while ($row = $result->fetch_assoc()) {
    $data1[$row['azbioScore']][] = $row['SNR50'];
}

//$answer=linear_regression($x,$y);

//injecting divs for styling
print("<div class=\"mForm\">");
print("<div class=\"formContent\">");
foreach ($data1 as $key => $value) {
    // print_r($value);
    $avg = round(array_sum($value) / count($value));
    $data1[$key] = $avg;
}

if (isset($_POST['Azbio'])) {
  
    $x = array_keys($data1);
    $y = array_values($data1);

    $line = linear_regression($x, $y);
    $linereverse = linear_regression($y, $x);

    $slope=1/$linereverse['slope'];
    $intercept=$linereverse['intercept']/$linereverse['slope'];
    $linereverse['slope']=$slope;
    $linereverse['intercept']=$intercept;
    
    //print_r("B = " . $line['slope'] . "A + " . $line['intercept'] . "\n");
    
    $convert = $_POST['Azbio'];
    $answer = floor($line['slope'] * intval($convert) + $line['intercept']);
    $answerreverse = ceil($linereverse['slope'] * intval($convert) - $linereverse['intercept']);
    print("<br><hr>");
    print("<br/>BKB Score Range:<br/>");
    if ($answer <= $answerreverse) {
        print($answer . "-" . $answerreverse);
    } else {
        print($answerreverse . "-" . $answer);
    }

    print("<br><hr><br>");

} else if (isset($_POST['BKB'])) {
    $x = array_keys($data1);
    $y = array_values($data1);

    $line = linear_regression($y, $x);
    $linereverse = linear_regression($x, $y);
   

    $slope=1/$linereverse['slope'];
    $intercept=$linereverse['intercept']/$linereverse['slope'];
    $linereverse['slope']=$slope;
    $linereverse['intercept']=$intercept;
   
    $convert = $_POST['BKB'];

   // print_r("A = " . $line['slope'] . "B + " . $line['intercept'] . "\n");
   
   $answer = floor($line['slope'] * intval($convert) + $line['intercept']);
    $answerreverse = ceil($linereverse['slope'] * intval($convert) - $linereverse['intercept']);
    print("<br><hr>");
    print("<br/>Azbio Score Range:<br/>");

    if ($answer <= $answerreverse) {
        print($answer . "-" . $answerreverse);
    } else {
        print($answerreverse . "-" . $answer);
    }
    
    print("<br><hr><br>");
}
print("<br>");
print("<button onclick=\"location.href='http://cs2.mwsu.edu/~sbeaver/SWEProject/test.html';\">Return</button>");
print("</div>");
print("</div>");



    //while rows, get row and add data to corresponding array
 /*   while ($row = $result->fetch_assoc()) {
        $x[] = $row['azbioScore'];
        $y[] = $row['SNR50'];
        $Result[] = $row;
    }

    //$answer=linear_regression($x,$y);
    $line = linear_regression($x, $y);
    $line2 = linear_regression($y, $x);

    if ($convert = $_POST['Azbio']) {

        print("AzBio to BKB linear Regression: ");
        print_r("B = " . $line['slope'] . "A + " . $line['intercept'] . "\n");

        $answer = $line['slope'] * doubleval($convert) + $line['intercept'];
        print("<br><hr>");
        print("<br/>BKB Score: ");
        print_r($answer);
        print("<br><hr><br>");
    }
    else if ($convert = $_POST['BKB']) {

        print("BKB to AzBio linear Regression: ");
        print_r("A = " . $line2['slope'] . "B + " . $line2['intercept'] . "\n");

        $answer2 = $line2['slope'] * doubleval($convert) + $line2['intercept'];
        print("<br><hr>");
        print("<br/>AzBio Score: ");
        print_r($answer2);
        print("<br><hr><br>");
    }
*/


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
