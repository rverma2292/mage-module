<?php
namespace Advik\EmpGrid\Model\ResourceModel\Employee;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'employee_id';
	protected $_eventPrefix = 'employee_collection';
	protected $_eventObject = 'employee_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Advik\EmpGrid\Model\Employee', 'Advik\EmpGrid\Model\ResourceModel\Employee');
	}

}

