<?php

namespace Advik\EmpGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

class Upload extends Action
{
	/**
	 * @var JsonFactory
	 */
	protected $resultJsonFactory;

	/**
	 * @var UploaderFactory
	 */
	protected $uploaderFactory;

	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	public function __construct(
		Action\Context $context,
		JsonFactory $resultJsonFactory,
		UploaderFactory $uploaderFactory,
		Filesystem $filesystem
	) {
		parent::__construct($context);
		$this->resultJsonFactory = $resultJsonFactory;
		$this->uploaderFactory  = $uploaderFactory;
		$this->filesystem       = $filesystem;
	}

	/**
	 * Execute method
	 */
	public function execute()
	{
		/** @var \Magento\Framework\Controller\Result\Json $resultJson */
		$resultJson = $this->resultJsonFactory->create();

		try {
			// Get file field name dynamically (profile_photo or resume_file)
			//$fileId = $this->getRequest()->getParam('profile_photo');
			$files = $this->getRequest()->getFiles()->toArray();

			if (isset($files['profile_photo'])) {
				$fileId = 'profile_photo';
			} elseif (isset($files['resume_file'])) {
				$fileId = 'resume_file';
			} else {
				$fileId = null;
			}

			if (!$fileId) {
				throw new \Exception("File parameter name not found.");
			}

			/** @var \Magento\Framework\Filesystem\Directory\Write $mediaDirectory */
			$mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
			$target = 'employee'; // folder in pub/media/employee
			$employeeDirectory = $mediaDirectory->getAbsolutePath($target);
			if (!is_dir($employeeDirectory)) {
				mkdir($employeeDirectory, 0777, true); // recursive create
				chmod($employeeDirectory, 0777);
			}

			$uploader = $this->uploaderFactory->create(['fileId' => $fileId]);
			$uploader->setAllowedExtensions(['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);
			$uploader->setAllowRenameFiles(true);
			$uploader->setFilesDispersion(false);

			$result = $uploader->save($mediaDirectory->getAbsolutePath($target));

			if (!$result) {
				throw new \Exception(__('File cannot be saved to the destination folder.'));
			}

			$fileUrl = $this->_url->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $target . '/' . $result['file'];

			return $resultJson->setData(
				[
				'success' => true,
				'url' => $fileUrl,
				'name' => $result['file'],
				'size' => $result['size'],   // required numeric value
				'type' => $result['type']    // mime type
				]
			);

		} catch (\Exception $e) {
			return $resultJson->setData([
				'success' => false,
				'error' => $e->getMessage()
			]);
		}
	}
}
