<?php
namespace Advik\BlockAdminGrid\Controller\Adminhtml\Employee;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Exception\LocalizedException;

class Edit extends Action implements HttpGetActionInterface
{
	//const ADMIN_RESOURCE = 'Advik_BlockAdminGrid::employee';

	/**
	 * @var \Magento\Framework\Registry
	 */
	protected $coreRegistry;

	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * @var \Advik\BlockAdminGrid\Model\EmployeeFactory
	 */
	protected $employeeFactory;

	public function __construct(
		Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Registry $coreRegistry,
		\Advik\BlockAdminGrid\Model\EmployeeFactory $employeeFactory
	) {
		parent::__construct($context);
		$this->resultPageFactory = $resultPageFactory;
		$this->coreRegistry = $coreRegistry;
		$this->employeeFactory = $employeeFactory;
	}

	public function execute()
	{
		$id = $this->getRequest()->getParam('id');
		$model = $this->employeeFactory->create();

		if ($id) {
			$model->load($id);
			if (!$model->getEmployeeId()) {
				$this->messageManager->addErrorMessage(__('This employee no longer exists.'));
				$resultRedirect = $this->resultRedirectFactory->create();
				return $resultRedirect->setPath('*/*/');
			}
		}

		// Register model for use in form
		$this->coreRegistry->register('blockadmingrid_employee', $model);

		/** @var \Magento\Framework\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Advik_BlockAdminGrid::employee');
		$resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Employee') : __('New Employee'));

		return $resultPage;
	}
}
