<?php
namespace Advik\EmpGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Redirect;

class Delete extends Action
{
	/**
	 * Authorization level of a basic admin session
	 */
	//const ADMIN_RESOURCE = 'Advik_EmpGrid::employee_delete';

	/**
	 * @var \Advik\EmpGrid\Model\EmployeeFactory
	 */
	protected $employeeFactory;

	/**
	 * Constructor
	 *
	 * @param Action\Context $context
	 * @param \Advik\EmpGrid\Model\EmployeeFactory $employeeFactory
	 */
	public function __construct(
		Action\Context $context,
		\Advik\EmpGrid\Model\EmployeeFactory $employeeFactory
	) {
		$this->employeeFactory = $employeeFactory;
		parent::__construct($context);
	}

	/**
	 * Execute method for deleting employee record
	 *
	 * @return Redirect
	 */
	public function execute()
	{
		/** @var Redirect $resultRedirect */
		$resultRedirect = $this->resultRedirectFactory->create();
		$id = $this->getRequest()->getParam('employee_id');

		if ($id) {
			try {
				$employee = $this->employeeFactory->create()->load($id);
				if (!$employee->getId()) {
					throw new \Exception(__('This employee no longer exists.'));
				}
				$employee->delete();
				$this->messageManager->addSuccessMessage(__('Employee deleted successfully.'));
				return $resultRedirect->setPath('*/*/');
			} catch (\Exception $e) {
				$this->messageManager->addErrorMessage(__('Error deleting record: %1', $e->getMessage()));
				return $resultRedirect->setPath('*/*/edit', ['employee_id' => $id]);
			}
		}

		$this->messageManager->addErrorMessage(__('Cannot find a record to delete.'));
		return $resultRedirect->setPath('*/*/');
	}
}
