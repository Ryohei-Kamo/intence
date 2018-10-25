<?php

namespace App\Common\Infrastructure\Db;

use App\Common\Domain\Entity\Value\MamaPostPublicLevelValue;
use App\Common\Domain\Entity\Value\MamaPostDeleteFlagValue;
use App\Common\Domain\Entity\Value\MamaCommentPublicLevelValue;
use App\Front\UserInterface\Helper\FrontDateTimeHelper;
use Illuminate\Database\Connection;

/**
 * 投稿 DB
 */
class MamaPostsDb extends BaseDb
{
	const TABLE_NAME = 'user_physical_data';

	const SORT_POST_DETAIL_DESC = 'desc';

	const SORT_POST_DETAIL_ASC = 'asc';

	/**
	 * @var array 主キーリスト
	 */
	protected $primary_key = [
		'data_id',
	];

	/**
	 * @var array テーブルカラムリスト
	 */
	protected $table_columns = [
		'data_id',
		'user_id',
		'body_weight',
		'body_fat_percentage',
		'muscle_mass',
		'body_water_content',
		'visceral_fat',
		'basal_metabolic_rate',
		'bmi',
		'pub_date',
		'updated_at',
		'delete_flag',
		'monitoring_status',
		'checked_admin_user_id',
		'checked_at',
	];

	/**
	 * 新着投稿一覧を検索する（[A-PST-003] カテゴリ別投稿一覧用）
	 *
	 * @param  Connection $dbc         DBコネクション
	 * @param  int        $limit       リミット
	 * @param  int | null $category_id カテゴリID
	 * @param  string     $sort        ソート順
	 * @param  int        $offset_id   オフセットID
	 * @return array レコード
	 */
	public function findNormalPostList($dbc, $category_id, $limit, $sort, $offset_id)
	{
		// 取得項目
		$column_list = [];
		foreach ($this->table_columns as $val) {
			$column_list[$val] = 'mp.'.$val;
		}

		array_push(
			$column_list,
			'mpb.body AS body',
			'uu.nickname AS nickname',
			'uum.profile_image_key AS profile_image_key'
		);

		$str_columns = implode(',', $column_list);

		// SQL生成
		$query = $this->buildNormalPostListQuery(
			$str_columns,
			$category_id,
			$limit,
			$sort,
			$offset_id
		);

		$sql = $query['sql'];
		$bindings = $query['bindings'];

		return $dbc->select($sql, $bindings);
	}

	/**
	 * [A-PST-003] カテゴリ別投稿一覧用のSQL作成
	 *
	 * @param string $str_columns SELECT句の文字列
	 * @param int    $category_id カテゴリID
	 * @param int    $limit       取得件数
	 * @param string $sort        取得ソート順
	 * @param int    $offset_id   オフセットID
	 * @return array
	 */
	protected function buildNormalPostListQuery(
		$str_columns,
		$category_id,
		$limit,
		$sort,
		$offset_id
	) {
		// バインド変数
		$bindings = [];
		$bindings['public_level'] = MamaPostPublicLevelValue::DISPLAY;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;
		// カテゴリIDの条件追加
		if (! $category_id) {
			$category_id_sql = '';
		} else {
			$category_id_sql = 'AND mp.category_id = :category_id ';
			$bindings['category_id'] = $category_id;
		}
		// ORDER BY句、LIMIT句の追加
		if ($limit) {
			$bindings['limit'] = $limit;
		}
		// ソート条件追加
		if (! $sort) {
			$sort_sql = 'desc';
		} else {
			$sort_sql = $sort;
		}
		// オフセットIDの条件追加
		if (! $offset_id) {
			$offset_id_sql = '';
		} else {
			if ($sort_sql == 'desc') {
				$offset_id_sql = 'AND mp.post_id < :post_id ';
				$bindings['post_id'] = $offset_id;
			} elseif ($sort_sql == 'asc') {
				$offset_id_sql = 'AND mp.post_id > :post_id ';
				$bindings['post_id'] = $offset_id;
			}
		}

		// SQL
		$sql = <<<___SQL___
SELECT $str_columns
FROM mama_posts AS mp 
INNER JOIN mama_post_bodies AS mpb 
ON mp.post_id = mpb.post_id 
INNER JOIN user_users AS uu 
ON mp.user_id = uu.user_id 
LEFT JOIN user_users_meta AS uum 
ON uu.user_id = uum.user_id 
WHERE mp.public_level = :public_level
AND mp.delete_flag = :delete_flag 
$offset_id_sql 
$category_id_sql 
ORDER BY mp.pub_date $sort_sql 
LIMIT :limit 
___SQL___;
		return ['sql' => $sql, 'bindings' => $bindings];
	}

