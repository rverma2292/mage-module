<?php
namespace Advik\BlogApi\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Advik\BlogApi\Model\ResourceModel\Blog\CollectionFactory;

class BlogPosts implements ResolverInterface
{
	protected $collectionFactory;

	public function __construct(
		CollectionFactory $collectionFactory
	) {
		$this->collectionFactory = $collectionFactory;
	}

	public function resolve(
		$field,
		$context,
		ResolveInfo $info,
		array $value = null,
		array $args = null
	) {
		$pageSize = $args['pageSize'] ?? 10;
		$currentPage = $args['currentPage'] ?? 1;

		$collection = $this->collectionFactory->create();
		$collection->setPageSize($pageSize);
		$collection->setCurPage($currentPage);

		$items = [];
		foreach ($collection as $post) {
			$items[] = [
				'post_id' => (int) $post->getPostId(),
				'title' => $post->getTitle(),
				'content' => $post->getContent(),
				'identifier' => $post->getIdentifier()
			];
		}

		return [
			'items' => $items,
			'total_count' => $collection->getSize()
		];
	}
}
