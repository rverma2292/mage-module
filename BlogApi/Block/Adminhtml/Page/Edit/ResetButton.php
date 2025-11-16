<?php

namespace Advik\BlogApi\Block\Adminhtml\Page\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class BackButton
 */
class ResetButton extends GenericButton implements ButtonProviderInterface
{
	/**
	 * @return array
	 */
	public function getButtonData()
	{
		return [
			'label' => __('Reset'),
			'on_click' => sprintf("location.href = '%s';", $this->getResetUrl()),
			'class' => 'reset',
			'data_attribute' => [
				'mage-init' => null, // keep if you use buttonAdapter
				'url' => $this->getResetUrl()  // <--- will render data-url="..."
			],
			'sort_order' => 15
		];
	}

	public function getResetUrl()
	{
		if ($this->getPostId()) {
			return $this->getUrl('*/*/edit', ['id' => $this->getPostId()]);
		}
		return $this->getUrl('/*/edit');
	}

}