	/**
	 * 投稿テーブルのコメント数を＋１する
	 * 最新のコメントIDを更新する。
	 * updated_atを現在時刻に更新する。
	 *
	 * @param Connection                      $dbc DBコネクション
	 * @param                                 $post_id
	 * @param                                 $comment_id
	 */
	public function applyNewComment($dbc, $post_id, $comment_id)
	{
		$binds = [
			'post_id'         => (int) $post_id,
			'last_comment_id' => (int) $comment_id,
			'updated_at'      => FrontDateTimeHelper::createDate(),
		];
		$sql = <<<___SQL___
UPDATE mama_posts 
  SET 
	comment_count = comment_count + 1
	, updated_at = :updated_at
	, last_comment_id = :last_comment_id
  WHERE post_id = :post_id 
___SQL___;
		$dbc->update($sql, $binds);
	}

	/**
	 * 投稿テーブルのハート数を＋１する
	 *
	 * @param  Connection $dbc     DBコネクション
	 * @param  int        $post_id 投稿ID
	 * @return int                                      UPDATEした行数
	 */
	public function countUpHeart($dbc, $post_id)
	{
		$bindings = [
			'post_id'    => $post_id,
			'updated_at' => FrontDateTimeHelper::createDate(),
		];
		$sql = <<<___SQL___
UPDATE mama_posts
SET heart_count = heart_count+1, updated_at = :updated_at
WHERE post_id = :post_id
___SQL___;
		return $dbc->update($sql, $bindings);
	}

	/**
	 * 投稿テーブルの総ハート数を＋１する
	 *
	 * @param  Connection $dbc     DBコネクション
	 * @param  int        $post_id 投稿ID
	 * @return int                                      UPDATEした行数
	 */
	public function countUpTotalHeart($dbc, $post_id)
	{
		$bindings = [
			'post_id'    => $post_id,
			'updated_at' => FrontDateTimeHelper::createDate(),
		];
		$sql = <<<___SQL___
UPDATE mama_posts
SET total_heart_count = total_heart_count+1, updated_at = :updated_at
WHERE post_id = :post_id
___SQL___;
		return $dbc->update($sql, $bindings);
	}

	/**
	 * 投稿テーブル、投稿内容テーブルより削除していない投稿を取得する
	 *
	 * @param Connection                      $dbc DBコネクション
	 * @param                                 $post_id
	 * @return array
	 */
	public function findByPostId($dbc, $post_id)
	{
		$column_list = [];

		foreach ($this->table_columns as $key => $val) {
			$column_list[$key] = 'mp.'.$val;
		}

		$str_column_list = implode(',', $column_list);

		$sql = <<<___SQL___
SELECT $str_column_list
  , mpb.body
  ,  (CASE WHEN mp.heart_count > 0 OR mp.comment_count > 0 THEN 0
   ELSE 1 END) as deletable
FROM mama_posts mp
  JOIN mama_post_bodies mpb
  ON mp.post_id = mpb.post_id
WHERE mp.post_id = :post_id
AND mp.delete_flag = :delete_flag 
___SQL___;

		$bindings['post_id'] = (int) $post_id;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;

		return $dbc->selectOne($sql, $bindings);
	}

	/**
	 * 投稿テーブル、投稿内容テーブルより取得する
	 *
	 * @param Connection                      $dbc DBコネクション
	 * @param                                 $post_id
	 * @return array
	 */
	public function findPostJoinByKey($dbc, $post_id)
	{
		$column_list = [];

		foreach ($this->table_columns as $key => $val) {
			$column_list[$key] = 'mp.'.$val;
		}

		$str_column_list = implode(',', $column_list);

		$sql = <<<___SQL___
SELECT $str_column_list
  , mpb.body
  ,  (CASE WHEN mp.heart_count > 0 OR mp.comment_count > 0 THEN 0
   ELSE 1 END) as deletable
FROM mama_posts mp
  JOIN mama_post_bodies mpb
  ON mp.post_id = mpb.post_id
WHERE mp.post_id = :post_id
___SQL___;

		$bindings['post_id'] = (int) $post_id;

		return $dbc->selectOne($sql, $bindings);
	}

