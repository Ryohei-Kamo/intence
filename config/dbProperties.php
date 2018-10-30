<?php
//接続データベース情報(本番)
//記述例なので環境に応じて変更をしてください。
define('DATABASE_NAME','physical_db');
define('DATABASE_USER','root');
define('DATABASE_PASSWORD','Cde3Vfr4');
define('DATABASE_HOST','localhost');

define('PDO_DSN','mysql:dbname=' . DATABASE_NAME .';host=' . DATABASE_HOST . '; charset=utf8');
