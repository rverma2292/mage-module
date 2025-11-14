<?php

namespace Advik\BlockAdminGrid\Block\Adminhtml\Employee\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;

class Skills extends AbstractRenderer
{
	public function render(\Magento\Framework\DataObject $row)
	{
		$value = $row->getData($this->getColumn()->getIndex());

		if (!$value) { // check for null or empty
			return '';
		}

		// explode only if it's a string
		if (is_string($value)) {
			return implode(', ', explode(',', $value));
		}

		return (string)$value;
	}
}