	/**
	 * コメントの一覧を取得する
	 *
	 * コメントテーブルで、投稿Idに該当する全レコードを取得(取得カラムはコメントIdのみ)
	 * 公開状態(public_level=1)&削除属性(delete_flag=0)も検索条件に加える。
	 *
	 * @param Connection $dbc DBコネクション
	 * @param int        $user_id
	 * @param null       $sort
	 * @param null       $limit
	 * @param null       $offset
	 * @return array
	 */
	public function findIdsByUserId($dbc, $user_id, $sort = null, $limit = null, $offset = null)
	{
		$column_list = [];

		foreach ($this->table_columns as $key => $val) {
			$column_list[$key] = 'mp.'.$val;
		}

		$str_column_list = implode(',', $column_list);

		$conditions = [
			'mp.user_id = :user_id',
			'mp.public_level = :public_level',
			'mp.delete_flag= :delete_flag',
		];
		$bindings = [
			'user_id'      => $user_id,
			'public_level' => MamaPostPublicLevelValue::DISPLAY,
			'delete_flag'  => MamaPostDeleteFlagValue::NOT_DELETE,
		];
		$where_clause = 'WHERE '.implode(' AND ', $conditions);

		$this->checkSort($sort);
		// LIMIT句の追加
		$bindings['limit'] = $limit;

		// オフセットIDの条件追加
		if (! $offset) {

			$offset_id_sql = '';

		} else {

			if ($sort == 'desc') {
				$offset_id_sql = 'AND mp.post_id < :post_id ';
				$bindings['post_id'] = $offset;

			} elseif ($sort == 'asc') {
				$offset_id_sql = 'AND mp.post_id > :post_id ';
				$bindings['post_id'] = $offset;
			}
		}

		//削除の可否を取得するようにする。
		$sql = <<<___SQL___
SELECT $str_column_list
  , mpb.body
  ,  (CASE WHEN mp.heart_count > 0 OR mp.comment_count > 0 THEN 0
   ELSE 1 END) as deletable
FROM mama_posts mp
  JOIN mama_post_bodies mpb
  ON mp.post_id = mpb.post_id
$where_clause
$offset_id_sql
ORDER BY mp.pub_date $sort
LIMIT :limit 
___SQL___;

		return $dbc->select($sql, $bindings);
	}

	/**
	 * 利用可能なソート条件のリストを返す
	 *
	 * @return array ソート条件の配列
	 */
	protected function getSortList()
	{
		return [
			self::SORT_POST_DETAIL_DESC,
			self::SORT_POST_DETAIL_ASC,
		];
	}

	/**
	 * ユーザ名刺用に投稿情報を取得する
	 *
	 * @param  Connection $dbc     DBコネクション
	 * @param  int        $post_id 投稿ID
	 * @return array
	 */
	public function findPostDetailByKey($dbc, $post_id)
	{
		$column_list = [
			'mama_posts.post_id',
			'mama_posts.user_id',
			'mama_posts.category_id',
			'mama_posts.type',
			'mama_posts.caption',
			'mama_post_bodies.body',
		];
		$str_columns = implode(',', $column_list);

		$sql = <<<___SQL___
SELECT $str_columns
FROM mama_posts
INNER JOIN mama_post_bodies
  ON mama_posts.post_id = mama_post_bodies.post_id
WHERE mama_posts.post_id = :post_id
___SQL___;

		$bindings['post_id'] = $post_id;

		return $dbc->selectOne($sql, $bindings);
	}

