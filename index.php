<?php

require_once("./config/dbProperties.php");
require_once('./class/business/getFormAction.php');

$action = new getFormAction();

$user_id = null;
// イベントID取得
if (isset($_POST['$user_id'])) {
	$user_id = $_POST['$user_id'];
}

switch ($user_id) {

	// DBsave
	case 'save':
		$action->setPhysicalData($_POST);
		require("./view/post.php");
		break;

	// login
	case 'login':
		// パスワードが一致するかチェック
		$errm = $action->checkloginMode($_POST["user_name"], $_POST["password"]);

		if (empty($errm)) {
			require("./view/admin_list.php");
		} else {
			require("./view/post.php");
		}

		break;

	// admin list
	case 'admin':
		require("./view/admin_list.php");
		break;

	// データ更新画面
	case 'edit':
		$id = $_POST['id'];
		require("./view/admin_detail.php");
		break;

	// データ更新
	case 'edit_save':
		$action->updatePhysicalData($_POST);
		require("./view/admin_list.php");
		break;

	// ここを追記-----------------------------------------
	// データ削除
	case 'delete':
		$data_id = $_POST['id'];
		$action->deletePhysicalData($data_id);
		require("./view/admin_list.php");

		break;



	// 初回アクセス時、投稿画面表示
	default:
		require("./view/post.php");
		break;
}

