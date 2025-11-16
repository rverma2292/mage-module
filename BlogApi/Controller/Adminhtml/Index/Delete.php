<?php

namespace Advik\BlogApi\Controller\Adminhtml\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Advik\BlogApi\Model\BlogFactory;

class Delete extends \Magento\Backend\App\Action
{
	protected PageFactory $resultPageFactory;
	protected BlogFactory $blogFactory;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 * @param BlogFactory $blogFactory
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		BlogFactory $blogFactory
	)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->blogFactory = $blogFactory;
		parent::__construct($context);
	}

	public function execute()
	{
		// check if we know what should be deleted
		$id = $this->getRequest()->getParam('id');
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();

		if ($id) {
			$title = "";
			try {
				// init model and delete
				$model = $this->blogFactory->create();
				$model->load($id);

				$title = $model->getTitle();
				$model->delete();

				// display success message
				$this->messageManager->addSuccessMessage(__('The blog has been deleted.'));

				// go to grid
				$this->_eventManager->dispatch('adminhtml_blog_on_delete', [
					'title' => $title,
					'status' => 'success'
				]);

				return $resultRedirect->setPath('*/*/');
			} catch (\Exception $e) {
				$this->_eventManager->dispatch(
					'adminhtml_blog_on_delete',
					['title' => $title, 'status' => 'fail']
				);
				// display error message
				$this->messageManager->addErrorMessage($e->getMessage());
				// go back to edit form
				return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
			}
		}

		// display error message
		$this->messageManager->addErrorMessage(__('We can\'t find a page to delete.'));

		// go to grid
		return $resultRedirect->setPath('*/*/');
	}
}