	/**
	 * 指定した条件に従いスレッドを検索する
	 *
	 * @param Connection $dbc              DBコネクション
	 * @param int        $limit            リミット
	 * @param int        $offset           オフセット
	 * @param array      $search_condition 検索条件
	 * @return array レコード
	 */
	public function findThread($dbc, $limit, $offset, $search_condition = null)
	{
		$conditions = [];
		$bindings = [];
		$where_clause = '';

		$nickname = $search_condition['search_nickname'];
		$post_id = $search_condition['search_post_id'];
		$delete_flag = MamaPostDeleteFlagValue::NOT_DELETE;

		if (! empty($nickname)) {
			$conditions[] = 'uu.nickname = :nickname';
			$bindings[':nickname'] = $nickname;
		}

		if (! empty($post_id)) {
			$conditions[] = 'mp.post_id = :post_id';
			$bindings[':post_id'] = $post_id;
		}

		$conditions[] = 'mp.delete_flag = :delete_flag';
		$bindings[':delete_flag'] = $delete_flag;

		if ($conditions) {
			$where_clause = 'WHERE '.implode(' AND ', $conditions);
		}

		$bindings['limit'] = $limit;
		$bindings['offset'] = $offset;

		$sql = <<<___SQL___
SELECT SQL_CALC_FOUND_ROWS
       mp.post_id
     , mp.type
     , mp.pub_date
     , mp.device
     , mp.ipaddress
     , mp.monitoring_status
     , uu.nickname AS nickname
     , mpb.body AS body
     , mc.name AS category_name
     , mau.goo_id AS admin_user_goo_id
FROM mama_posts mp
  INNER JOIN user_users uu
     ON mp.user_id = uu.user_id
  INNER JOIN mama_post_bodies mpb
     ON mp.post_id = mpb.post_id
  INNER JOIN master_categories mc
     ON mp.category_id = mc.category_id
  LEFT JOIN monitor_admin_users mau
     ON mp.checked_admin_user_id = mau.admin_user_id
$where_clause
ORDER BY mp.pub_date DESC
LIMIT :offset, :limit
___SQL___;

		return $dbc->select($sql, $bindings);
	}

	/**
	 * IPアドレスに紐付くスレッドを検索する
	 *
	 * @param Connection $dbc              DBコネクション
	 * @param int        $limit            リミット
	 * @param int        $offset           オフセット
	 * @param array      $search_condition 検索条件
	 * @return array レコード
	 */
	public function findThreadByIpaddress($dbc, $limit, $offset, $search_condition)
	{
		$delete_flag = MamaPostDeleteFlagValue::NOT_DELETE;

		$bindings['ipaddress'] = $search_condition['search_ipaddress'];
		$bindings['limit'] = $limit;
		$bindings['offset'] = $offset;
		$bindings['delete_flag'] = $delete_flag;

		$sql = <<<___SQL___
SELECT SQL_CALC_FOUND_ROWS
       mp.post_id
     , mp.type
     , mp.pub_date
     , mp.device
     , mp.ipaddress
     , mp.monitoring_status
     , uu.nickname AS nickname
     , mpb.body AS body
     , mc.name AS category_name
     , mau.goo_id AS admin_user_goo_id
FROM mama_posts mp
  INNER JOIN user_users uu
     ON mp.user_id = uu.user_id
  INNER JOIN mama_post_bodies mpb
     ON mp.post_id = mpb.post_id
  INNER JOIN master_categories mc
     ON mp.category_id = mc.category_id
  LEFT JOIN monitor_admin_users mau
     ON mp.checked_admin_user_id = mau.admin_user_id
WHERE mp.ipaddress = :ipaddress
  AND mp.delete_flag = :delete_flag
ORDER BY mp.pub_date DESC
LIMIT :offset, :limit
___SQL___;

		return $dbc->select($sql, $bindings);
	}

	/**
	 * [A-PST-002] 全カテゴリ投稿一覧 [A-PST-003] カテゴリ別投稿一覧 用改良SQL
	 *
	 * @param Connection $dbc         DBコネクション
	 * @param int        $category_id カテゴリID
	 * @param int        $offset_id   オフセット
	 * @param int        $limit       リミット
	 * @param string     $sort        ソート順
	 * @return array レコード
	 */
	public function findList($dbc, $category_id = null, $offset_id = null, $limit = null, $sort = null)
	{
		// バインド変数
		$bindings = [];
		$bindings['public_level'] = MamaPostPublicLevelValue::DISPLAY;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;
		$bindings['mc_public_level'] = MamaCommentPublicLevelValue::DISPLAY;
		// カテゴリIDの条件追加
		if (! $category_id) {
			$add_where_sql = '';
		} else {
			$add_where_sql = 'AND mp.category_id = :category_id ';
			$bindings['category_id'] = $category_id;
		}
		// ORDER BY句、LIMIT句の追加
		if (! $limit) {
			$limit_sql = '';
		} else {
			$limit_sql = 'LIMIT :limit ';
			$bindings['limit'] = $limit;
		}
		// ソート条件追加
		if (! $sort) {
			$sort_sql = 'desc';
		} else {
			$sort_sql = $sort;
		}
		// オフセットIDの条件追加
		if ($offset_id) {
			if ($sort_sql == 'desc') {
				$add_where_sql .= 'AND mp.post_id < :post_id ';
				$bindings['post_id'] = $offset_id;
			} elseif ($sort_sql == 'asc') {
				$add_where_sql .= 'AND mp.post_id > :post_id ';
				$bindings['post_id'] = $offset_id;
			}
		}

		//SQL取得
		$sql = 'SELECT';
		$index_sql = '';
		$sql .= $this->getFindListCommonSql(
			$index_sql,
			$add_where_sql,
			$sort_sql,
			$limit_sql
		);

		return $dbc->select($sql, $bindings);
	}

