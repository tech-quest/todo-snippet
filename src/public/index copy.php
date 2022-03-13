<?php
session_start();
if (!isset($_SESSION['userId'])) {
    header('Location: ./user/signin.php');
    exit();
}

require_once __DIR__ . '/utils/pdo.php';

if (filter_input(INPUT_GET, 'done', FILTER_VALIDATE_BOOL)) {
    $jobStatus = '未完了';
    $title = '完了タスク一覧';
    $setDone = true;
} else {
    $jobStatus = '完了';
    $title = '未完了タスク一覧';
    $setDone = false;
}

$searchSql = '';
$search = false;

if ($_SERVER['REQUEST_URI'] == '/todoList/index.php') {
    $searchKeyword = '';
}

$searchKeyword = filter_input(
    INPUT_GET,
    'search',
    FILTER_SANITIZE_SPECIAL_CHARS
);
if ($searchKeyword != '') {
    $search = true;
}

$strSetDone = $setDone ? 'true' : 'false';

$categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$categoryFilterSql = '';
$categoryFilter = false;

if (!empty($categoryId)) {
    $categoryFilter = true;
    $categoryFilterSql = '&& categories.id=:categoryId';
}

$sortSql = '';
$sort = false;
$sortMode = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_SPECIAL_CHARS);
$sortSql = 'order by tasks.category_id asc, tasks.deadline asc';
if ($sortMode == 'desc') {
    $sortSql = ' order by tasks.category_id asc, tasks.deadline desc';
}

$ascLink = "./index.php?done=$setDone&sort=asc&search=$searchKeyword&category_id=$categoryId";
$descLink = "./index.php?done=$setDone&sort=desc&search=$searchKeyword&category_id=$categoryId";

$statusId = 0;
if ($setDone && filter_input(INPUT_GET, 'done', FILTER_VALIDATE_BOOL)) {
    $statusId = 1;
}

$queries = [];
$queries[] =
    'SELECT * FROM categories inner join tasks on categories.id = tasks.category_id where tasks.status=:statusId && tasks.user_id=:userId';

if ($search) {
    $searchSql = '&& contents LIKE :searchKeyword';

    $escapedKeyword = '';
    if (preg_match('/%/', $searchKeyword)) {
        $escapedKeyword = str_replace('%', '\%', $searchKeyword);
    } elseif (preg_match('/_/', $searchKeyword)) {
        $escapedKeyword = str_replace('_', '\_', $searchKeyword);
    }

    $searchBind =
        $escapedKeyword != ''
            ? '%' . $escapedKeyword . '%'
            : '%' . $searchKeyword . '%';

    $queries[] = $searchSql;
} elseif ($categoryFilter) {
    $queries[] = $categoryFilterSql;
}

$queries[] = $sortSql;
$sql = implode(' ', $queries);
$statement = $pdo->prepare($sql);
$statement->bindValue(':statusId', $statusId, PDO::PARAM_INT);
$statement->bindValue(':userId', $_SESSION['userId'], PDO::PARAM_INT);
if ($search) {
    $statement->bindValue(':searchKeyword', $searchBind, PDO::PARAM_STR);
}
if ($categoryFilter) {
    $statement->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);
}
$statement->execute();
$tasksTable = $statement->fetchAll(PDO::FETCH_ASSOC);

$routingNotDone = './index.php?done=false';
$routingDone = './index.php?done=true';

if (isset($_GET['search'])) {
    $routingNotDone = "./index.php?done=false&search=$searchKeyword";
    $routingDone = "./index.php?done=true&search=$searchKeyword";
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>todo view</title>
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-300">
    <div class="mx-auto md:w-7/12 w-11/12 bg-white mb-6">

        <?php require_once './utils/header.php'; ?>

        <div class="w-9/12 mx-auto pb-3 h-5/6">

            <div class="flex block mb-3">
                <div class="mr-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">
                    <a href="<?php echo $routingNotDone; ?>"><span class=text-white>未完了</span></a>
                </div>
                <div class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">
                    <a href="<?php echo $routingDone; ?>"><span class=text-white>完了</span></a><br />
                </div>
            </div>

            <div class="mb-10">
                <form action="./index.php">
                    <input class="border-2 border-gray-300" type="search" name="search" placeholder="キーワードを入力" value="<?php echo $searchKeyword; ?>">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded" type="submit">検索</button>
                </form>
            </div>

            <div class="mb-5">
                <a href="<?php echo $ascLink; ?>" class="text-blue-600 inline-block w-20">締切昇順</a>
                <a href="<?php echo $descLink; ?>" class="text-blue-600 inline-block w-20">締切降順</a>
            </div>

            <h2 class='mb-6 text-3xl'><?php echo $title; ?></h2>

            <?php if (
                !empty($_GET['search']) ||
                !empty($_GET['category_id'])
            ): ?>
                <div class="mb-6">
                    <a class='text-blue-600' href=./index.php>全件表示に戻す</a><br />
                </div>
            <?php endif; ?>

            <div class='mb-5 border-b-2'>
                <span class="inline-block w-36">タスク名</span>
                <span class="inline-block w-32">締め切り</span>
                <span class="inline-block w-28">カテゴリー</span>
            </div>

            <div>
                <?php if (count($tasksTable) == 0): ?>
                    <p>現在タスクはありません</p>
                <?php endif; ?>
                <?php foreach ($tasksTable as $tasks): ?>
                    <form class="mb-3" method="POST" action="./delete.php">
                        <label class="inline-block w-36"><?php echo $tasks[
                            'contents'
                        ]; ?></label>
                        <label class="inline-block w-32"><?php echo $tasks[
                            'deadline'
                        ]; ?></label>
                        <div class="inline-block w-28 text-blue-500"><a href="./index.php?done=<?php echo $setDone; ?>&category_id=<?php echo $tasks[
    'category_id'
]; ?>"><?php echo $tasks['name']; ?></a></div>
                        <a class="inline-block w-18 bg-blue-500 text-white rounded py-1 px-1 hover:bg-blue-700" href="./updateStatus.php?complete=<?php echo $tasks[
                            'id'
                        ]; ?>&status=<?php echo $tasks[
    'status'
]; ?>"><?php echo $jobStatus; ?></a>
                        <a class="inline-block w-18 bg-blue-500 text-white rounded py-1 px-1 hover:bg-blue-700" href="./edit.php?task_id=<?php echo $tasks[
                            'id'
                        ]; ?>&category_name=<?php echo $tasks[
    'name'
]; ?>&content=<?php echo $tasks['contents']; ?>&deadline=<?php echo $tasks[
    'deadline'
]; ?>">編集</a>
                        <button class="inline-block w-18 bg-red-500 text-white px-1 py-1 rounded hover:bg-red-700" type="submit">削除</button>
                        <input type="hidden" name="taskId" value=<?php echo $tasks[
                            'id'
                        ]; ?>>
                    </form>
                <?php endforeach; ?>
            </div>


        </div>
    </div>
</body>

</html>