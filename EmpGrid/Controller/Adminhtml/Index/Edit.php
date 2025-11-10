<?php
namespace Advik\EmpGrid\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Advik\EmpGrid\Model\EmployeeFactory;
class Edit extends Action
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;
	protected EmployeeFactory $employeeFactory;

	/**
	 * Constructor
	 */
	public function __construct(
		Action\Context $context,
		PageFactory $resultPageFactory,
		EmployeeFactory $employeeFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->employeeFactory = $employeeFactory;
	}

	/**
	 * Check ACL permissions
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Advik_EmpGrid::employee');
	}

	/**
	 * Execute action
	 */
	public function execute()
	{

		// Get employee ID from request
		$id = $this->getRequest()->getParam('employee_id');

		// Load employee model if ID exists (optional)
		$model = $this->employeeFactory->create();
		if ($id) {
			$model->load($id);
			if (!$model->getId()) {
				$this->messageManager->addErrorMessage(__('This employee no longer exists.'));
				$resultRedirect = $this->resultRedirectFactory->create();
				return $resultRedirect->setPath('*/*/index');
			}
		}

		$resultPage = $this->resultPageFactory->create();



		// Build result page
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Advik_EmpGrid::employee');
		$resultPage->getConfig()->getTitle()->prepend($id ? __('Edit Employee') : __('Add Employee'));


		return $resultPage;
	}
}
