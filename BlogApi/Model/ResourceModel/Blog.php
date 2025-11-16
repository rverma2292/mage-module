<?php
namespace Advik\BlogApi\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
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

	/**
	 * @throws LocalizedException
	 */
	public function checkIdentifierExists($identifier, $excludeId = null): bool
	{
		$connection = $this->getConnection();
		$table = $this->getMainTable(); // custom_blog
		$select = $connection->select()->from($table, ['post_id'])
			->where('identifier = ?', $identifier);
		if ($excludeId) {
			$select->where('post_id != ?', $excludeId);
		}
		return (bool)$connection->fetchOne($select);
	}

	/**
	 * Check url_rewrite conflict for request_path = identifier
	 * excludeTargetId => blog/post/view/id/<id> (if updating same post)
	 */
	public function checkUrlRewriteConflict($identifier, $storeId = 0, $excludePostId = null): bool
	{
		$connection = $this->getConnection();
		$table = $this->getTable('url_rewrite');
		$select = $connection->select()->from($table, ['url_rewrite_id'])
			->where('request_path = ?', $identifier)
			->where('store_id = ?', $storeId);

		// If it points to this same blog post, that's ok
		if ($excludePostId) {
			$target = 'blog/post/view/id/' . (int)$excludePostId;
			$select->where('target_path != ?', $target);
		}

		return (bool)$connection->fetchOne($select);
	}

}