<?php 
require './vendor/autoload.php';


$server='cs2.mwsu.edu';
$username='sbeaver';
$password='sbeaver2019!!!';
$db='sbeaver';
// Create connection server, username, password, database
$con=mysqli_connect($server,$username,$password,$db);

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


$result = $con->query ("SELECT DISTINCT azbioResults.PatientID, azbioResults.Score as azbioScore, azbioResults.TestDate, azbioResults.ConditionsID, cncResults.TestDate, cncResults.ConditionsID, cncResults.`Phonemes Correct`,cncResults.`Words with 3 Phonemes Correct` from azbioResults join cncResults on azbioResults.PatientID=cncResults.PatientID and azbioResults.ConditionsID =cncResults.ConditionsID and azbioResults.TestDate = cncResults.TestDate and azbioResults.Score!=0 and `cncResults`.`Words with 3 Phonemes Correct`!=0 and `cncResults`.`Phonemes Correct`!=0 where azbioResults.TestDate!=0000 and `cncResults`.`Words with 3 Phonemes Correct` is NOT null and azbioResults.Score=56");
$count=0;
$total=0;
while($row=$result->fetch_assoc())
{
    $count++;
    $total+=intval($row['Phonemes Correct']);
}
print_r($total/$count);