	/**
	 * 投稿詳細情報を取得する
	 *
	 * @param  Connection $dbc     DBコネクション
	 * @param  int        $post_id 投稿ID
	 * @return array
	 */
	public function findPostDetailJoinByKey($dbc, $post_id)
	{
		$column_list = [];

		foreach ($this->table_columns as $key => $val) {
			$column_list[$key] = 'mp.'.$val;
		}

		$str_column_list = implode(',', $column_list);

		$sql = <<<___SQL___
SELECT $str_column_list
  , mpb.body
  ,  (CASE WHEN mp.heart_count > 0 OR mp.comment_count > 0 THEN 0
   ELSE 1 END) as deletable
FROM mama_posts mp
  JOIN mama_post_bodies mpb
  ON mp.post_id = mpb.post_id
WHERE mp.post_id = :post_id
  AND mp.delete_flag = :delete_flag
___SQL___;

		$bindings['post_id'] = $post_id;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;

		return $dbc->selectOne($sql, $bindings);
	}

	/**
	 * 直近のSQL_CALC_FOUND_ROWSの結果を返す
	 *
	 * @param  Connection $dbc Connection
	 * @return int 件数
	 */
	public function countFoundRows($dbc)
	{
		$result = $dbc->selectOne('SELECT FOUND_ROWS() AS total');

		return (int) $result['total'];
	}

	/**
	 * 指定されたユーザーの最新の投稿日時を取得する
	 *
	 * @param  Connection $dbc     DBコネクション
	 * @param  int        $user_id ユーザーID
	 * @return string|null レコードの配列0件の場合はnull
	 */
	public function findLastInsertPubDateByUserId($dbc, $user_id)
	{
		$mama_posts = self::TABLE_NAME;

		$sql = <<<___SQL___
SELECT pub_date
FROM $mama_posts
WHERE user_id = :user_id
ORDER BY pub_date desc
LIMIT 1
___SQL___;

		$bindings['user_id'] = $user_id;
		$result = $dbc->selectOne($sql, $bindings);

		return is_null($result) ? $result : $result['pub_date'];
	}

	/**
	 * [A-PST-006] [PC]投稿ランキング
	 *
	 * @param Connection $dbc                   DBコネクション
	 * @param int[]      $post_ids              ランキングの投稿IDの配列
	 * @param int        $category_id           カテゴリID
	 * @param int        $offset                オフセット
	 * @param int        $limit                 リミット
	 * @param int        $excluding_category_id 除外するカテゴリID
	 * @return array レコード
	 */
	public function findListForRanking(
		$dbc,
		$post_ids,
		$category_id = null,
		$offset = null,
		$limit = null,
		$excluding_category_id = null
	) {
		// バインド変数
		$bindings = [];
		$bindings['public_level'] = MamaPostPublicLevelValue::DISPLAY;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;
		$bindings['mc_public_level'] = MamaCommentPublicLevelValue::DISPLAY;

		// 取得投稿ID、ソート
		$post_id_sql = '';
		$order_by_sql = '';
		$where_post_ids = null;
		$sort_post_ids = null;

		foreach ($post_ids as $post_id) {
			$where_post_ids[] = ":post_$post_id";
			$sort_post_ids[] = ":sort_$post_id";
			$bindings["post_$post_id"] = $post_id;
			$bindings["sort_$post_id"] = $post_id;
		}

		if (! is_null($where_post_ids) && ! is_null($sort_post_ids)) {
			$bind_where = implode(', ', $where_post_ids);
			$bind_sort = implode(', ', $sort_post_ids);
			$post_id_sql = "AND mp.post_id IN ({$bind_where})";
			$order_by_sql = "ORDER BY FIELD (mp.post_id, {$bind_sort})";
		}

		// カテゴリIDの条件追加
		if (is_null($category_id) && is_null($excluding_category_id)) {
			$category_id_sql = '';
		} elseif (is_null($category_id) && ! is_null($excluding_category_id)) {
			$category_id_sql = 'AND NOT mp.category_id = :category_id ';
			$bindings['category_id'] = $excluding_category_id;
		} else {
			$category_id_sql = 'AND mp.category_id = :category_id ';
			$bindings['category_id'] = $category_id;
		}

		// offset、LIMIT句の追加
		if (! $offset) {
			$limit_sql = 'LIMIT :limit ';
		} else {
			$limit_sql = 'LIMIT :offset, :limit ';
			$bindings['offset'] = $offset;
		}

		$bindings['limit'] = $limit;

		$sql = $this->getFindListForRankingSql(
			$category_id_sql,
			$post_id_sql,
			$order_by_sql,
			$limit_sql
		);
		return $dbc->select($sql, $bindings);
	}

