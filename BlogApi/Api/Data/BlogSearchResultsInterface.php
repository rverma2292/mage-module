<?php
namespace Advik\BlogApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface BlogSearchResultsInterface extends SearchResultsInterface
{

	/**
	 * Get list of blog items.
	 *
	 * @return \Advik\BlogApi\Api\Data\BlogInterface[]
	 */
	public function getItems();

	/**
	 * Set list of blog items.
	 *
	 * @param \Advik\BlogApi\Api\Data\BlogInterface[] $items
	 * @return $this
	 */
	public function setItems(array $items);

}