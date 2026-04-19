<?php
namespace Advik\BlogApi\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\ObjectManager\ConfigInterface;

class Test extends Action
{
	public function execute()
	{
		/**
		 * @var \Magento\Framework\View\Result\Page $result
		 */
		// Todo: Try to know why plugin not working for resolver class
		// Todo: Ask Noor to what we can do to override the Resolver
		// Todo: Plugin Example
		// Todo: Preference Example
		// Todo: Programmatically Product Example
		// Todo: Programmatically Customer Example
		// Todo: Programmatically Product Attribute Example
		// Todo: Programmatically Category Attribute Example
		// Todo: Programmatically Quote Example
		// Todo: Programmatically Quote revive Example
		// Todo: Programmatically Order Example
		// Todo: Payment Method
		// Todo: Shipping Method
		// Todo: Shipping Method
		// Todo: Mail Configuration Code
		// Todo: Generate CSV Code
		// Todo: Console Command
		// Todo: Try to learn JS code - basic code shim, * all example to understand
		// Todo: Try to learn Learn JS class to use - render html pages
		// Todo: Try to learn Checkout page modification
		// Todo: Theme Registration
		// Todo: Differentiate Flat Catalog
		// Todo: Add more feature in forms
		// Todo: How to create Email Template
		// Todo: How to use Widget
		// Todo: Know All Basic Classes e.g. Product, Store, Customer, Order, Get Payment Methods, Shipping Method etc

		$result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$result->getConfig()->getTitle()->prepend('Test Page');
		echo $this->notifier->send('Calling from Test Controller'); die(' 578');
		$diConfig = $this->_objectManager->get(ConfigInterface::class);
		$interface = 'Magento\\Framework\\GraphQl\\Query\\Resolver\\ContextInterface';
		$dd = $this->_objectManager->create(\Magento\GraphQl\Model\Query\Resolver\Context::class);
		$encryptor = $this->_objectManager->get(EncryptorInterface::class);
		echo $encryptor->getHash('rahul@123', true);
		echo '<pre>';
		print_r($diConfig->getPreference($interface));
		echo '</pre>';
		die('27');
		return $result;
	}
}


