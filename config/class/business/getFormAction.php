<?php

class getFormAction
{
	public $pdo;

	/**
	 * コネクション確保
	 */
	function __construct()
	{
		try {
			$db = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD);

			//SQL作成
			$sql = $db->exec('create table `user_physical_dates` (
  `data_id` INT unsigned not null auto_increment comment \'データID\'
  , `user_id` INT unsigned not null comment \'ユーザID\'
  , `body_weight` DOUBLE(5,2) unsigned not null comment \'体重\'
  , `body_fat_percentage` DOUBLE(5,2) unsigned not null comment \'体脂肪\'
  , `muscle_mass` DOUBLE(5,2) unsigned not null comment \'筋肉量\'
  , `body_water_content` DOUBLE(5,2) unsigned not null comment \'体水分量\'
  , `visceral_fat` DOUBLE(5,2) unsigned not null comment \'内臓脂肪\'
  , `basal_metabolic_rate` DOUBLE(5,2) unsigned not null comment \'基礎代謝量\'
  , `bmi` DOUBLE(5,2) unsigned not null comment \'BMI\'
  , `pub_date` DATETIME default CURRENT_TIMESTAMP not null comment \'投稿日時\'
  , `updated_at` DATETIME default CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP not null comment \'更新日時\'
  , `delete_flag` TINYINT unsigned default 0 not null comment \'削除フラグ:0:削除していない
1:削除済み\'
  , `monitoring_status` TINYINT unsigned default 0 not null comment \'監視ステータス:0:未確認
1:OK
2:条件付OK
3:NG\'
  , `checked_admin_user_id` SMALLINT unsigned default NULL comment \'最終確認管理者ID\'
  , `checked_at` DATETIME default NULL comment \'最終確認日時\'
  , constraint `user_physical_dates_PKC` primary key (`data_id`)
) comment \'ユーザ健康データ\' ENGINE=InnoDB CHARACTER SET utf8mb4');

		} catch (PDOException $e) {
			echo 'DB接続エラー'.$e->getMessage();
			die();
		}
	}

	/**
	 * 記事データをDBに保存
	 */
	function saveDbPostData($data)
	{
		// データの保存
		$smt = $this->pdo->prepare(
			'insert into post (name,email,body,created_at,updated_at) values(:name,:email,:body,now(),now())'
		);
		$smt->bindParam(':name', $data['name'], PDO::PARAM_STR);
		$smt->bindParam(':email', $data['email'], PDO::PARAM_STR);
		$smt->bindParam(':body', $data['body'], PDO::PARAM_STR);
		$smt->execute();
	}

	/**
	 * 記事データをDBから読み込み
	 */
	function getDbPostData(){
		// 登録データ取得
		$smt = $this->pdo->prepare('select * from post order by created_at DESC limit 100');
		$smt->execute();
		// 実行結果を配列に返す。
		$result = $smt->fetchAll(PDO::FETCH_ASSOC);

		return $result;
	}
}