	/**
	 * findListForRanking()で使用するSQLを返す
	 *
	 * @param $category_id_sql
	 * @param $post_id_sql
	 * @param $order_by_sql
	 * @param $limit_sql
	 * @return string
	 */
	private function getFindListForRankingSql(
		$category_id_sql,
		$post_id_sql,
		$order_by_sql,
		$limit_sql
	) {
		return <<<___SQL___
SELECT STRAIGHT_JOIN
     m_cat.category_id
    ,m_cat.name
    ,m_cat.english_name
    ,mp.post_id
    ,mp.type
    ,mp.caption
    ,mp.media_image_key
    ,mp.media_image_delete_flag
    ,mp.stamp
    ,mp.pub_date
	,mp.comment_count
	,mp.heart_count
	,mp.total_heart_count
	,LEFT(mpb.body, 200) AS body
	,mp.user_id
	,puu.nickname
	,puum.profile_image_key
	,ua1.option_no AS attribute1_option_no
	,ao1.description AS attribute1_description
	,ao1.alias AS attribute1_alias
	,ua2.option_no AS attribute2_option_no
	,ao2.description AS attribute2_description
	,ao2.alias AS attribute2_alias
	,ua3.option_no AS attribute3_option_no
	,ao3.description AS attribute3_description
	,ao3.alias AS attribute3_alias
	,mc.comment_id
	,mcuu.user_id AS comment_user_id
	,mcuu.nickname AS comment_user_nickname
	,mcuum.profile_image_key AS comment_user_profile_image_key
	,mc.pub_date AS comment_pub_date
	,mcb.body AS comment_body
  FROM
    mama_posts AS mp
  INNER JOIN
    mama_post_bodies AS mpb USING(post_id)
  INNER JOIN
    user_users AS puu USING(user_id)
  INNER JOIN
    user_users_meta AS puum ON puu.user_id = puum.user_id
  INNER JOIN
    master_categories AS m_cat USING(category_id)
    /* 投稿用ユーザ属性情報join */
    INNER JOIN
      user_users AS uu ON mp.user_id = uu.user_id
    INNER JOIN
      user_attributes AS ua1 ON ua1.attribute_id = 1 AND uu.user_id = ua1.user_id
    INNER JOIN
      master_attribute_options ao1 ON ao1.attribute_id = 1 AND ua1.option_no = ao1.option_no
    INNER JOIN
      user_attributes AS ua2 ON ua2.attribute_id= 2 AND uu.user_id = ua2.user_id
    INNER JOIN
      master_attribute_options ao2 ON ao2.attribute_id = 2 AND ua2.option_no = ao2.option_no
    INNER JOIN
      user_attributes AS ua3 ON ua3.attribute_id= 3 AND uu.user_id = ua3.user_id
    INNER JOIN
      master_attribute_options ao3 ON ao3.attribute_id= 3 AND ua3.option_no = ao3.option_no
      /* コメント情報取得join */
      LEFT JOIN
        mama_comments AS mc ON mp.last_comment_id = mc.comment_id AND mc.public_level = :mc_public_level
      LEFT JOIN
        mama_comment_bodies AS mcb ON mc.comment_id = mcb.comment_id
      LEFT JOIN
        user_users AS mcuu ON mc.user_id = mcuu.user_id
      LEFT JOIN
        user_users_meta AS mcuum ON mcuu.user_id = mcuum.user_id
        WHERE
          mp.public_level = :public_level
        AND
          mp.delete_flag = :delete_flag
        $category_id_sql
        $post_id_sql
        $order_by_sql
        $limit_sql
___SQL___;
	}

