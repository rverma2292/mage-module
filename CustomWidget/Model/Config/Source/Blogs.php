<?php

namespace Advik\CustomWidget\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Blogs implements OptionSourceInterface
{
	protected $blogCollectionFactory;

	public function __construct(
		\Advik\BlogApi\Model\ResourceModel\Blog\CollectionFactory $blogCollectionFactory
	) {
		$this->blogCollectionFactory = $blogCollectionFactory;
	}

	public function toOptionArray()
	{
		$options = [];

		$blogs = $this->blogCollectionFactory->create();
		$blogs->addFieldToSelect(['post_id', 'title']);

		foreach ($blogs as $blog) {
			$options[] = [
				'value' => $blog->getPostId(),
				'label' => $blog->getTitle()
			];
		}

		return $options;
	}
}
