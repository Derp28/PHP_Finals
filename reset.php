<?php

session_start();

unset($_SESSION['answer']);
unset($_SESSION['attempts']);

header("Location:index.php");

?>