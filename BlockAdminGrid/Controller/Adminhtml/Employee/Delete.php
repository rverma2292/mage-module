<?php

namespace Advik\BlockAdminGrid\Controller\Adminhtml\Employee;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Advik\BlockAdminGrid\Model\EmployeeFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Delete extends Action
{
	protected EmployeeFactory $employeeFactory;
	protected Filesystem $filesystem;

	/**
	 * @param Context $context
	 * @param EmployeeFactory $employeeFactory
	 */
	public function __construct(
		Context $context,
		EmployeeFactory $employeeFactory,
		Filesystem $filesystem,
	)
	{
		$this->employeeFactory = $employeeFactory;
		$this->filesystem = $filesystem;
		parent::__construct($context);
	}

	public function execute()
	{
		$id = $this->getRequest()->getParam('id');

		$errors = [];
		$success = [];

		if (!$id) {
			$errors[] = __('You must select an item to delete.');
		}

		$model = $this->employeeFactory->create()->load($id);

		if (!$model->getId()) {
			$errors[] = __('Record not found.');
		}

		if (!empty($errors)) {
			$this->messageManager->addErrorMessage(implode(' ', $errors));
			return $this->_redirect('*/*/');
		}

		// Capture existing file names before delete
		$existing_files = [
			$model->getData('profile_photo'),
			$model->getData('resume_file')
		];

		try {
			// Delete DB record
			$model->delete();
			$success[] = __('Record %1 deleted successfully.', $id);

			// Delete uploaded files
			$mediaDirectory = $this->filesystem
				->getDirectoryWrite(DirectoryList::MEDIA)
				->getAbsolutePath('employee/');

			foreach ($existing_files as $file) {
				if ($file && file_exists($mediaDirectory . $file)) {
					unlink($mediaDirectory . $file);
					$success[] = __('File "%1" deleted.', $file);
				}
			}

			// Show messages
			$this->messageManager->addSuccessMessage(implode(' ', $success));
			return $this->_redirect('*/*/');

		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			return $this->_redirect('*/*/');
		}
	}

}