<?php

$servername = "localhost";
$username = "root";
$password = "Yogeshpo7@";
$dbname = "ecommerce";

$conn = mysqli_connect($servername,$username,$password,$dbname);

if(!$conn)
{
    die("Connection Failed");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: validation.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{

    $Apid = isset($_POST["Apid"]) ? (int) $_POST["Apid"] : 0;
    $apname = mysqli_real_escape_string($conn, trim($_POST["apname"] ?? ''));
    $apbrand = mysqli_real_escape_string($conn, trim($_POST["apbrand"] ?? ''));
    $apcategory = mysqli_real_escape_string($conn, trim($_POST["apcategory"] ?? ''));
    $apqty = isset($_POST["apqty"]) ? (int) $_POST["apqty"] : 0;
    $apprice = isset($_POST["apprice"]) ? (float) $_POST["apprice"] : 0.0;

    // Image Upload

    $folder = '';
    if (!empty($_FILES['apimage']['tmp_name']) && is_uploaded_file($_FILES['apimage']['tmp_name'])) {
        $imageName = basename($_FILES['apimage']['name']);
        $folder = 'uploads/' . $imageName;
        move_uploaded_file($_FILES['apimage']['tmp_name'], $folder);
        $folder = mysqli_real_escape_string($conn, $folder);
    }

    $sql = "INSERT INTO apadd (Apid, apname, apbrand, apcategory, apqty, apprice, Apimage) VALUES ($Apid, '$apname', '$apbrand', '$apcategory', $apqty, $apprice, '$folder')";

    if(mysqli_query($conn,$sql))
    {
        echo "<script>
        alert('Product Added Successfully');
        window.location='validation.php';
        </script>";
    }
    else
    {
        echo mysqli_error($conn);
    }

}

mysqli_close($conn);

?>



