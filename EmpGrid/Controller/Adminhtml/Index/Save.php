<?php
namespace Advik\EmpGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;

class Save extends Action
{
	protected $employeeFactory;

	public function __construct(
		Action\Context $context,
		\Advik\EmpGrid\Model\EmployeeFactory $employeeFactory
	) {
		parent::__construct($context);
		$this->employeeFactory = $employeeFactory;
	}

	public function execute()
	{
		$data = $this->getRequest()->getPostValue();
		//echo '<pre>'; print_r($data); echo '</pre>'; die('Endhere');
		/** @var Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();

		if ($data) {
			try {
				$id = $this->getRequest()->getParam('employee_id');
				$model = $this->employeeFactory->create();

				if ($id) {
					$model->load($id);
				}
				//skills
				if (isset($data['skills']) && is_array($data['skills'])) {
					$data['skills'] = implode(',', $data['skills']);
				}
				//profile_photo
				if (isset($data['profile_photo']) && is_array($data['profile_photo'])) {
					if (isset($data['profile_photo'][0]['name'])) {
						// Delete old file if exists
						$newFileName = $data['profile_photo'][0]['name'];
						$oldFile = $model->getData('profile_photo');
						if ($oldFile && $oldFile !== $newFileName) {
							$filePath = BP . '/pub/media/employee/' . $oldFile;
							if (file_exists($filePath)) {
								unlink($filePath);
							}
						}
						// Save new file name
						$data['profile_photo'] = $data['profile_photo'][0]['name'] ?? null;
					}else {
						$data['profile_photo'] = $model->getData('profile_photo');
					}
				}
				//resume_file
				if (isset($data['resume_file']) && is_array($data['resume_file'])) {
					if (isset($data['resume_file'][0]['name'])) {
						$newFileName = $data['resume_file'][0]['name'] ?? null;
						$oldFile = $model->getData('resume_file');
						if ($oldFile && $oldFile !== $newFileName) {
							$filePath = BP . '/pub/media/employee/' . $oldFile;
							if (file_exists($filePath)) {
								unlink($filePath);
							}
						}
						$data['resume_file'] = $newFileName;
					}else {
						$data['resume_file'] = $model->getData('resume_file');
					}
				}
				try {
					$model->setData($data);
					if ($model->save()){
						$this->messageManager->addSuccessMessage(__('Employee saved successfully.'));
					}else {
						$this->messageManager->addErrorMessage('Something went wrong while saving employee.');
					}

					$back = $this->getRequest()->getParam('back');
					if ($back === 'duplicate') {
						// Create new record with same data
						$duplicate = $this->employeeFactory->create();
						$duplicate->setData($model->getData());
						$email = $model->getData('email');
						if ($email) {
							$email = $this->incrementEmail($email);
							$duplicate->setData('email', $email);
						}
						$duplicate->setEmployeeId(null); // reset primary key
						$duplicate->save();

						$this->messageManager->addSuccessMessage(__('Employee duplicated successfully.'));
						return $resultRedirect->setPath('*/*/edit', ['employee_id' => $duplicate->getId()]);
					}

					// Regular redirects
					if ($back === 'edit') {
						return $resultRedirect->setPath('*/*/edit', ['employee_id' => $model->getId()]);
					}
				}catch (\Exception $e) {
					$this->messageManager->addErrorMessage($e->getMessage());
				}

				if ($this->getRequest()->getParam('back') == 'duplicate') {
					return $resultRedirect->setPath('*/*/edit');
				}
				return $resultRedirect->setPath('*/*/index');
			} catch (\Exception $e) {
				$this->messageManager->addErrorMessage($e->getMessage());
			}
		}
		return $resultRedirect->setPath('*/*/index');
	}

	function incrementEmail($email)
	{
		// Split email into name and domain parts
		[$name, $domain] = explode('@', $email);
		// Check if the name already ends with a number
		if (preg_match('/(.*?)(\d+)$/', $name, $matches)) {
			$baseName = $matches[1];
			$number = (int)$matches[2] + 1;
			$newName = $baseName . $number;
		} else {
			// If no number, start with 1
			$newName = $name . '1';
		}

		return $newName . '@' . $domain;
	}
}
