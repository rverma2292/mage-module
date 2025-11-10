<?php

namespace Advik\EmpGrid\Model;

class Employee extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'employee';

	protected $_cacheTag = 'employee';

	protected $_eventPrefix = 'employee';

	protected function _construct()
	{
		$this->_init('Advik\EmpGrid\Model\ResourceModel\Employee');
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
