<?php

namespace Advik\BlockAdminGrid\Block\Adminhtml\Employee;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Edit extends Container
{
	public function __construct(
		Context $context,
		array $data = [],
		?SecureHtmlRenderer $secureRenderer = null
	)
	{
		$this->_objectId = 'id'; //primarykey
		$this->_blockGroup = 'Advik_BlockAdminGrid'; //module name
		$this->_controller = 'adminhtml_employee'; //This corresponds to the folder path of your admin controller under:
		parent::__construct($context, $data, $secureRenderer);
		$this->buttonList->update('save', 'label', __('Save Employee'));
		$this->buttonList->update('save', 'class', 'save primary');

		$this->buttonList->add(
			'saveandcontinue',
			[
				'label' => __('Save and Continue Edit'),
				'class' => 'save',
				'data_attribute' => [
					'mage-init' => [
						'button' => [
							'event' => 'saveAndContinueEdit',
							'target' => '#edit_form'
						]
					],
				]
			],
			-100
		);

		// Delete button
		$this->buttonList->update('delete', 'label', __('Delete Employee'));


	}

	public function getHeaderText(){
		return __('Edit Employee');
	}

	protected function _getSaveAndContinueUrl()
	{
		return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit']);
	}

}
