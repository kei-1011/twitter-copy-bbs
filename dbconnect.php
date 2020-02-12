<?php
try {
  $db = new PDO('mysql:dbname=twbbs;host=localhost;charset=utf8', 'root', 'root');
} catch (PDOException $e) {
  print('DB接続エラー:' . $e->getMessage());
}