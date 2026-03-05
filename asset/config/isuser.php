<?php
session_start();
if($_SESSION['issAuthenticated'] == false){
    header("Location: ../login.html");
}




?>