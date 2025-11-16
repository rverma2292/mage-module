<?php

namespace Advik\BlogApi\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Advik\BlogApi\Model\Blog;
use Advik\BlogApi\Model\BlogFactory;
use Advik\BlogApi\Api\BlogRepositoryInterface;
use Advik\BlogApi\Api\Data\BlogInterface;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\DataPersistorInterface;


class Save extends \Magento\Backend\App\Action
{
	/**
	 * @var PageFactory
	 */
	protected PageFactory $resultPageFactory;

	/**
	 * @var BlogFactory
	 */
	protected BlogFactory $blogFactory;

	/**
	 * @var BlogRepositoryInterface
	 */
	protected BlogRepositoryInterface $blogRepository;

	/**
	 * @var DataPersistorInterface
	 */
	protected DataPersistorInterface $dataPersistor;

	/**
	 * @param Context $context
	 * @param PageFactory $resultPageFactory
	 * @param BlogFactory $blogFactory
	 * @param DataPersistorInterface $dataPersistor
	 * @param BlogRepositoryInterface $blogRepository
	 */
	public function __construct(
		Context $context,
		PageFactory $resultPageFactory,
		BlogFactory $blogFactory,
		DataPersistorInterface $dataPersistor,
		BlogRepositoryInterface $blogRepository
	)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->blogFactory = $blogFactory;
		$this->dataPersistor = $dataPersistor;
		$this->blogRepository = $blogRepository;
		parent::__construct($context);
	}

	/**
	 * @return ResultInterface
	 */
	public function execute(){
		$data = $this->getRequest()->getPostValue();
		/**
		 * @var Redirect $resultRedirect
		 */
		$resultRedirect = $this->resultRedirectFactory->create();
		if ($data) {
			//$data = $this->dataProcessor->filter($data);
			if (isset($data['is_active']) && $data['is_active'] === 'true') {
				$data['is_active'] = Blog::STATUS_ENABLED;
			}
			if (empty($data['post_id'])) {
				$data['post_id'] = null;
			}

			/** @var Blog $model */
			$model = $this->blogFactory->create();

			$id = $this->getRequest()->getParam('post_id');
			if ($id) {
				try {
					$model = $this->blogRepository->getById($id);
				} catch (LocalizedException $e) {
					$this->messageManager->addErrorMessage(__('This blog no longer exists.'));
					return $resultRedirect->setPath('*/*/');
				}
			}

			//will use later for layout
			//$data['layout_update_xml'] = $model->getLayoutUpdateXml();
			//$data['custom_layout_update_xml'] = $model->getCustomLayoutUpdateXml();
			$model->setData($data);

			try {
				$this->_eventManager->dispatch(
					'blog_prepare_save',
					['blog' => $model, 'request' => $this->getRequest()]
				);

				$this->blogRepository->save($model);
				$this->messageManager->addSuccessMessage(__('You saved the blog.'));
				return $this->processResultRedirect($model, $resultRedirect, $data);
			} catch (LocalizedException $e) {
				$this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
			} catch (\Throwable $e) {
				$this->messageManager->addErrorMessage(__('Something went wrong while saving the blog.'));
			}

			$this->dataPersistor->set('blog', $data);
			return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('post_id')]);
		}
		return $resultRedirect->setPath('*/*/');
	}

	/**
	 * Process result redirect
	 *
	 * @param BlogInterface $model
	 * @param Redirect $resultRedirect
	 * @param array $data
	 * @return Redirect
	 */
	private function processResultRedirect($model, $resultRedirect, $data)
	{
		if ($this->getRequest()->getParam('back', false) === 'duplicate') {
			$newPage = $this->blogFactory->create(['data' => $data]);
			$newPage->setPostId(null);
			$identifier = $model->getIdentifier() . '-' . uniqid();
			$newPage->setIdentifier($identifier);
			$newPage->setIsActive(false);
			$this->blogRepository->save($newPage);
			$this->messageManager->addSuccessMessage(__('You duplicated the blog.'));
			return $resultRedirect->setPath(
				'*/*/edit',
				[
					'id' => $newPage->getPostId(),
					'_current' => true,
				]
			);
		}
		$this->dataPersistor->clear('blog');
		if ($this->getRequest()->getParam('back')) {
			return $resultRedirect->setPath('*/*/edit', ['id' => $model->getPostId(), '_current' => true]);
		}
		return $resultRedirect->setPath('*/*/');
	}
}