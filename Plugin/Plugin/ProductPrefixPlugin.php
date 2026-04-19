<?php

namespace Advik\Plugin\Plugin;

use Magento\Catalog\Model\Product;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Magento\Framework\App\Filesystem\DirectoryList;

class ProductPrefixPlugin
{
	protected $logger;

	public function __construct(
		\Magento\Framework\Filesystem\DirectoryList $directoryList
	) {
		$logDir = $directoryList->getPath(DirectoryList::LOG);
		$logFile = $logDir . '/advik_direct.log';
		$this->logger = new Logger('advik_custom_logger');
		$this->logger->pushHandler(new StreamHandler($logFile, Logger::INFO));
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
		return __("Prefix ") .$result;
	}
}