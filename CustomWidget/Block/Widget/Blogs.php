<?php

namespace Advik\CustomWidget\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Advik\BlogApi\Model\ResourceModel\Blog\CollectionFactory;

class Blogs extends \Magento\Framework\View\Element\Template implements BlockInterface
{
	/**
	 * @var CollectionFactory
	 */
	protected $blogCollectionFactory;

	protected $_template = 'widget/blogs.phtml';

	/**
	 * @param Template\Context $context
	 * @param CollectionFactory $blogCollectionFactory
	 * @param array $data
	 */
	public function __construct(
		Template\Context $context,
		CollectionFactory $blogCollectionFactory,
		array $data = []
	)
	{
		$this->blogCollectionFactory = $blogCollectionFactory;
		parent::__construct($context, $data);
	}

	public function getBlogs()
	{
		$ids = $this->getData('blog_ids');

		if (!$ids) {
			return [];
		}

		$ids = explode(',', $ids); // convert CSV to array

		$collection = $this->blogCollectionFactory->create();
		$collection->addFieldToFilter('post_id', ['in' => $ids]);

		return $collection;
	}
}
