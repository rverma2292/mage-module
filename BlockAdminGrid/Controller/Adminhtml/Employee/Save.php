<?php
namespace Advik\BlockAdminGrid\Controller\Adminhtml\Employee;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Save extends Action
{
	protected $employeeFactory;
	protected $filesystem;
	protected $uploaderFactory;

	public function __construct(
		Action\Context $context,
		\Advik\BlockAdminGrid\Model\EmployeeFactory $employeeFactory,
		Filesystem $filesystem,
		UploaderFactory $uploaderFactory
	) {
		parent::__construct($context);
		$this->employeeFactory = $employeeFactory;
		$this->filesystem = $filesystem;
		$this->uploaderFactory = $uploaderFactory;
	}

	public function execute()
	{
		$data = $this->getRequest()->getPostValue();
		$id = $this->getRequest()->getParam('employee_id');

		if (!$data) {
			$this->_redirect('*/*/');
			return;
		}

		try {
			$model = $this->employeeFactory->create();
			if ($id) {
				$model->load($id);
			}

			/** Handle Profile Photo Upload **/
			$data['profile_photo'] = $this->handleUpload(
				'profile_photo',
				'employee',
				$model->getProfilePhoto()
			);

			/** Handle Resume File Upload **/
			$data['resume_file'] = $this->handleUpload(
				'resume_file',
				'employee',
				$model->getResumeFile()
			);

			if (isset($data['skills'])) {
				$data['skills'] = implode(',', $data['skills']);
			}

			/** Save Data **/
			$model->setData($data);
			$model->save();

			$this->messageManager->addSuccessMessage(__('Employee saved successfully.'));

			if ($this->getRequest()->getParam('back')) {
				$this->_redirect('*/*/edit', ['id' => $model->getId()]);
				return;
			}

			$this->_redirect('*/*/');
		} catch (\Exception $e) {
			$this->messageManager->addErrorMessage($e->getMessage());
			$this->_redirect('*/*/edit', ['id' => $id]);
		}
	}

	/**
	 * Handles file upload, delete, and retain logic
	 */
	protected function handleUpload($fieldName, $subDir, $existingValue)
	{
		$mediaDirectory = $this->filesystem
			->getDirectoryRead(DirectoryList::MEDIA)
			->getAbsolutePath($subDir . '/');
		//echo '<pre>'; print_r($_FILES); echo '</pre>';
		// 1️⃣ If new file uploaded
		if (isset($_FILES[$fieldName]['name']) && $_FILES[$fieldName]['name'] != '') {
			try {
				$uploader = $this->uploaderFactory->create(['fileId' => $fieldName]);
				$uploader->setAllowRenameFiles(false);
				$uploader->setFilesDispersion(false);

				// Allowed extensions
				if ($fieldName === 'profile_photo') {
					$uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'webp']);
				} else {
					$uploader->setAllowedExtensions(['pdf', 'doc', 'docx', 'jpg', 'png']);
				}
				$result = $uploader->save($mediaDirectory);

				if (!empty($result['file'])) {
					if (!empty($existingValue) && $existingValue != $result['file']) {
						// Delete existing value
						if (file_exists($mediaDirectory . $existingValue)) {
							unlink($mediaDirectory . $existingValue);
						}
					}
					return $result['file'];
				}
			} catch (LocalizedException $e) {
				$this->messageManager->addErrorMessage(__('Upload failed for %1: %2', $fieldName, $e->getMessage()));
			}
		}

		// 2️⃣ Handle delete checkbox
		if (isset($_POST[$fieldName]['delete']) && $_POST[$fieldName]['delete'] == 1) {
			return null;
		}

		// 3️⃣ Retain existing file if not replaced
		if (isset($_POST[$fieldName]['value'])) {
			return $_POST[$fieldName]['value'];
		}

		return $existingValue;
	}

	/*protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Advik_BlockAdminGrid::employee_save');
	}*/
}
