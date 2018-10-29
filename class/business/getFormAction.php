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
			$db = new PDO(PDO_DSN, DATABASE_USER, DATABASE_PASSWORD,array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
		} catch (PDOException $e) {
			print 'データベースにアクセスできませんでした。'.$e->getMessage();
			exit();
		}
		// DBエラー時の例外を設定する
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// フェッチモードを設定する：オブジェクトとしての行
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	}


	/**
	 * ニックネームとパスワードを確認する
	 */
	function checkloginMode($user_name, $password)
	{
		try {
			// 登録データ取得
			$smt = $this->pdo->prepare(
				'SELECT user_id FROM user_users  WHERE user_name = :user_neme AND password = :password'
			);
			$smt->bindParam(':user_id', $user_name, PDO::PARAM_STR);
			$smt->bindParam(':user_id', $password, PDO::PARAM_STR);
			$smt->execute();
			// 実行結果を配列に返す。
			$result = $smt->fetchAll(PDO::FETCH_ASSOC);

			return $result;

		} catch (PDOException $e) {
			echo 'ログインに失敗しました。'.$e->getMessage();
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
				'INSERT INTO user_physical_datas (user_id,weight,fat_percentage,muscle_mass,water_content,visceral_fat,basal_metabolic_rate,bmi,created_at,updated_at, delete_flag) VALUES (:user_id,:weight,:fat_percentage,:muscle_mass,:water_content,:visceral_fat,:basal_metabolic_rate,:bmi,now(),now(),0)'
			);
			$smt->bindParam(':user_id', $data['user_id'], PDO::PARAM_INT);
			$smt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
			$smt->bindParam(':fat_percentage', $data['fat_percentage'], PDO::PARAM_STR);
			$smt->bindParam(':muscle_mass', $data['muscle_mass'], PDO::PARAM_STR);
			$smt->bindParam(':water_content', $data['water_content'], PDO::PARAM_STR);
			$smt->bindParam(':visceral_fat', $data['visceral_fat'], PDO::PARAM_STR);
			$smt->bindParam(':basal_metabolic_rate', $data['basal_metabolic_rate'], PDO::PARAM_STR);
			$smt->bindParam(':bmi', $data['bmi'], PDO::PARAM_STR);
			$smt->execute();

		} catch (PDOException $e) {
			echo 'データの入力ができませんでした。'.$e->getMessage();
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
				'UPDATE  user_physical_datas SET weight = :weight,fat_percentage = :fat_percentage,muscle_mass = :muscle_mass,water_content = :water_content,visceral_fat = :visceral_fat,basal_metabolic_rate = :basal_metabolic_rate,bmi = :bim,updated_at = now() WHERE data_id = data_id)'
			);
			$smt->bindParam(':data_id', $data_id, PDO::PARAM_INT);
			$smt->bindParam(':weight', $data['weight'], PDO::PARAM_STR);
			$smt->bindParam(':fat_percentage', $data['fat_percentage'], PDO::PARAM_STR);
			$smt->bindParam(':muscle_mass', $data['muscle_mass'], PDO::PARAM_STR);
			$smt->bindParam(':water_content', $data['water_content'], PDO::PARAM_STR);
			$smt->bindParam(':visceral_fat', $data['visceral_fat'], PDO::PARAM_STR);
			$smt->bindParam(':basal_metabolic_rate', $data['basal_metabolic_rate'], PDO::PARAM_STR);
			$smt->bindParam(':bmi', $data['bmi'], PDO::PARAM_STR);
			$smt->execute();

		} catch (PDOException $e) {
			echo 'データの更新が出来ませんでした。'.$e->getMessage();
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
				'SELECT * FROM user_physical_datas ORDER BY created_at DESC limit 20 WHERE user_id = :user_id AND delete_flag = 0'
			);
			$smt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
			$smt->execute();
			// 実行結果を配列に返す。
			$result = $smt->fetchAll(PDO::FETCH_ASSOC);

			return $result;

		} catch (PDOException $e) {
			echo 'データの読み込みが出来ませんでした。'.$e->getMessage();
		}
	}

	/**
	 * データをDBから読み込み
	 */
	function getPhysicalData($data_id)
	{
		try {
			// 登録データ取得
			$smt = $this->pdo->prepare(
				'SELECT * FROM user_physical_datas WHERE data_id = :data_id AND delete_flag = 0'
			);
			$smt->bindParam(':data_id', $data_id, PDO::PARAM_INT);
			$smt->execute();
			// 実行結果を配列に返す。
			$result = $smt->fetchAll(PDO::FETCH_ASSOC);

			return $result;

		} catch (PDOException $e) {
			echo 'データの読み込みが出来ませんでした。'.$e->getMessage();
		}
	}

	/**
	 * データを論理削除する
	 */
	function deletePhysicalData($data_id)
	{
		try {
			// 登録データ論理削除
			$smt = $this->pdo->prepare(
				'UPDATE user_physical_datas SET delete_flag = 1 WHERE data_id = :data_id'
			);
			$smt->bindParam(':data_id', $data_id, PDO::PARAM_INT);
			$smt->execute();

		} catch (PDOException $e) {
			echo 'データの削除が出来ませんでした。'.$e->getMessage();
		}
	}
}
