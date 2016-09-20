<?php

namespace Lumi\SecurePostWithLink;


interface ProviderInterface {

	/**
	 * Method called on plugin load
	 * Use only for attaching WP hooks
	 */
	public function boot();

}