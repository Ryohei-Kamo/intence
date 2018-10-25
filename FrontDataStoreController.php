<?php

namespace App\Front\UserInterface\Http\Controller\Api;

use App\Front\Application\Exception\FrontInvalidCategoryException;
use App\Front\UserInterface\Exception\UIHttpCategoryFeatureRefusedException;
use App\Front\UserInterface\Exception\UIHttpContinuousPostException;
use App\Front\UserInterface\Exception\UIHttpInvalidCategoryException;
use App\Front\UserInterface\Exception\UIHttpPostingThemeRefusedException;
use App\Front\UserInterface\Exception\UIHttpUserNotFoundException;
use DB;
use Exception;
use Illuminate\Http\Request;
use App\Front\Application\Exception\FrontPostStoreServiceException;
use App\Front\Application\Service\FrontPostStoreService;
use App\Front\UserInterface\Validator\Api\FrontApiPostValidator;
use App\Front\UserInterface\Exception\UIHttpInternalServerErrorException;
use App\Front\UserInterface\Exception\UIHttpValidationErrorException;
use App\Common\Domain\Entity\Value\EsSyncEventIdValue;
use App\Common\Jobs\EsSynchronizeJob;
use Queue;
use Log;

/**
 * データ登録コントローラ
 */
class FrontApiPostStoreController extends FrontApiBaseController
{
	/**
	 * 投稿登録バリデータ
	 *
	 * @var FrontApiPostValidator
	 */
	private $validator;

	/**
	 * 投稿登録サービス
	 */
	private $post_service;

	/**
	 * コンストラクタ
	 *
	 * @param FrontApiPostValidator $validator
	 * @param FrontPostStoreService $post_service
	 * @param Request               $request
	 */
	public function __construct(
		FrontApiPostValidator $validator,
		FrontPostStoreService $post_service,
		Request $request
	) {
		parent::__construct($request);
		$this->validator = $validator;
		$this->post_service = $post_service;
	}

	/**
	 * 投稿情報を登録する
	 *
	 * @param Request $request
	 * @return array
	 */
	public function store(Request $request)
	{
		//アクセストークンよりのユーザーIDを取得する。
		$user_id = $this->getUserId();

		$param = [
			'category_id' => $request->input('category_id'),
			'type'        => $request->input('type'),
			'caption'     => $request->input('caption'),
			'stamp'       => $request->input('stamp'),
		];

		//バリデート処理
		if (! $this->validator->with($param)->withRules('store')->passes()) {
			throw new UIHttpValidationErrorException(
				$this->validator->errors()
			);
		}

		$dbc = DB::connection('mysql_master');

		//トランザクション開始
		$dbc->beginTransaction();

		try {
			$post_id = $this->post_service->store($dbc, $param, $user_id);
			$dbc->commit();

		} catch (FrontPostStoreServiceException $e) {
			//ロールバックして例外処理対応するExceptionを投げる。
			$dbc->rollback();
			switch ($e->getcode()) {
				case FrontPostStoreServiceException::USER_NOT_AUTHORIZED:
					throw new UIHttpPostingThemeRefusedException($e);
				case FrontPostStoreServiceException::TYPE_NOT_SET_VALUE:
					throw new UIHttpUserNotFoundException($e);
				case  FrontPostStoreServiceException::CONTINUOUS_POST:
					throw new UIHttpContinuousPostException($e);
				case FrontPostStoreServiceException::USER_NOT_AUTHORIZED_BY_FEATURE:
					throw new UIHttpCategoryFeatureRefusedException($e);
			}
		} catch (FrontInvalidCategoryException $e) {
			$dbc->rollback();
			throw new UIHttpInvalidCategoryException($e);
		} catch (Exception $e) {
			//ロールバックして例外処理対応するExceptionを投げる。
			$dbc->rollback();
			throw new UIHttpInternalServerErrorException($e);
		}

		// 返却値
		return response()->json(
			[
				'post_id' => (int) $post_id,
			],
			201
		);
	}

	/**
	 * アクセストークンにユーザIDを含む必要があるAPIか
	 *
	 * @return bool
	 */
	protected function isRequiredUserId()
	{
		return true;
	}
}
