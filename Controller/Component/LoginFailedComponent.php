<?php
App::uses('Component', 'Controller');

class LoginFailedComponent extends Component {

	const CACHE_KEY = 'users_login';

	/**
	 * controller
	 *
	 * @var Controller
	 */
	protected $controller = null;

	/**
	 * @var int
	 */
	protected $limit = 4;

	/**
	 * @var null
	 */
	protected $prefix = null;

	/**
	 * @var string
	 */
	protected $model = 'Auth';

	/**
	 * @var string
	 */
	protected $column = 'loginid';

	/**
	 * Initialize
	 *
	 * @param Controller $controller
	 */
	public function initialize(Controller $controller) {
		$this->controller = $controller;
	}

	/**
	 * ログイン失敗記録用のキーを作成します
	 * @return string
	 * @throws Exception
	 */
	public function cacheName() {
		$prefix = !empty($this->prefix) ? $this->prefix . '_' : '';
		return 'auth_failed_' . $prefix . $this->controller->request->data[$this->model][$this->column];
	}

	/**
	 * 失敗回数をチェックします
	 *
	 * @param $actions
	 * @param $limit
	 * @return bool
	 */
	public function check($actions, $limit) {
		if (!$this->controller->request->is('post')) {
			return true;
		}
		if (!in_array($this->controller->request->params['action'], $actions)) {
			return true;
		}
		if (Cache::read($this->cacheName(), self::CACHE_KEY) >= $this->limit) {
			return false;
		}
		return true;
	}

	/**
	 * 失敗を記録します
	 * @param $actions
	 */
	public function record($actions) {
		if (!in_array($this->controller->request->params['action'], $actions)) {
			return;
		}
		if (!$this->controller->request->is('post') || empty($this->controller->request->data)) {
			return;
		}
		$cacheValue = Cache::read($this->cacheName(), self::CACHE_KEY);
		Cache::write($this->cacheName(), (int)$cacheValue + 1, self::CACHE_KEY);
	}

}
