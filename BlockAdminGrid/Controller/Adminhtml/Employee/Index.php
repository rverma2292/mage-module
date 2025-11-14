<?php
namespace Advik\BlockAdminGrid\Controller\Adminhtml\Employee;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
	protected $resultPageFactory;

	public function __construct(Context $context, PageFactory $resultPageFactory)
	{
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
	}

	public function execute()
	{
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Advik_BlockAdminGrid::employee');
		$resultPage->getConfig()->getTitle()->prepend(__('Employee Grid'));
		return $resultPage;
	}
}


