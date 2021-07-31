<?php
session_start();
require('../dbconnect.php');

if (!empty($_POST)){
	if ($_POST['name'] === ''){
		$error['name'] = 'blank';
	} 
	if ($_POST['email'] === ''){
		$error['email'] = 'blank';
	} 
	if (strlen($_POST['password']) < 4 ){
		$error['password'] = 'length';
	} 
	if ($_POST['password'] === ''){
		$error['password'] = 'blank';
	} 
	$fileName = $_FILES['image']['name'];
	if(!empty($fileName)){
		$ext = substr($fileName, -3);
		if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png'){
			$error['image'] = 'type';
		}
	}
	
	// アカウント重複チェック ※なぜ実装できない？！
	if (empty($error)){
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');
		$member->execute(array($_POST['email']));
		$record = $member->fetch(); //いなければ０
		if ($record['cnt'] > 0) {
			$erroe['email'] = 'duplicate';
		}
	}

	if (empty($error)){
		$image = date('YmdHis') . $_FILES['image']['name'];
		// ファイル名の作成
		move_uploaded_file($_FILES['image']['tmp_name'], '../member_picture/' . $image);
		$_SESSION['join'] = $_POST;
		$_SESSION['join']['image'] = $image;
		header('Location: check.php');
		exit();
	}
}
// フォーム送信した時のみエラーチェック
// phpではボタンクリックしたかは判別つかないので、!empty (空ではない)として判別

if ($_REQUEST['action'] == 'rewrite' && isset($_SESSION['join'])){
	$_POST = $_SESSION['join'];
}

?>

<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>
<body>
<div id="wrap">
<div id="head">
<h1>会員登録</h1>
</div>

<div id="content">
<p>次のフォームに必要事項をご記入ください。</p>
<form action="" method="post" enctype="multipart/form-data">
	<dl>
		<dt>ニックネーム<span class="required">必須</span></dt>
		<dd>
        	<input type="text" name="name" size="35" maxlength="255" value="<?php print (htmlspecialchars($_POST['name'], ENT_QUOTES)); ?>" />
			<?php if ($error['name'] === 'blank'): ?>
			<p class="error">Pls enter your nick name</p>
			<?php endif; ?>
		</dd>
		<dt>メールアドレス<span class="required">必須</span></dt>
		<dd>
        	<input type="text" name="email" size="35" maxlength="255" value="<?php print (htmlspecialchars($_POST['email'], ENT_QUOTES)); ?>" />
			<?php if ($error['email'] === 'blank'): ?>
			<p class="error">Pls enter your email</p>
			<?php endif; ?>
			<?php if ($error['email'] === 'duplicate'): ?>
			<p class="error">Your email is already in use</p>
			<?php endif; ?>
		<dt>パスワード<span class="required">必須</span></dt>
		<dd>
        	<input type="password" name="password" size="10" maxlength="20" value="<?php print (htmlspecialchars($_POST['password'], ENT_QUOTES)); ?>" />
			<?php if ($error['password'] === 'length'): ?>
			<p class="error">Pls enter longer password</p>
			<?php endif; ?>
			<?php if ($error['password'] === 'blank'): ?>
			<p class="error">Pls enter your password</p>
			<?php endif; ?>

        </dd>
		<dt>写真など</dt>
		<dd>
        	<input type="file" name="image" size="35" value="test"  />
			<?php if ($error['image'] === 'type'): ?>
			<p class="error">Pls choose correct type of your photo</p>
			<?php endif; ?>
			
        </dd>
	</dl>
	<div><input type="submit" value="入力内容を確認する" /></div>
</form>
</div>
</body>
</html>
