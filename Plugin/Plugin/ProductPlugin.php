<?php

namespace Advik\Plugin\Plugin;

use Magento\Catalog\Model\Product;
use Psr\Log\LoggerInterface;

class ProductPlugin
{
	/**
	 * @param LoggerInterface $logger
	 */
	public function __construct(LoggerInterface $logger) //$logger should be same as in di.xml file
	{
		$this->logger = $logger;
	}

	/**
	 * @param Product $subject
	 * @param string $result
	 * @return string
	 */
	public function afterGetName(Product $subject, $result): string
	{
		$now = \DateTime::createFromFormat('U.u', number_format(microtime(true), 6, '.', ''));
		$this->logger->info('Plugin executed at: ' . $now->format('d.m.Y H:i:s.u') . ' for '. $subject->getSku());
		return $result . __("Testing");
	}
}