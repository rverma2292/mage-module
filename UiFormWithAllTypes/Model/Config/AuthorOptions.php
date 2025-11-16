<?php
namespace Advik\UiFormWithAllTypes\Model\Config;

use Magento\Framework\Data\OptionSourceInterface;

class AuthorOptions implements OptionSourceInterface
{
	public function toOptionArray()
	{
		return [
			['value' => 1, 'label' => __('John Doe')],
			['value' => 2, 'label' => __('Jane Smith')],
			['value' => 3, 'label' => __('Alice')],
		];
	}
}
