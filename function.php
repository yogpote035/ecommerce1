<?php
function check_login($con)
{
    if(isset($_SESSION['rname']))
    {
        $rname=$_SESSION['rname'];
        $query="select * from retailler where retailler name='$rname' limit 1"; 
        $result=mtsqli_query($con,$query);
        if($result && mysqli_num_rows($result)>0)
        {
           $user_data=mysqli_fetch_assoc($result);
           return $user_data;
        }
    }
    header("Location:Rmain.php");
    die;
}
?>