<?php

namespace Advik\EmpGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Cms\Api\Data\PageInterface;
use Advik\EmpGrid\Model\EmployeeFactory;
use Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
	protected PostDataProcessor $dataProcessor;
	protected EmployeeFactory $employeeFactory;
	protected JsonFactory $jsonFactory;

	public function __construct(
		Context $context,
		PostDataProcessor $dataProcessor,
		EmployeeFactory $employeeFactory,
		JsonFactory $jsonFactory
	)
	{
		$this->dataProcessor = $dataProcessor;
		$this->employeeFactory = $employeeFactory;
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

		foreach (array_keys($postItems) as $empId) {
			/** @var \Advik\EmpGrid\Model\Employee $employee */
			$model = $this->employeeFactory->create();
			$employee = $model->load($empId);

			try {
				$extendedPageData = $employee->getData();
				//$model->setData(array_merge($employee->getData(), $postItems[$empId])); //Either choose this or
				$model->addData($postItems[$empId]);
				if ($model->save()){
					$messages[] = __('Employee ID %1 was successfully updated.', $empId);
				}
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$messages[] = '[ID: ' . $empId . '] ' . $e->getMessage();
				$error = true;
			} catch (\RuntimeException $e) {
				$messages[] = '[ID: ' . $empId . '] ' . $e->getMessage();
				$error = true;
			} catch (\Exception $e) {
				$messages[] = '[ID: ' . $empId . '] Something went wrong while saving.';
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
