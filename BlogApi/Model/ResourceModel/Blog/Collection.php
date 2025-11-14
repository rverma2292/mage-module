<?php
namespace Advik\BlogApi\Model\ResourceModel\Blog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Advik\BlogApi\Model\ResourceModel\Blog as BlogResource;
use Advik\BlogApi\Model\Blog;

class Collection extends AbstractCollection
{
	protected $_idFieldName = 'blog_id';
	protected $_eventPrefix = 'blog_collection';
	protected $_eventObject = 'blog_collection';

	protected function _construct(){
		$this->_init(Blog::class, BlogResource::class);
	}
}