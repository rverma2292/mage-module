<?php

namespace Advik\EmpGrid\Block\Adminhtml\Page\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Advik\EmpGrid\Model\EmployeeFactory;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

	/**
	 * @var EmployeeFactory
	 */
	protected EmployeeFactory $employeeFactory;

	/**
	 * @param Context $context
	 * @param EmployeeFactory $employeeFactory
	 */
    public function __construct(
        Context $context,
        EmployeeFactory $employeeFactory
    ) {
        $this->context = $context;
        $this->employeeFactory = $employeeFactory;
    }

    /**
     * Return CMS page ID
     *
     * @return int|null
     */
    public function getEmployeeId()
    {
	    $empId = $this->context->getRequest()->getParam('employee_id');
	    if (!$empId) {
		    return null;
	    }

	    $employee = $this->employeeFactory->create()->load($empId);
	    if ($employee->getEmployeeId()) {
		    return $employee->getEmployeeId(); // or ->getId() if that's the PK
	    }

	    return null;
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
