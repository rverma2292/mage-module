<?php


namespace Advik\UiFormWithAllTypes\Model;

use Advik\BlogApi\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Framework\UrlInterface;

class FormDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
	protected $loadedData;
	protected $urlBuilder;

	/**
	 * @param string $name
	 * @param string $primaryFieldName
	 * @param string $requestFieldName
	 * @param CollectionFactory $blogCollectionFactory
	 * @param UrlInterface $urlBuilder
	 * @param array $meta
	 * @param array $data
	 */
	public function __construct(
		$name,
		$primaryFieldName,
		$requestFieldName,
		CollectionFactory $blogCollectionFactory,
		UrlInterface $urlBuilder,
		array $meta = [],
		array $data = []
	) {
		$this->collection = $blogCollectionFactory->create();
		$this->urlBuilder = $urlBuilder;
		parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
	}

	/**
	 * Get data
	 *
	 * @return array
	 */
	public function getData()
	{
		return [];
	}

	/**
	 * Get full media URL
	 */
	private function getMediaUrl($file)
	{
		return $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $file;
	}
}