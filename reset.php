<?php

session_start();

unset($_SESSION['answer']);
unset($_SESSION['attempts']);
unset($_SESSION['hinted_letters']);

$_SESSION['maxAttempts'] = 5;

header("Location:index.php");

?>