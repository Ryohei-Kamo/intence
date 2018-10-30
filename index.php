<?php

require_once("./config/dbProperties.php");
require_once('./class/business/getFormAction.php');

$action = new getFormAction();

$user_id = null;
//ユーザID取得
if (isset($_POST['$user_id'])) {
	$user_id = $_POST['$user_id'];
}

switch ($user_id) {

	// DBsave
	case 'save':
		$action->setPhysicalData($_POST);
		require("./view/data_list.php");
		break;

	// data list
	case 'data_list':
		require("./view/data_list.php");
		break;

	// データ更新画面
	case 'edit':
		$data_id = $_POST['$data_id'];
		require("./view/data_detail.php");
		break;

	// データ更新
	case 'edit_save':
		$action->updatePhysicalData($_POST, $data_id);
		require("./view/data_list.php");
		break;

	// データ削除
	case 'delete':
		$data_id = $_POST['$data_id'];
		$action->deletePhysicalData($data_id);
		require("./view/data_list.php");
		break;

	// アクセス時、投稿画面表示
	default:
		require("./view/post.php");
		break;
}