	/**
	 * 新着順で取得(Web版右サイドバー)
	 *
	 * @param  \Illuminate\Database\Connection $dbc                   DBコネクション
	 * @param  integer                         $limit                 取得件数
	 * @param  integer                         $excluding_category_id 除外するカテゴリID
	 * @param  integer                         $category_id           カテゴリID
	 * @return mixed
	 */
	public function findSubNewArrivalsList($dbc, $limit, $excluding_category_id, $category_id)
	{
		$mama_posts = self::TABLE_NAME;

		$category_id_sql = " AND NOT ".$mama_posts.".category_id = :category_id";
		$bindings['category_id'] = $excluding_category_id;
		if (! is_null($category_id)) {
			$category_id_sql = " AND ".$mama_posts.".category_id = :category_id";
			$bindings['category_id'] = $category_id;
		}

		$column_list = [
			$mama_posts.".post_id",
			$mama_posts.".user_id",
			$mama_posts.".category_id",
			$mama_posts.".caption",
			$mama_posts.".pub_date",
			'master_categories.name',
			'master_categories.english_name',
			'user_users.nickname',
			'user_users_meta.profile_image_key',
		];

		$str_column_list = implode(',', $column_list);

		$sql = <<<___SQL___
SELECT
$str_column_list
FROM $mama_posts
 LEFT JOIN master_categories ON mama_posts.category_id = master_categories.category_id
 LEFT JOIN user_users ON mama_posts.user_id = user_users.user_id
 LEFT JOIN user_users_meta ON mama_posts.user_id = user_users_meta.user_id
 WHERE mama_posts.public_level = :public_level
 AND mama_posts.delete_flag = :delete_flag
 $category_id_sql
 ORDER BY mama_posts.pub_date DESC, mama_posts.post_id DESC
 LIMIT :limit
___SQL___;

		$bindings['public_level'] = MamaPostPublicLevelValue::DISPLAY;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;
		$bindings['limit'] = $limit;

		return $dbc->select($sql, $bindings);
	}

	/**
	 * 【Web版】メイン新着取得処理
	 *
	 * @param Connection $dbc                   DBコネクション
	 * @param int        $category_id           カテゴリID
	 * @param int        $offset                オフセット
	 * @param int        $limit                 リミット
	 * @param string     $sort                  ソート順
	 * @param  int       $excluding_category_id 除外するカテゴリID
	 * @return array レコード
	 */
	public function findWebList(
		$dbc,
		$category_id = null,
		$offset = null,
		$limit = null,
		$sort = null,
		$excluding_category_id = null
	) {
		// バインド変数
		$bindings = [];
		$bindings['public_level'] = MamaPostPublicLevelValue::DISPLAY;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;
		$bindings['mc_public_level'] = MamaCommentPublicLevelValue::DISPLAY;
		// カテゴリIDの条件追加
		if (! $category_id) {
			$category_id_sql = 'AND NOT mp.category_id = :category_id ';
			$bindings['category_id'] = $excluding_category_id;
			$index_sql = '';
		} else {
			$category_id_sql = 'AND mp.category_id = :category_id ';
			$bindings['category_id'] = $category_id;
			$index_sql = 'USE INDEX (mama_posts_FK4)';
		}
		// ORDER BY句、LIMIT句の追加
		if (! $limit) {
			$limit_sql = '';
		} else {
			$limit_sql = 'LIMIT :limit ';
			$bindings['limit'] = $limit;
		}
		// ソート条件追加
		if (! $sort) {
			$sort_sql = 'desc';
		} else {
			$sort_sql = $sort;
		}
		// オフセットの条件追加 リミット句に追加する
		if (! $offset) {
			$limit_sql .= '';
		} else {
			$limit_sql .= ' OFFSET :offset';
			$bindings['offset'] = $offset;
		}

		//SQL取得
		$sql = 'SELECT SQL_CALC_FOUND_ROWS';
		$sql .= $this->getFindListCommonSql(
			$index_sql,
			$category_id_sql,
			$sort_sql,
			$limit_sql
		);

		return $dbc->select($sql, $bindings);
	}

