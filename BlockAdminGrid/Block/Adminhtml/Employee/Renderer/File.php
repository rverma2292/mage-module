<?php

namespace Advik\BlockAdminGrid\Block\Adminhtml\Employee\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\App\Filesystem\DirectoryList;

class File extends AbstractRenderer
{
	protected $mediaDirectory;
	protected $storeManager;

	public function __construct(
		\Magento\Backend\Block\Context $context,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		array $data = []
	) {
		$this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
		$this->storeManager = $storeManager;
		parent::__construct($context, $data);
	}

	public function render(\Magento\Framework\DataObject $row)
	{
		$file = $row->getData($this->getColumn()->getIndex());
		$filePath = 'employee/' . $file;
		$mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		if (!$file || !$this->mediaDirectory->isExist($filePath)) {
			$filePath = 'employee/dummy.png'; //default image given
		}
		$url = $mediaUrl . $filePath;
		if ($file) {
			return '<a href="' . $url . '" target="_blank">Download</a>';
		}
		return '';
	}
}