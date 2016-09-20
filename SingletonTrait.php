<?php

namespace Lumi\SecurePostWithLink;


trait SingletonTrait {

	/**
	 * Instance holder
	 */
	private static $instance;

	/**
	 * @return $this
	 */
	public static function getInstance()
	{
		if (null === static::$instance) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	public function __construct() {
	}

	public function __clone() {
	}

}