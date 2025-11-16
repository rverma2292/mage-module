<?php

namespace Advik\BlogApi\Controller\Adminhtml\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
	protected PageFactory $resultPageFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
	)
	{
		$this->resultPageFactory = $resultPageFactory;
		parent::__construct($context);
	}

	public function execute(){
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Advik_BlogApi::blog');
		$resultPage->getConfig()->getTitle()->prepend(__('Blog Posts'));
		return $resultPage;
	}
}