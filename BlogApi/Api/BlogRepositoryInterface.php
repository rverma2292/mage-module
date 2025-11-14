<?php
namespace Advik\BlogApi\Api;

use Advik\BlogApi\Api\Data\BlogInterface;
use Advik\BlogApi\Api\Data\BlogSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface BlogRepositoryInterface
{
	/**
	 * @param BlogInterface $blog
	 * @return integer
	 */
	public function save(BlogInterface $blog);

	/**
	 * Get blog post by ID
	 *
	 * @param int $blogId
	 * @return BlogInterface
	 * @throws NoSuchEntityException
	 */
	public function getById($blogId);

	/**
	 * Delete a blog post
	 *
	 * @param BlogInterface $blog
	 * @return bool
	 */
	public function delete(BlogInterface $blog);

	/**
	 * Delete by ID
	 *
	 * @param int $blogId
	 * @return bool
	 */
	public function deleteById($blogId);

	/**
	 * Get list of blogs
	 *
	 * @param SearchCriteriaInterface $searchCriteria
	 * @return BlogSearchResultsInterface
	 */
	public function getList(SearchCriteriaInterface $searchCriteria);
}

