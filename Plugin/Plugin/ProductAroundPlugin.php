<?php
namespace Advik\Plugin\Plugin;

use Magento\Catalog\Model\Product;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Magento\Framework\App\Filesystem\DirectoryList;

class ProductAroundPlugin
{
	protected $logger;

	public function __construct(
		\Magento\Framework\Filesystem\DirectoryList $directoryList
	)
	{
		$logDir = $directoryList->getPath(DirectoryList::LOG);
		$logFile = $logDir . '/advik_direct.log';
		$this->logger = new Logger('advik_custom_logger');
		$this->logger->pushHandler(new StreamHandler($logFile, Logger::DEBUG));
	}

	/**
	 * Around plugin for Product Save
	 */
	public function aroundSave(
		Product $subject,
		callable $proceed
	) {
		$startTime = microtime(true);
		$productSku = $subject->getSku();

		try {
			// 1. call original function
			$result = $proceed();

			// 2. Perfomance Check
			$endTime = microtime(true);
			$executionTime = $endTime - $startTime;

			// if it took more than 1 second to save (Slow Execution)
			if ($executionTime > 1.0) {
				$this->logger->info("Slow Save Detected: SKU $productSku took " . number_format($executionTime, 4) . " seconds.");
			}

			return $result;

		} catch (\Exception $e) {
			// 3. Catch original function error and log
			$this->logger->error("Error saving product SKU $productSku: " . $e->getMessage());

			// Send error to handle by magento
			throw $e;
		}
	}
}