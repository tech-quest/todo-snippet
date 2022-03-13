<?php
$dbUserName = 'root';
$dbPassword = 'password';
$pdo = new PDO(
    'mysql:host=mysql; dbname=todolist; charset=utf8',
    $dbUserName,
    $dbPassword
);

$pullDownMenuSql = <<<EOF
  SELECT 
    id
    , name
  FROM 
    categories 
  ;
EOF;
$statement = $pdo->prepare($pullDownMenuSql);
$statement->execute();
$categories = $statement->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['order'])) {
    $direction = $_GET['order'];
} else {
    $direction = 'desc';
}

if (isset($_GET['search'])) {
    $contents = '%' . $_GET['search'] . '%';
} else {
    $contents = '%%';
}

if (isset($_GET['status']) && $_GET['status'] == 'done') {
    $status = 'tasks.status = 1';
} elseif (isset($_GET['status']) && $_GET['status'] == 'notdone') {
    $status = 'tasks.status = 0';
} else {
    $status = 'tasks.status in (0,1) ';
}

if ($_GET['category_id']) {
    $category_id = 'AND tasks.category_id = ' . $_GET['category_id'];
} else {
    $category_id = '';
}

$displaySql = <<<EOF
  SELECT 
  tasks.contents
  , tasks.deadline
  , categories.name AS category_name
  , CASE 
    tasks.status 
      WHEN 0 THEN "未完了" 
      WHEN 1 THEN "完了" 
      END AS status 
  FROM 
    tasks 
  JOIN categories 
    ON tasks.category_id = categories.id
  WHERE 
    tasks.contents LIKE :contents
  AND
    $status
    $category_id
  ORDER BY 
    tasks.id $direction
  ;
EOF;

$statement = $pdo->prepare($displaySql);
$statement->bindValue(':contents', $contents, PDO::PARAM_STR);
$statement->execute();
$tasks = $statement->fetchAll(PDO::FETCH_ASSOC);
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

<body>
  <div class="container mt-5 px-5 mx-auto flex mb-8">
    <div class="bg-white rounded-lg px-8 py-4 flex flex-col w-full mx-40 mt-10 md:mt-0 relative z-10 shadow-md">
      <h2 class="text-gray-900 text-2xl mb-2 font-medium title-font">絞り込み検索</h2>
      <form action="index.php" method="get">
        <div class="flex flex-wrap mb-2">
          <div class="w-1/4">
            <div class="relative">
              <input name="search" type="text" value="<?php echo $_GET[
                  'search'
              ] ??
                  ''; ?>" placeholder="キーワードを入力" class=" bg-white rounded border border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 text-base outline-none text-gray-700 py-1 px-3 leading-8 transition-colors duration-200 ease-in-out" />
            </div>
          </div>

          <div class="w-1/6 pl-8">
            <div class="relative">
              <div>
                <input type="radio" name="order" value="desc">
                <span>新着順</span>
              </div>
              <div>
                <input type="radio" name="order" value="asc">
                <span>古い順</span>
              </div>
            </div>
          </div>

          <div class="w-1/6">
            <div class="relative">
              <select name="category_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                <option value="">カテゴリ</option>
                <?php foreach ($categories as $category): ?>
                  <option value="<?php echo $category[
                      'id'
                  ]; ?>"><?php echo $category['name']; ?></option>
                <?php endforeach; ?> 
              </select>
            </div>
          </div>

          <div class="w-1/6 pl-6">
            <div class="relative">
              <div>
                <input type="radio" name="status" value="done">
                <span>完了</span>
              </div>
              <div>
                <input type="radio" name="status" value="notdone">
                <span>未完了</span>
              </div>
            </div>
          </div>
        </div>
        <button class="text-white bg-indigo-500 border-0 py-1 px-6 focus:outline-none hover:bg-indigo-600 rounded text-lg">Button</button>
      </form>
    </div>
  </div>

  <div class="container px-5 mx-auto">
      <table class="table-auto w-full">
        <thead>
          <tr>
            <th class="px-4 py-2">タスク名</th>
            <th class="px-4 py-2">締め切り</th>
            <th class="px-4 py-2">カテゴリー名</th>
            <th class="px-4 py-2">完了未完了</th>
            <th class="px-4 py-2">編集</th>
            <th class="px-4 py-2">削除</th>
          </tr>
        </thead>

        <?php foreach ($tasks as $task): ?>
          <tbody>
            <tr>
              <td class="border px-4 py-2"><?php echo $task['contents']; ?></td>
              <td class="border px-4 py-2"><?php echo $task['deadline']; ?></td>
              <td class="border px-4 py-2"><?php echo $task[
                  'category_name'
              ]; ?></td>
              <td class="border px-4 py-2"><?php echo $task['status']; ?></td>
              <td class="border px-4 py-2"><button class="text-white bg-green-300 border-0 py-1 px-1 focus:outline-none hover:bg-green-400 rounded text-lg">編集</button></td>
              <td class="border px-4 py-2"><button class="text-white bg-red-300 border-0 py-1 px-1 focus:outline-none hover:bg-red-400 rounded text-lg">削除</button></td>
            </tr>
          </tbody>
        <?php endforeach; ?>
      </table>
  </div>
</body>

</html>