<?php

namespace Shopware\SwagCustomSort\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\SwagCustomSort\Components\Listing;

class Resource implements SubscriberInterface
{
	/**
	 * @var \Shopware\Components\DependencyInjection\Container
	 */
	private $container;

	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents()
	{
		$base = 'Enlight_Bootstrap_InitResource_';
		return array(
			$base . 'swagcustomsort.listing_component' => 'onInitListingComponent'
		);
	}

	public function onInitListingComponent()
	{
		return new Listing(
			Shopware()->Config()
		);
	}
}