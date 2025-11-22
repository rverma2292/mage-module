<?php

namespace Advik\BlogApi\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Advik\BlogApi\Api\BlogRepositoryInterface;

class UpdateBlogPost implements ResolverInterface
{
	protected $blogFactory;
	protected $resource;

	public function __construct(
		\Advik\BlogApi\Model\BlogFactory $blogFactory,
		\Advik\BlogApi\Model\ResourceModel\Blog $resource,
		BlogRepositoryInterface $blogRepository
	) {
		$this->blogFactory = $blogFactory;
		$this->resource = $resource;
		$this->blogRepository = $blogRepository;
	}

	public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
	{
		if (!isset($args['input']['post_id'])) {
			throw new GraphQlInputException(__('post_id is required.'));
		}

		$input = $args['input'];
		$postId = $input['post_id'];

		$blog = $this->blogFactory->create();
		$this->resource->load($blog, $postId);

		if (!$blog->getId()) {
			throw new GraphQlInputException(__("Blog post with ID $postId not found."));
		}

		if (isset($input['title'])) {
			$blog->setTitle($input['title']);
		}

		if (isset($input['content'])) {
			$blog->setContent($input['content']);
		}

		if (isset($input['identifier'])) {
			$blog->setIdentifier($input['identifier']);
		}

		try {
			$postId = $this->blogRepository->save($blog);
			if ($postId) {
				return [
					'success' => true,
					'message' => 'Blog post updated successfully',
					'post_id' => $postId
				];
			}
		}catch (GraphQlInputException $e){
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
		}
		return [
			'success' => false,
			'message' => 'Something went wrong'
		];
	}
}