<?php

namespace Advik\BlogApi\Model\Resolver;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ResourceConnection\ConfigInterface as ResourceConfigInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Model\ResourceModel\Type\Db\ConnectionFactoryInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CreateBlog implements ResolverInterface
{

	protected $blogFactory;
	protected $resource;

	public function __construct(
		\Advik\BlogApi\Model\BlogFactory $blogFactory,
		\Advik\BlogApi\Model\ResourceModel\Blog $resource
	)
	{
		$this->blogFactory = $blogFactory;
		$this->resource = $resource;
	}


	public function resolve(
		$field,
		$context,
		ResolveInfo $info,
		array $value = null,
		array $args = null
	) {
		if (empty($args['input'])) {
			throw new GraphQlInputException(__('Input field is required.'));
		}

		$input = $args['input'];

		foreach (['identifier'] as $fieldName) {
			if (empty($input[$fieldName])) {
				throw new GraphQlInputException(__("$fieldName is required."));
			}
		}
		$blog = $this->blogFactory->create();
		$blog->setData([
			'title'      => $input['title'],
			'content'    => $input['content'],
			'identifier' => $input['identifier']
		]);

		// Save data
		try {
			if ($blog->save($blog)) {
				$msg = __('Blog created successfully.');
				return [
					'success' => true,
					'message' => $msg,
					'post_id' => $blog->getId()
				];
			} else {
				throw new GraphQlInputException(__('Unable to create blog.'));
			}
		}catch (\Exception $e) {
			return [
				'success' => false,
				'message' => $e->getMessage()
			];
			//throw new GraphQlInputException(__('Unable to create blog.'));
		}
	}
}