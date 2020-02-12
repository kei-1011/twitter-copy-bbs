<?php
session_start();
require('dbconnect.php');


//自分のメッセージであるかを判断する
if (isset($_SESSION['id'])) {
  //削除する候補のメッセージを取得

  $id = $_REQUEST['id'];

  $messages = $db->prepare('SELECT * FROM posts WHERE id=?');

  $messages->execute(array($id));
  $message = $messages->fetch();

  //DBのIDとセッションで記憶しているIDが一致している場合、削除できる
  if ($message['member_id'] == $_SESSION['id']) {
    $del = $db->prepare('DELETE FROM posts WHERE id=?');
    $del->execute(array($id));
  }
}

header('Location:index.php');
exit();