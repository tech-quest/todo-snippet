<?php
session_start();
$_SESSION['error'] = '';

$userId = 1;
$task = filter_input(INPUT_POST, 'task');
$categoryId = filter_input(INPUT_POST, 'categoryId');
$deadline = filter_input(INPUT_POST, 'deadline');

if (empty($_POST['task']) && !empty($_POST['deadline'])) {
    $_SESSION['error'] = 'タスク内容または日付を入力してください';
    header('Location: ./create.php');
    exit();
}

require_once __DIR__ . '/../utils/pdo.php';
$sql =
    'INSERT INTO tasks(user_id, contents, category_id, deadline) VALUES (:userId, :contents, :categoryId, :deadline)';

$statement = $pdo->prepare($sql);
$statement->bindValue(':userId', $userId, PDO::PARAM_INT);
$statement->bindValue(':contents', $task, PDO::PARAM_STR);
$statement->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
$statement->bindValue(':deadline', $deadline, PDO::PARAM_STR);
$statement->execute();
header('Location: ../index.php');
exit();
