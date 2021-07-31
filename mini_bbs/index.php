<?php


session_start();
require('dbconnect.php');

if (isset ($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
  $_SESSION['time'] = time(); //最後の行動から1時間経過したらログアウト

  $members = $db->prepare('SELECT * FROM members WHERE id=?');
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();

} else {
  header('Location: login.php');
  exit();
}

if (!empty($_POST)) {
  if ($_POST['message'] !== '') {
    $message = $db->prepare('INSERT INTO posts SET member_id=?, 
    message=?, reply_message_id=?, created=NOW()');
    $message->execute(array(
    $member['id'], //SESSIONよりDBのIDの方が確実
    $_POST['message'],
    $_POST['reply_post_id'],
    ));

    header('Location: index.php'); 
    //同じ画面を初期状態で呼び出す(これがないとリロードの度に投稿される)
    exit();
  }
}

$page = $_REQUEST['page'];
if ($page == '') {
  $page = 1;
}
$page = max($page, 1); //１以下にはならないように

$counts = $db->query('SELECT count(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage); //メッセージのあるページ分だけ表示

$start = ($page - 1) * 5;

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM 
members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5'); //５件ずつ
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();


//入力した値を使うわけではないので、query methodでSQL呼び出す
//m, pは、テーブルの略称。
//membersテーブルのname, picture, postsテーブルの全ての値
//WHERE~~: 両テーブルのidを一致

if (isset($_REQUEST['res'])) {
  //返信処理
  $response = $db->prepare('SELECT m.name, m.picture,
  p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');

  $response->execute(array($_REQUEST['res']));

  $table = $response->fetch();
  $message = '@' . $table['name'] . ' ' . $table['message'];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">
  	<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
    <form action="" method="post">
      <dl>
        <dt><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?>さん、メッセージをどうぞ</dt>
        <dd>
          <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
        </dd>
      </dl>
      <div>
        <p>
          <input type="submit" value="投稿する" />
        </p>
      </div>
    </form>

    <?php foreach ($posts as $post): ?>

    <div class="msg">
    <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES))?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES))?>" />
    <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES))?>
    <span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES))?>）
    </span>[<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]</p>
    <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'])); ?>">
    <?php print(htmlspecialchars($post['created'], ENT_QUOTES))?></a>

    <?php if ($post['reply_message_id'] > 0): ?>
    <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES))?>">
    返信元のメッセージ</a>
    <?php endif; ?>

    <?php if($_SESSION['id'] == $post['member_id']): ?>

[<a href="delete.php?id=<?php print(htmlspecialchars($post['id'])); ?>"
style="color: #F33;">削除</a>]
<?php endif;?>

    </p>
    </div>

    <?php endforeach; ?>

<ul class="paging">
<?php if($page > 1): ?>
<li><a href="index.php?page=<?php print($page-1); ?>">前のページへ</a></li>
<?php else: ?>
<li><a href="index.php?page=<?php print($page+1); ?>">次のページへ</a></li>
<?php endif; ?>

</ul>
  </div>
</div>
</body>
</html>
