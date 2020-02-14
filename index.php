<?php
session_start();
require('dbconnect.php');

//セッションに記録された時間に1時間を足された時間が、現在の時刻より大きい時
//　→　1時間何もしない状態でいると、自動的にログアウトされる
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {

  $_SESSION['time'] = time();
  //現在の時刻で上書きする

  $members = $db->prepare('SELECT * FROM members WHERE id=?');  //メンバーの情報を取得
  $members->execute(array($_SESSION['id']));  //idを使って会員情報を取得する
  $member = $members->fetch();                //fetchをして取得できた情報を保存（｜ログインしたユーザーの情報を格納している）

} else {
  header('Location:login.php');
  exit();
}

if (!empty($_POST)) {
  if ($_POST['message'] !== '') {
    $message = $db->prepare('INSERT INTO posts SET member_id=?,message=?,reply_message_id=?,created=NOW()');
    $message->execute(array(
      $member['id'],
      $_POST['message'],
      $_POST['reply_post_id']
    ));

    //メッセージが投稿されたあと、自分自身をもう一度よび出す
    //POSTの値をリセットし、情報の重複を防ぐ
    header('Location:index.php');
    exit();
  }
}

$page = $_REQUEST['page'];

if ($page == '') {
  $page = 1;
}

$page = max($page, 1); //1より小さい数字の場合は、１が自動的に指定される

//大きな数字を入力されたときの対処
//最終ページを取得
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');  //postsのずを数える
$cnt = $counts->fetch();    //メッセージの件数が入る
$maxPage = ceil($cnt['cnt'] / 5);   //最終のページ数を5で割る
$page = min($page, $maxPage);    // maxPage以上の数字にならない

$start = ($page - 1) * 5;

$posts = $db->prepare('SELECT m.name,m.picture,p.* FROM members m ,posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');
// LIMIT 0,5　先頭から５件を出力
$posts->bindParam(1, $start, PDO::PARAM_INT);
//executeを使うと文字列として渡ってしまうので、bindparamを使う
$posts->execute();


if (isset($_REQUEST['res'])) {
  //返信の処理（URLパラメータがresだった時）
  $response = $db->prepare('SELECT m.name,m.picture,p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=?');
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
            <textarea name="message" cols="50"
              rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
            <input type="hidden" name="reply_post_id"
              value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)) ?>" />
          </dd>
        </dl>
        <div>
          <p>
            <input type="submit" value="投稿する" />
          </p>
        </div>
      </form>
      <?php foreach ($posts as $post) { ?>
      <div class="msg">
        <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>.jpg" width="48"
          height="48" alt="" />
        <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?>
            <?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?>
            <span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>
            [<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]
          </p>
        <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
            <?php if ($post['reply_message_id'] > 0) : ?>
              <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES)); ?>">
                返信元のメッセージ</a>
            <?php endif; ?>

            <?php if ($_SESSION['id'] == $post['member_id']) : //自分の投稿にだけ削除ボタンを表示させる
            ?>
              [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>" style="color: #F33;">削除</a>]
            <?php endif; ?>
          </p>
      </div>
      <?php } ?>

      <ul class="paging">
        <?php if ($page > 1) : ?>
        <li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
        <?php else : ?>
        <li>前のページへ</li>
        <?php endif; ?>

        <?php if ($page < $maxPage) : ?>
        <li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
        <?php else : ?>
        <li>次のページへ</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</body>

</html>