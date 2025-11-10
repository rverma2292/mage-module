<?php

namespace Advik\EmpGrid\Model;

use Advik\EmpGrid\Model\ResourceModel\Employee\CollectionFactory;
use Magento\Framework\UrlInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
	protected $loadedData;
	protected $urlBuilder;

	/**
	 * @param string $name
	 * @param string $primaryFieldName
	 * @param string $requestFieldName
	 * @param CollectionFactory $employeeCollectionFactory
	 * @param array $meta
	 * @param array $data
	 */
	public function __construct(
		$name,
		$primaryFieldName,
		$requestFieldName,
		CollectionFactory $employeeCollectionFactory,
		UrlInterface $urlBuilder,
		array $meta = [],
		array $data = []
	) {
		$this->collection = $employeeCollectionFactory->create();
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
		if (isset($this->loadedData)) {
			return $this->loadedData;
		}

		$items = $this->collection->getItems();
		foreach ($items as $employee) {
			$data = $employee->getData();
			// Format profile_photo for file uploader
			if (!empty($data['profile_photo'])) {
				$data['profile_photo'] = [
					[
						'name' => $data['profile_photo'], // file name
						'url'  => $this->getMediaUrl('employee/' . $data['profile_photo']),
						'size' => file_exists(BP . '/pub/media/employee/' . $data['profile_photo'])
							? filesize(BP . '/pub/media/employee/' . $data['profile_photo'])
							: 0,
						'type' => mime_content_type(BP . '/pub/media/employee/' . $data['profile_photo']),
						'id'   => base64_encode($data['profile_photo'])
					]
				];
			}

			// Format resume_file for file uploader
			if (!empty($data['resume_file'])) {
				$data['resume_file'] = [
					[
						'name' => $data['resume_file'],
						'url'  => $this->getMediaUrl('employee/' . $data['resume_file']),
						'size' => file_exists(BP . '/pub/media/employee/' . $data['resume_file'])
							? filesize(BP . '/pub/media/employee/' . $data['resume_file'])
							: 0,
						'type' => mime_content_type(BP . '/pub/media/employee/' . $data['resume_file']),
						'id'   => base64_encode($data['resume_file'])
					]
				];
			}

			$this->loadedData[$employee->getId()] = $data;
		}

		return $this->loadedData;
	}

	/**
	 * Get full media URL
	 */
	private function getMediaUrl($file)
	{
		return $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $file;
	}
}