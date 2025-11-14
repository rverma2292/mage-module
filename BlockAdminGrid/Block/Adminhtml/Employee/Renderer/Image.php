<?php
namespace Advik\BlockAdminGrid\Block\Adminhtml\Employee\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\App\Filesystem\DirectoryList;

class Image extends AbstractRenderer
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

	public function render(DataObject $row)
	{
		$fileName = $row->getData($this->getColumn()->getIndex());

		// Full path in media folder
		$filePath = 'employee/' . $fileName;
		$mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		// If file does not exist or value is empty, use default
		if (!$fileName || !$this->mediaDirectory->isExist($filePath)) {
			$filePath = 'employee/dummy.png'; //default image given
		}

		$url = $mediaUrl . $filePath;

		return '<img src="' . $url . '" width="70" height="70" />';
	}
}
