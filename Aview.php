<!doctype html>
<html lang="en">
<head>
<!-- Required meta tags -->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
<title>Retail</title>
<style>
#container {
  width: 100%;
  min-height: 100vh;
  overflow: visible;
  background: linear-gradient(180deg, #f9c2e2 0%, #d8b3ff 100%);
}
#header{
  width:100%;
  height:70px;
  background:#6a1b9a;
  display:flex;
  justify-content:center;
  align-items:center;
}

#header h1{
  color:white;
  margin:0;
  font-size:36px;
  font-weight:bold;
  font-family:Arial, sans-serif;
}
#menu{
  background:#ff69b4;
  min-height:60px;
  width:100%;
}
#menu ul{
margin:0;
padding:0;
list-style:none;
}
#menu ul li{
float:left;
position:relative;
width:25%;
}
#menu ul li a{
color:blue;
display:block;
padding:15px 95px;
text-decoration:none;
}
#menu ul li a:hover{
background:purple;
color:white;
}
#menu ul li ul{
position:absolute;
z-index: 99999;
left:9999px;
background:hotpink;
}
#menu ul li:hover ul{
left:0;
display:block;
}
#menu ul li ul li{
float:none;
width:250px;
}
#menu ul li ul li a{
background:hotpink;
color:blue;
padding:15px 85px;
}
#content {
  background-image: linear-gradient(135deg, #ffd5ea 0%, #d4c4ff 100%);
  padding: 24px;
  min-height: calc(100vh - 160px);
  overflow: auto;
}
.table-wrapper {
  max-height: calc(100vh - 300px);
  overflow: auto;
  background: rgba(255, 255, 255, 0.96);
  border: 1px solid rgba(255, 255, 255, 0.9);
  border-radius: 18px;
  padding: 18px;
  box-shadow: 0 10px 30px rgba(106, 27, 154, 0.18);
}
.table-wrapper table {
  background: white;
}
.table-wrapper thead th {
  background: #6a1b9a;
  color: white;
  border-color: #d7a0ff;
}
.table-wrapper tbody tr:nth-child(odd) {
  background: rgba(255, 223, 245, 0.6);
}
.table-wrapper tbody tr:nth-child(even) {
  background: rgba(230, 220, 255, 0.8);
}
#content pre {
  color: darkblue;
  padding-top: 10px;
}
a:active{
color:brown;
text-decoration:none;
}
a:hover{
text-decoration:none;
color:brown;
}
#footer {
background-color: purple;
height: 45px;
margin-top: 0px;
}
#footer p {
padding-top: 15px;
color: white;
}
</style></head>
<body>
<div id="container">
<div id="header">
<h1 align="center"> Ecommerce</h1>
</div>
<div id="menu">
<ul>
<li><a href="Home.html"><h5>Home</h5></a></li>
<li><a href="Aadd.php"><h5>Product</h5></a></li>
<li><a href="Aview.php"><h5>View Product</h5></a></li>
<li><a href="Contact.html"><h5>Contact</h5></a></li>
</ul>
</div>
<div id="content">
<h1 align="center">View Products</h1>
<div class="table-wrapper">
<?php
include("Apview.php");
?>
</div>
</div>
<div id="footer">
<p align="center">&#169;Copyrights Reserved</a></p>
</div>
<!-- Optional JavaScript; choose one of the two! -->
<!-- Option 1: jQuery and Bootstrap Bundle (includes Popper) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
<!-- Option 2: jQuery, Popper.js, and Bootstrap JS
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
-->
</div></body></html>