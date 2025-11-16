<?php
namespace Advik\BlogApi\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Advik\BlogApi\Model\BlogFactory;
use Magento\Framework\Registry;


class Edit extends Action
{
	protected $resultPageFactory;
	protected BlogFactory $blogFactory;
	protected Registry $_coreRegistry;

	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		BlogFactory $blogFactory,
		Registry $registry,
	)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->blogFactory = $blogFactory;
		$this->_coreRegistry = $registry;
		parent::__construct($context);
	}

	public function execute() {
		// 1. Get ID and create model
		$id = $this->getRequest()->getParam('id');
		$model = $this->blogFactory->create();

		// 2. Initial checking
		if ($id) {
			$model->load($id);
			if (!$model->getId()) {
				$this->messageManager->addErrorMessage(__('This blog no longer exists.'));
				/** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
				$resultRedirect = $this->resultRedirectFactory->create();
				return $resultRedirect->setPath('*/*/');
			}
		}

		$this->_coreRegistry->register('blog', $model);

		// 5. Build edit form
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Advik_BlogApi::blog');
			$resultPage->addBreadcrumb(
				$id ? __('Edit Blog') : __('New Blog'),
				$id ? __('Edit Blog') : __('New Blog')
			);
		$resultPage->getConfig()->getTitle()
			->prepend($model->getId() ? $model->getTitle() : __('New Blog'));

		return $resultPage;
	}
}