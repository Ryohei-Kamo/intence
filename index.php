<?php

require_once("./config/dbProperties.php");
require_once('./class/business/getFormAction.php');

$action = new getFormAction();

$user_id = null;


switch ($user_id = null){
    default:
        require("./view/login.php");
        break;

    //初回ログイン
    case 'login_first':
        $action->getLoginFirst($_POST);
        require("./view/post.php");
        break;

    //ログイン
    case 'login':
        $action->getLogin($_POST);
        require("./view/post.php");
        break;
}

//ユーザID取得
if (isset($_POST['$user_id'])) {
    $user_id = $_POST['$user_id'];
}

switch ($user_id) {

    // DBsave
    case 'save':
        $last_date = $action->getPostLastDate($user_id)['created_at'];

        if (date('Y-m-d', time()) != substr($last_date, 0, 10)) {
            $action->setPhysicalData($_POST);
            require("./view/data_list.php");
        } else {
            $action->updatePhysicalData($_POST, $data_id);
            require("./view/data_list.php");
        }
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
}

