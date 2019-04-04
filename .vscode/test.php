<?php 
//all variable start with $
// Create connection server, username, password, database
$con=mysqli_connect('cs2.mwsu.edu', 'sbeaver', 'sbeaver', 'sbeaver2019!!!', 'sbeaver');

// Check connection
if (mysqli_connect_errno()) {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
//in querey go the sql 
//$sql="SELECT * FROM `azbioResults` where 'score'>'$score' ";

$result = $con->query("SELECT DISTINCT azbioResults.PatientID, azbioResults.Score as azbioScore, azbioResults.TestDate, cncResults.TestDate, cncResults.`Phonemes Correct`,cncResults.`Words with 3 Phonemes Correct` from azbioResults join cncResults on azbioResults.PatientID=cncResults.PatientID and azbioResults.TestDate = cncResults.TestDate where azbioResults.TestDate!=0000");
$Result = $result->fetch_assoc();
print_r($Result);
?>