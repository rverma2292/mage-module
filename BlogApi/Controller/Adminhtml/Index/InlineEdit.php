<?php

namespace Advik\BlogApi\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Advik\BlogApi\Api\Data\BlogInterface;
use Advik\BlogApi\Model\BlogFactory;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Magento\Backend\App\Action implements \Magento\Framework\App\ActionInterface
{
	protected PostDataProcessor $dataProcessor;
	protected JsonFactory $jsonFactory;
	protected BlogFactory $blogFactory;

	public function __construct(
		Context $context,
		PostDataProcessor $dataProcessor,
		BlogFactory $blogFactory,
		JsonFactory $jsonFactory
	)
	{
		$this->dataProcessor = $dataProcessor;
		$this->blogFactory = $blogFactory;
		$this->jsonFactory = $jsonFactory;
		parent::__construct($context);
	}

	/**
	 * Process the request
	 *
	 * @return \Magento\Framework\Controller\ResultInterface
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function execute()
	{
		/** @var \Magento\Framework\Controller\Result\Json $resultJson */
		$resultJson = $this->jsonFactory->create();
		$error = false;
		$messages = [];
		$postItems = $this->getRequest()->getParam('items', []);

		if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
			return $resultJson->setData(
				[
					'messages' => [__('Please correct the data sent.')],
					'error' => true,
				]
			);
		}

		foreach (array_keys($postItems) as $postId) {
			/** @var \Advik\BlogApi\Model\Blog $blog */
			$model = $this->blogFactory->create();
			$blog = $model->load($postId);

			try {
				$extendedPageData = $blog->getData();
				//$model->setData(array_merge($blog->getData(), $postItems[$postId])); //Either choose this or
				$model->addData($postItems[$postId]);
				if ($model->save()){
					$messages[] = __('Blog ID %1 was successfully updated.', $postId);
				}
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$messages[] = '[ID: ' . $postId . '] ' . $e->getMessage();
				$error = true;
			} catch (\RuntimeException $e) {
				$messages[] = '[ID: ' . $postId . '] ' . $e->getMessage();
				$error = true;
			} catch (\Exception $e) {
				$messages[] = '[ID: ' . $postId . '] Something went wrong while saving.';
				$error = true;
			}

		}

		return $resultJson->setData(
			[
				'messages' => $messages,
				'error' => $error
			]
		);
	}

}
