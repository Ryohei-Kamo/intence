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
  , `weight` DOUBLE(5,2) unsigned not null comment \'体重\'
  , `fat_percentage` DOUBLE(5,2) unsigned not null comment \'体脂肪\'
  , `muscle_mass` DOUBLE(5,2) unsigned not null comment \'筋肉量\'
  , `water_content` DOUBLE(5,2) unsigned not null comment \'体水分量\'
  , `visceral_fat` DOUBLE(5,2) unsigned not null comment \'内臓脂肪\'
  , `basal_metabolic_rate` DOUBLE(5,2) unsigned not null comment \'基礎代謝量\'
  , `bmi` DOUBLE(5,2) unsigned not null comment \'BMI\'
  , `created_at` DATETIME default CURRENT_TIMESTAMP not null comment \'投稿日時\'
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
	 * データをDBに保存
	 */
	function setPhysicalData($data)
	{
		try {
			// データの保存
			$smt = $this->pdo->prepare(
				'INSERT INTO user_physical_dates (user_id,weight,fat_percentage,muscle_mass,water_content,visceral_fat,basal_metabolic_rate,bmi,created_at,updated_at, delete_flag) VALUES (:user_id,:weight,:fat_percentage,:muscle_mass,:water_content,:visceral_fat,:basal_metabolic_rate,:bmi,now(),now(),0)'
			);
			$smt->bindParam(':user_id', $data['user_id'], PDO::PARAM_STR);
			$smt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
			$smt->bindParam(':fat_percentage', $data['fat_percentage'], PDO::PARAM_STR);
			$smt->bindParam(':muscle_mass', $data['muscle_mass'], PDO::PARAM_STR);
			$smt->bindParam(':water_content', $data['water_content'], PDO::PARAM_STR);
			$smt->bindParam(':visceral_fat', $data['visceral_fat'], PDO::PARAM_STR);
			$smt->bindParam(':basal_metabolic_rate', $data['basal_metabolic_rate'], PDO::PARAM_STR);
			$smt->bindParam(':bmi', $data['bmi'], PDO::PARAM_STR);
			$smt->execute();

		} catch (PDOException $e) {
			echo 'データの入力エラー'.$e->getMessage();
		}
	}

	/**
	 * データリストをDBから読み込み
	 */
	function getPhysicalDataList($user_id)
	{
		try {
			// 登録データ取得
			$smt = $this->pdo->prepare(
				'SELECT * FROM user_physical_dates ORDER BY created_at DESC limit 100 WHERE user_id = :user_id AND delete_flag = 0'
			);
			$smt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
			$smt->execute();
			// 実行結果を配列に返す。
			$result = $smt->fetchAll(PDO::FETCH_ASSOC);

			return $result;

		} catch (PDOException $e) {
			echo 'データの読み来いエラー'.$e->getMessage();
		}
	}

	/**
	 * データをDBから読み込み
	 */
	function getPhysicalData($user_id, $data_id)
	{
		try {
			// 登録データ取得
			$smt = $this->pdo->prepare(
				'SELECT * FROM user_physical_dates WHERE user_id = :user_id  AND data_id = :data_id AND delete_flag = 0'
			);
			$smt->bindParam(':data_id', $data_id, PDO::PARAM_STR);
			$smt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
			$smt->execute();
			// 実行結果を配列に返す。
			$result = $smt->fetchAll(PDO::FETCH_ASSOC);

			return $result;
		} catch (PDOException $e) {
			echo 'データの読み込みエラー'.$e->getMessage();
		}
	}

	/**
	 * データを論理削除する
	 */
	function daletePhysicalData($user_id, $data_id)
	{
		try {
			// 登録データ論理削除
			$smt = $this->pdo->prepare(
				'UPDATE user_physical_dates SET delete_flag = 1 WHERE user_id = :user_id  AND data_id = :data_id'
			);
			$smt->bindParam(':data_id', $data_id, PDO::PARAM_STR);
			$smt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
			$smt->execute();
			// 実行結果を配列に返す。
			$result = $smt->fetchAll(PDO::FETCH_ASSOC);

			return $result;
		} catch (PDOException $e) {
			echo 'データの論理削除エラー'.$e->getMessage();
		}
	}

	/**
	 * データを更新する
	 */
	function updatePhysicalData($data, $data_id)
	{
		try {
			// データの更新
			$smt = $this->pdo->prepare(
				'UPDATE  user_physical_dates SET weight = :weight,fat_percentage = :fat_percentage,muscle_mass = :muscle_mass,water_content = :water_content,visceral_fat = :visceral_fat,basal_metabolic_rate = :basal_metabolic_rate,bmi = :bim,updated_at = now() WHERE data_id = data_id)'
			);
			$smt->bindParam(':data_id', $data_id, PDO::PARAM_STR);
			$smt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
			$smt->bindParam(':fat_percentage', $data['fat_percentage'], PDO::PARAM_STR);
			$smt->bindParam(':muscle_mass', $data['muscle_mass'], PDO::PARAM_STR);
			$smt->bindParam(':water_content', $data['water_content'], PDO::PARAM_STR);
			$smt->bindParam(':visceral_fat', $data['visceral_fat'], PDO::PARAM_STR);
			$smt->bindParam(':basal_metabolic_rate', $data['basal_metabolic_rate'], PDO::PARAM_STR);
			$smt->bindParam(':bmi', $data['bmi'], PDO::PARAM_STR);
			$smt->execute();

		} catch (PDOException $e) {
			echo 'データの更新エラー'.$e->getMessage();
		}

	}
}
