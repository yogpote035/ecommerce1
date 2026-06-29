<?php
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "retailler";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM rpadd";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  echo "<h4><table style='border:solid 5px black;width:100%;height:80;}'><tr><th>Rpid</th><th>Rpame</th><th>Rpbrand</th><th>Rpqty</th><th>Rpprice</th></tr>";
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr><td>".$row["Rpid"]."</td><td>".$row["Rpname"]."</td><td> ".$row["Rpbrand"]."</td><td>".$row["Rpqty"]."</td><td>".$row["Rpprice"]."</td></tr>";
  }
  echo "</table></h4>";
} else {
  echo "0 results";
}
$conn->close();
?>