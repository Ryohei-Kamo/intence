<?php

namespace App\Common\Infrastructure\Db;

use App\Common\Exception\LogicException;

/**
 * 基底DB
 */
abstract class BaseDb
{
	/**
	 * テーブル名
	 */
	const TABLE_NAME = null;

	/**
	 * @var array 主キー
	 */
	protected $primary_key = [];

	/**
	 * @var array テーブルカラム
	 */
	protected $table_columns = [];

	/**
	 * コンストラクタ
	 *
	 * @throws LogicException
	 */
	public function __construct()
	{
		if (is_null(static::TABLE_NAME)) {
			throw new LogicException('テーブル名が設定されていません。');
		}

		if (empty($this->primary_key)) {
			throw new LogicException('主キーが設定されていません。');
		}

		if (empty($this->table_columns)) {
			throw new LogicException('テーブルカラムが設定されていません。');
		}
	}


	/**
	 * 主キーで検索する
	 *
	 * @param  \Illuminate\Database\Connection $dbc DBコネクション
	 * @param  array                           $key 対象のキー
	 * @return array レコード
	 */
	public function findByKey($dbc, $key)
	{
		$builder = $dbc->table(static::TABLE_NAME)->select($this->table_columns);
		$builder = $this->setWhereOfPrimarykey($builder, $key);

		$record = $builder->first();

		return $record;
	}

	/**
	 * 挿入する
	 *
	 * @param  \Illuminate\Database\Connection $dbc    DBコネクション
	 * @param  array                           $values 挿入値
	 * @return int ID
	 */
	public function insert($dbc, $values)
	{
		$id = $dbc->table(static::TABLE_NAME)
			->insertGetId($values);

		return $id;
	}

	/**
	 * 主キーで更新する
	 *
	 * @param  \Illuminate\Database\Connection $dbc    DBコネクション
	 * @param  array                           $values 更新値
	 * @param  array                           $key    対象のキー
	 * @return int 更新件数
	 */
	public function updateByKey($dbc, $values, $key)
	{
		$builder = $dbc->table(static::TABLE_NAME);
		$builder = $this->setWhereOfPrimarykey($builder, $key);

		$count = $builder->update($values);

		return $count;
	}

	/**
	 * 主キーで削除する
	 *
	 * @param  \Illuminate\Database\Connection $dbc DBコネクション
	 * @param  array                           $key 対象のキー
	 * @return int 削除件数
	 */
	public function deleteByKey($dbc, $key)
	{
		$builder = $dbc->table(static::TABLE_NAME);
		$builder = $this->setWhereOfPrimarykey($builder, $key);

		$count = $builder->delete();

		return $count;
	}

	/**
	 * 主キー用の検索条件を設定する
	 *
	 * @param  \Illuminate\Database\Query\Builder $builder クエリビルダー
	 * @param  array                              $wheres  検索パラメータ
	 * @return \Illuminate\Database\Query\Builder
	 * @throws LogicException
	 */
	private function setWhereOfPrimarykey($builder, $wheres)
	{
		foreach ($this->primary_key as $key) {
			if (! array_key_exists($key, $wheres)) {
				$wheres_str = implode(',', array_keys($wheres));
				throw new LogicException("検索パラメータが不足しています。wheres[${wheres_str}]");
			}

			$builder->where($key, $wheres[$key]);
		}

		return $builder;
	}

	/**
	 * ソート条件が利用可能なものであるかチェックする
	 *
	 * @param string $sort DBクラスに定義されたソート条件
	 * @return void
	 * @throws LogicException
	 */
	protected function checkSort($sort)
	{
		if (is_null($sort)) {
			return;
		}

		if (! in_array($sort, $this->getSortList())) {
			throw new LogicException("Invalid sort string ($sort)");
		}
	}

	/**
	 * 利用可能なソート条件のリストを返す
	 *
	 * @return array ソート条件の配列
	 *               例)
	 *               [
	 *                   static::SORT_AAA_DESC,
	 *                   static::SORT_BBB_DESC,
	 *                     ・
	 *                     ・
	 *               ]
	 * @throws LogicException
	 */
	protected function getSortList()
	{
		throw new LogicException('継承先クラスで'.__METHOD__.'()を定義してください');
	}

	/**
	 * LIMIT句を取得する
	 *
	 * @param int $limit  取得件数
	 * @param int $offset 取得開始位置(0開始)
	 * @return string
	 */
	protected function buildLimitClause($limit, $offset)
	{
		if (is_null($limit)) {
			return '';
		}

		$limit = (int) $limit;
		$offset = (int) $offset;

		return "LIMIT {$offset}, {$limit}";
	}
}