	/**
	 * findList(),findWebListで使用するSQLの共通部分を取得する
	 *
	 * @param string $index_sql
	 * @param string $add_where_sql
	 * @param string $sort_sql
	 * @param string $limit_sql
	 * @return string
	 */
	private function getFindListCommonSql(
		$index_sql,
		$add_where_sql,
		$sort_sql,
		$limit_sql
	) {
		return <<<___SQL___
 STRAIGHT_JOIN 
     mp.category_id
    ,mp.post_id
    ,mp.type
    ,mp.caption
    ,mp.media_image_key
    ,mp.media_image_delete_flag
    ,mp.stamp
    ,mp.pub_date
	,mp.comment_count
	,mp.heart_count
	,mp.total_heart_count
	,LEFT(mpb.body, 200) AS body
	,mp.user_id
	,puu.nickname
	,puum.profile_image_key	
	,ua1.option_no AS attribute1_option_no
	,ao1.description AS attribute1_description
	,ao1.alias AS attribute1_alias
	,ua2.option_no AS attribute2_option_no
	,ao2.description AS attribute2_description
	,ao2.alias AS attribute2_alias
	,ua3.option_no AS attribute3_option_no
	,ao3.description AS attribute3_description
	,ao3.alias AS attribute3_alias	
	,mc.comment_id
	,mcuu.user_id AS comment_user_id
	,mcuu.nickname AS comment_user_nickname
	,mcuum.profile_image_key AS comment_user_profile_image_key
	,mc.pub_date AS comment_pub_date
	,mcb.body AS comment_body
  FROM 
    mama_posts AS mp $index_sql
  INNER JOIN 
    mama_post_bodies AS mpb USING(post_id) 
  INNER JOIN 
    user_users AS puu USING(user_id) 
  INNER JOIN 
    user_users_meta AS puum ON puu.user_id = puum.user_id 
    /* 投稿用ユーザ属性情報join */
    INNER JOIN
      user_users AS uu ON mp.user_id = uu.user_id 
    INNER JOIN 
      user_attributes AS ua1 ON ua1.attribute_id = 1 AND uu.user_id = ua1.user_id
    INNER JOIN 
      master_attribute_options ao1 ON ao1.attribute_id = 1 AND ua1.option_no = ao1.option_no
    INNER JOIN 
      user_attributes AS ua2 ON ua2.attribute_id= 2 AND uu.user_id = ua2.user_id
    INNER JOIN 
      master_attribute_options ao2 ON ao2.attribute_id = 2 AND ua2.option_no = ao2.option_no
    INNER JOIN 
      user_attributes AS ua3 ON ua3.attribute_id= 3 AND uu.user_id = ua3.user_id
    INNER JOIN 
      master_attribute_options ao3 ON ao3.attribute_id= 3 AND ua3.option_no = ao3.option_no 
      /* コメント情報取得join */
      LEFT JOIN
        mama_comments AS mc ON mp.last_comment_id = mc.comment_id AND mc.public_level = :mc_public_level
      LEFT JOIN 
        mama_comment_bodies AS mcb ON mc.comment_id = mcb.comment_id 
      LEFT JOIN 
        user_users AS mcuu ON mc.user_id = mcuu.user_id 
      LEFT JOIN 
        user_users_meta AS mcuum ON mcuu.user_id = mcuum.user_id 
        WHERE 
          mp.public_level = :public_level 
        AND 
          mp.delete_flag = :delete_flag $add_where_sql 
        ORDER BY 
          mp.post_id $sort_sql $limit_sql 
___SQL___;
	}

	/**
	 * 指定された範囲内の投稿IDのリストを取得する
	 *
	 * @param Connection $dbc
	 * @param int        $start 以上
	 * @param int        $end   未満
	 * @return array レコード
	 */
	public function findPostIdList($dbc, $start, $end)
	{
		$sql = <<<___SQL___
SELECT mp.post_id
FROM mama_posts mp
WHERE mp.post_id >= :post_id_start
  AND mp.post_id < :post_id_end
  AND mp.public_level = :public_level
  AND mp.delete_flag = :delete_flag
___SQL___;

		$bindings['post_id_start'] = $start;
		$bindings['post_id_end'] = $end;
		$bindings['public_level'] = MamaPostPublicLevelValue::DISPLAY;
		$bindings['delete_flag'] = MamaPostDeleteFlagValue::NOT_DELETE;

		return $dbc->select($sql, $bindings);
	}

	/**
	 * 投稿IDの最大値を取得する
	 *
	 * @param Connection $dbc
	 * @return array レコード
	 */
	public function findMaxPostId($dbc)
	{
		$sql = <<<___SQL___
SELECT MAX(mp.post_id) AS max_post_id
FROM mama_posts mp;
___SQL___;

		$post = $dbc->selectOne($sql);

		return $post['max_post_id'];
	}
}
