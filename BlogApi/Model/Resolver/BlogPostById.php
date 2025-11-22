<?php
namespace Advik\BlogApi\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Advik\BlogApi\Model\BlogFactory;

class BlogPostById implements ResolverInterface
{
	protected $blogFactory;

	public function __construct(BlogFactory $blogFactory)
	{
		$this->blogFactory = $blogFactory;
	}

	public function resolve(
		$field,
		$context,
		ResolveInfo $info,
		array $value = null,
		array $args = null
	) {
		if (!isset($args['id'])) {
			throw new GraphQlInputException(__('Blog post ID is required'));
		}

		$post = $this->blogFactory->create()->load($args['id']);

		if (!$post->getId()) {
			throw new GraphQlInputException(__('Blog post not found'));
		}

		return [
			'post_id' => $post->getId(),
			'title' => $post->getTitle(),
			'content' => $post->getContent(),
			'identifier' => $post->getIdentifier()
		];
	}
}
