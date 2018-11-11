<?php
//接続データベース情報(本番)
//記述例なので環境に応じて変更をしてください。
define('DATABASE_NAME','sport_gym');
define('DATABASE_USER','root');
define('DATABASE_PASSWORD','triumph');
define('DATABASE_HOST','localhost');

define('PDO_DSN','mysql:host=DATABASE_HOST' . DATABASE_NAME .';host=' . DATABASE_HOST . '; charset=utf8');
