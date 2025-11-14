<?php
namespace Advik\BlockAdminGrid\Model\ResourceModel;

class Employee extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
	 * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
	 */
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	){
		parent::__construct($context);
	}

	/**
	 * @return void
	 */
	protected function _construct(){
		$this->_init('employee', 'employee_id');
	}
}