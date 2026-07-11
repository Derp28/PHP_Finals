<?php

$conn = new mysqli("localhost","root","","word_bank");

if($conn->connect_error){
    die("Connection Failed");
}

session_start();

?>