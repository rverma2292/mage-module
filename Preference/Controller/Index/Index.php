<?php

namespace Advik\Preference\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
	protected $notifier;

	public function __construct(
		Context $context,
		\Advik\Preference\Api\NotifyInterface $notifier
	)
	{
		$this->notifier = $notifier;
		parent::__construct($context);
	}

	public function execute()
	{
		/**
		 * @var \Magento\Framework\View\Result\Page $result
		 */
		$result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$result->getConfig()->getTitle()->prepend('Test Page');
		echo get_class($this->notifier)."<br>";
		die('MNKLKMNK');
		return $result;
	}
}
