<?php

namespace Advik\EmpGrid\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Store\Model\StoreManagerInterface;


class ProfilePhoto extends Column
{
	protected $storeManager;

	public function __construct(
		ContextInterface $context,
		UiComponentFactory $uiComponentFactory,
		StoreManagerInterface $storeManager,
		array $components = [],
		array $data = []
	) {
		$this->storeManager = $storeManager;
		parent::__construct($context, $uiComponentFactory, $components, $data);
	}
	public function prepareDataSource(array $dataSource)
	{
		if (isset($dataSource['data']['items'])) {
			$fieldName = $this->getData('name');
			foreach ($dataSource['data']['items'] as & $item) {
				$fileName = $item[$fieldName] ?? null;
				$mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'employee/';

				if ($fileName) {
					$item[$fieldName . '_src'] = $mediaUrl . $fileName;
					$item[$fieldName . '_orig_src'] = $mediaUrl . $fileName;
					$item[$fieldName . '_alt'] = $item['first_name'] ?? '';
					$item[$fieldName . '_link'] = $item[$fieldName . '_src']; // for lightbox click
				} else {
					$item[$fieldName . '_src'] = $mediaUrl . 'dummy.png';
					$item[$fieldName . '_orig_src'] = $mediaUrl . 'dummy.png';
					$item[$fieldName . '_alt'] = 'No Image';
					$item[$fieldName . '_link'] = $mediaUrl . 'dummy.png';
				}
			}
		}

		return $dataSource;
	}
}
