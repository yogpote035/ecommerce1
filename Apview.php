<?php
$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "ecommerce";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM apadd";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  echo "<div class='table-responsive'><table class='table table-bordered table-hover' style='min-width: 100%;'>";
  echo "<thead class='thead-dark'><tr><th>Apid</th><th>apname</th><th>apbrand</th><th>apqty</th><th>apprice</th></tr></thead><tbody>";
  while($row = $result->fetch_assoc()) {
    echo "<tr><td>".$row["Apid"]."</td><td>".$row["apname"]."</td><td>".$row["apbrand"]."</td><td>".$row["apqty"]."</td><td>".$row["apprice"]."</td></tr>";
  }
  echo "</tbody></table></div>";
} else {
  echo "<p>No products found.</p>";
}
$conn->close();
?>