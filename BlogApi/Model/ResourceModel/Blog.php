<?php
namespace Advik\BlogApi\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Blog extends AbstractDb
{
	public function __construct(Context $context, $connectionName = null)
	{
		parent::__construct($context, $connectionName);
	}

	public function _construct(){
		$this->_init('custom_blog', 'post_id');
	}
}