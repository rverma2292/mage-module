<?php
namespace Advik\BlockAdminGrid\Model\ResourceModel\Employee;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'employee_id';
	protected $_eventPrefix = 'block_employee_collection';
	protected $_eventObject = 'block_employee_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Advik\BlockAdminGrid\Model\Employee', 'Advik\BlockAdminGrid\Model\ResourceModel\Employee');
	}

}

