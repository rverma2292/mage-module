<?php

namespace Advik\EmpGrid\Model\Employee\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Option\ArrayInterface;

class Skills implements OptionSourceInterface
{
	/**
	 * Return options as array
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return [
			['value' => 'php', 'label' => __('PHP')],
			['value' => 'javascript', 'label' => __('JavaScript')],
			['value' => 'html', 'label' => __('HTML')],
			['value' => 'css', 'label' => __('CSS')],
			['value' => 'magento', 'label' => __('Magento')],
			['value' => 'laravel', 'label' => __('Laravel')],
		];
	}
}
