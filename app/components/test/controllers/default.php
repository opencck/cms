<?php
namespace APP\Components\Test\Controllers;

use API\App;
use API\DB\Cache;
use API\Session;
use APP\Controller;
use Exception;
use Memcached;
use SessionHandler;

class defaultController extends Controller {
	/**
	 * @param $input
	 * @return array
	 * @throws Exception
	 */
	public function default($input) {
		$session = Session::getInstance();
		$store = App::getInstance()->getStore();

		$cache = Cache::getInstance();
		$cache->set('cache_test', 'cache is worked');

		$session->set('test', $session->get('test', 0, 'int') + 1);

		$cacheHandlers = $cache->getHandlers();

		/** @var \Redis $redis */
		$redis = $cacheHandlers['redis']->getHandler();
		$redis->set('redis_test', 'redis is worked');

		/** @var \Memcached $memcached */
		$memcached = $cacheHandlers['memcached']->getHandler();
		$memcached->set('memcached_test', 'memcached is worked');

		return [
			'params' => $input,
			'session' => $session,
			'store' => $store,
			'cache' => $cache->get('cache_test'),
			'path' => $session->getLocation().'.'.$session->getId(),
			'memcached' => $memcached->get('memcached_test'),
			'memcachedData' => $memcached->get($session->getLocation().':'.$session->getId()),
			'redis' => $redis->get('redis_test'),
			'redisData' => $redis->get($session->getLocation().':'.$session->getId()),
			//'redis_flush' => $redis->flushAll(),
			'_SESSION' => $_SESSION
		];
	}
}
