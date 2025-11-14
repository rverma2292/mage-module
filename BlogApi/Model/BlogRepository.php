<?php
namespace Advik\BlogApi\Model;

use Advik\BlogApi\Model\BlogFactory;
use Advik\BlogApi\Api\Data\BlogInterface;
use Advik\BlogApi\Api\Data\BlogSearchResultsInterfaceFactory;
use Advik\BlogApi\Api\BlogRepositoryInterface;
use Advik\BlogApi\Model\ResourceModel\Blog as ResourceModel;
use Advik\BlogApi\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class BlogRepository implements BlogRepositoryInterface
{
	/**
	 * @var \Advik\BlogApi\Model\BlogFactory
	 */
	protected $blogFactory;

	/**
	 * @var ResourceModel
	 */
	protected $resource;

	/**
	 * @var CollectionFactory
	 */
	protected CollectionFactory $collectionFactory;

	/**
	 * @var BlogSearchResultsInterfaceFactory
	 */
	protected BlogSearchResultsInterfaceFactory $searchResultsFactory;

	/**
	 * @var CollectionProcessorInterface|null
	 */
	protected ?CollectionProcessorInterface $collectionProcessor;


	public function __construct(
		BlogFactory $blogFactory,
		ResourceModel $resource,
		CollectionFactory $collectionFactory,
		BlogSearchResultsInterfaceFactory $blogSearchResultsInterfaceFactory,
		CollectionProcessorInterface $collectionProcessor
	){
		$this->blogFactory = $blogFactory;
		$this->resource = $resource;
		$this->collectionFactory     = $collectionFactory;
		$this->searchResultsFactory  = $blogSearchResultsInterfaceFactory;
		$this->collectionProcessor   = $collectionProcessor;
	}

	public function save(BlogInterface $blog)
	{
		$this->resource->save($blog);
		return $blog->getPostId();
	}

	public function getById($blogId)
	{
		$blog = $this->blogFactory->create();
		$this->resource->load($blog, $blogId);
		if (!$blog->getPostId()) {
			throw new NoSuchEntityException(__('Blog with id "%d" does not exist.', $blogId));
		}
		return $blog;
	}

	public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
	{
		$collection = $this->collectionFactory->create();

		// Apply filters, sort orders, pagination
		$this->collectionProcessor->process($searchCriteria, $collection);

		/** @var \Advik\BlogApi\Api\Data\BlogSearchResultsInterface $searchResults */
		$searchResults = $this->searchResultsFactory->create();

		$searchResults->setSearchCriteria($searchCriteria);
		$searchResults->setItems($collection->getItems());
		$searchResults->setTotalCount($collection->getSize());

		return $searchResults;
	}

	public function delete(BlogInterface $blog)
	{
		$this->resource->delete($blog);
		return true;
	}

	public function deleteById($blogId)
	{
		return $this->delete($this->getById($blogId));
	}
}
