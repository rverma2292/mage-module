<?php

namespace Advik\BlogApi\Model;

use Advik\BlogApi\Api\Data\BlogInterface;
use Advik\BlogApi\Api\BlogRepositoryInterface;
use Advik\BlogApi\Model\ResourceModel\Blog\CollectionFactory;
use Advik\BlogApi\Model\BlogFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
	protected $loadedData;
	protected UrlInterface $urlBuilder;
	protected BlogRepositoryInterface $blogRepository;
	protected BlogFactory $blogFactory;
	protected RequestInterface $request;
	protected DataPersistorInterface $dataPersistor;

	/**
	 * @param string $name
	 * @param string $primaryFieldName
	 * @param string $requestFieldName
	 * @param CollectionFactory $blogCollectionFactory
	 * @param UrlInterface $urlBuilder
	 * @param BlogRepositoryInterface $blogRepository
	 * @param BlogFactory $blogFactory
	 * @param RequestInterface $request
	 * @param DataPersistorInterface $dataPersistor
	 * @param array $meta
	 * @param array $data
	 */
	public function __construct(
		$name,
		$primaryFieldName,
		$requestFieldName,
		CollectionFactory $blogCollectionFactory,
		UrlInterface $urlBuilder,
		BlogRepositoryInterface $blogRepository,
		BlogFactory $blogFactory,
		RequestInterface $request,
		DataPersistorInterface $dataPersistor,
		array $meta = [],
		array $data = []
	) {
		$this->collection = $blogCollectionFactory->create();
		$this->urlBuilder = $urlBuilder;
		$this->blogRepository = $blogRepository;
		$this->blogFactory = $blogFactory;
		$this->request = $request;
		$this->dataPersistor = $dataPersistor;
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
		$blog = $this->getCurrentBlog();
		$this->loadedData[$blog->getPostId()] = $blog->getData();
		/*
		 * Will use later to render in frontend
		if ($blog->getCustomLayoutUpdateXml() || $blog->getLayoutUpdateXml()) {
			//Deprecated layout update exists.
			$this->loadedData[$blog->getId()]['layout_update_selected'] = '_existing_';
		}*/

		return $this->loadedData;

	}

	/**
	 * Get full media URL
	 */
	private function getMediaUrl($file)
	{
		return $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $file;
	}

	private function getPostId(): int
	{
		//return (int) $this->request->getParam($this->getRequestFieldName());
		return (int) $this->request->getParam('id');
	}

	private function getCurrentBlog(): BlogInterface
	{
		$blogId = $this->getPostId();
		if ($blogId) {
			try {
				$blog = $this->blogRepository->getById($blogId);
			} catch (LocalizedException $exception) {
				$blog = $this->blogFactory->create();
			}
			return $blog;
		}

		$data = $this->dataPersistor->get('blog');
		if (empty($data)) {
			return $this->blogFactory->create();
		}
		$this->dataPersistor->clear('blog');

		return $this->blogFactory->create()
			->setData($data);
	}
}