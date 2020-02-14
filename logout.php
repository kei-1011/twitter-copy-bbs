<?php
/*
ログアウト
記録されているセッションの情報を削除する
*/

//セッションスタート
session_start();

//セッションの情報を削除するため、セッションの配列を空で上書きする
$_SESSION = array();

//セッションにクッキーを使うかどうかを指定する
if (ini_set('session.use_cookies')) {

  //クッキーの情報を削除する処理
  $params = session_get_cookie_params();

  //クッキーの有効期限を切ってセッションを削除する
  /*
  session_get_cookie_paramsの関数が返してきた値を
  それぞれ設定して、
  セッションのクッキーを使っているオプションを指定し、削除していく
  */
  setcookie(
    session_name() . '',
    time() - 42000,
    $params['path'],
    $params['domain'],
    $params['secure'],
    $params['httponly']
  );
}

//セッションを完全に削除
session_destroy();

//ログアウトした時に、クッキーに保存されているメールアドレスも削除する
setcookie('email', '', time() - 3600);

//login.phpに戻る
header('Location:login.php');
exit();