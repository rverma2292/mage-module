<?php
namespace Advik\BlockAdminGrid\Block\Adminhtml\Employee;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Context;
use Advik\BlockAdminGrid\Model\ResourceModel\Employee\CollectionFactory;

class Grid extends Extended
{
	protected $collectionFactory;

	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Backend\Helper\Data $backendHelper,
		CollectionFactory $collectionFactory,
		array $data = [])
	{
		$this->collectionFactory = $collectionFactory;
		parent::__construct($context, $backendHelper, $data);
	}

	protected function _construct()
	{
		parent::_construct();
		$this->setId('blockadmingrid_employee_grid');
		$this->setDefaultSort('employee_id');
		$this->setDefaultDir('DESC');
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = $this->collectionFactory->create();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}

	protected function _prepareColumns()
	{
		$this->addColumn('employee_id', [
			'header' => __('ID'),
			'index' => 'employee_id',
			'type' => 'number',
		]);

		$this->addColumn('first_name', [
			'header' => __('First Name'),
			'index' => 'first_name',
			'type' => 'text',
		]);

		$this->addColumn('last_name', [
			'header' => __('Last Name'),
			'index' => 'last_name',
			'type' => 'text',
		]);

		$this->addColumn('phone', [
			'header' => __('Phone'),
			'index' => 'phone',
			'type' => 'text',
		]);

		$this->addColumn('gender', [
			'header' => __('Gender'),
			'index' => 'gender',
			'type' => 'options',
			'options' => [
				'male' => __('Male'),
				'female' => __('Female'),
				'other' => __('Other')
			],
		]);

		$this->addColumn('dob', [
			'header' => __('Date of Birth'),
			'index' => 'dob',
			'type' => 'date',
		]);

		$this->addColumn('skills', [
			'header' => __('Skills'),
			'index' => 'skills',
			'type' => 'text', // multiselect stored as comma-separated string
			'renderer' => \Advik\BlockAdminGrid\Block\Adminhtml\Employee\Renderer\Skills::class
		]);

		$this->addColumn('city', [
			'header' => __('City'),
			'index' => 'city',
			'type' => 'text',
		]);

		$this->addColumn('state', [
			'header' => __('State'),
			'index' => 'state',
			'type' => 'text',
		]);

		$this->addColumn('country', [
			'header' => __('Country'),
			'index' => 'country',
			'type' => 'text',
		]);

		$this->addColumn('joining_date', [
			'header' => __('Joining Date'),
			'index' => 'joining_date',
			'type' => 'date',
		]);

		$this->addColumn('marital_status', [
			'header' => __('Marital Status'),
			'index' => 'marital_status',
			'type' => 'options',
			'options' => [
				'single' => __('Single'),
				'married' => __('Married'),
			],
		]);

		$this->addColumn('profile_photo', [
			'header' => __('Profile Photo'),
			'index' => 'profile_photo',
			'type' => 'text',
			'renderer' => \Advik\BlockAdminGrid\Block\Adminhtml\Employee\Renderer\Image::class
		]);

		$this->addColumn('resume_file', [
			'header' => __('Resume'),
			'index' => 'resume_file',
			'type' => 'text',
			'renderer' => \Advik\BlockAdminGrid\Block\Adminhtml\Employee\Renderer\File::class
		]);

		$this->addColumn('created_at', [
			'header' => __('Created At'),
			'index' => 'created_at',
			'type' => 'datetime',
		]);

		$this->addColumn('updated_at', [
			'header' => __('Updated At'),
			'index' => 'updated_at',
			'type' => 'datetime',
		]);

		$this->addColumn('action', [
			'header' => __('Action'),
			'type' => 'action',
			'getter' => 'getEmployeeId',
			'actions' => [
				[
					'caption' => __('Edit'),
					'url' => ['base' => '*/*/edit'],
					'field' => 'id'
				]
			],
			'filter' => false,
			'sortable' => false
		]);

		return parent::_prepareColumns();
	}
}
