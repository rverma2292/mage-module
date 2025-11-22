<?php
namespace Advik\BlogApi\Plugin;

use Magento\QuoteGraphQl\Model\Resolver\CartTotalQuantity;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CartTotalQuantityPlugin
{
	public function afterResolve(
		CartTotalQuantity $subject,
		                  $result,
		Field $field,
		                  $context,
		ResolveInfo $info,
		array $value = null,
		array $args = null
	) {
		// Safe check
		if (!isset($value['items']) || !is_array($value['items'])) {
			return $result;
		}
die('LMK');
		// Example: only count items with quantity > 1
		$total = 0;
		foreach ($value['items'] as $item) {
			if (!empty($item['quantity']) && $item['quantity'] > 1) {
				$total += $item['quantity'];
			}
		}
		return $total;
	}
}
