<?php

namespace Advik\BlockAdminGrid\Model;

class Employee extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'block_employee';

	protected $_cacheTag = 'block_employee';

	protected $_eventPrefix = 'block_employee';

	protected function _construct()
	{
		$this->_init('Advik\BlockAdminGrid\Model\ResourceModel\Employee');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}
