<?php

namespace Advik\CustomerCreation\Controller\Index;

use AllowDynamicProperties;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Advik\CustomerCreation\Helper\CustomerHelper;

#[AllowDynamicProperties]
class Index extends Action
{
	public function __construct(
		Context $context,
		CustomerHelper $customerHelper,
	)
	{
		$this->customerHelper = $customerHelper;
		parent::__construct($context);
	}

	/**
	 * @throws \Exception
	 */
	public function execute()
	{
		/**
		 * @var \Magento\Framework\View\Result\Page $result
		 */
		$result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$result->getConfig()->getTitle()->prepend('Test Page');
		$customer = $this->customerHelper->createCustomerComprehensive(
			[
				'prefix' => 'Mr.',
				'group_id' => 1,
				'firstname' => 'Joshi',
				'lastname' => 'Thakur',
				'password' => 'joshi@123',
				'email' => 'joshithakur@nomail.com',
				'dob' => '2002/03/12',
				'gender' => 1,
				'is_active' => 1,
				'street' => '123 Main St',
				'city' => 'New York',
				'region' => '2',
				'country_id' => 'US',
				'telephone' => '(145)8452892',
				'postcode' => '33022',
				'address_default_billing' => true,
				'address_default_shipping' => true,
			]
		);
		if ($customer->getId()) {
			$this->messageManager->addSuccessMessage("Customer created successfully with ID: {$customer->getId()}");
		} else {
			$this->messageManager->addErrorMessage('Failed to create customer');
		}
		$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
		return $resultRedirect->setPath('/');
	}
}
