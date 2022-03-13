<?php
$dbUserName = 'root';
$dbPassword = 'password';
$pdo = new PDO(
    'mysql:host=mysql; dbname=todolist; charset=utf8',
    $dbUserName,
    $dbPassword
);
