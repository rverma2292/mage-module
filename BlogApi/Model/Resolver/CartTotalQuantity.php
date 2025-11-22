<?php
namespace Advik\BlogApi\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;

class CartTotalQuantity implements ResolverInterface
{
	public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
	{
		// Example: only count items with quantity > 1
		$total = 0;
		foreach ($value['items'] as $item) {


			die('fff');
			if ($item['quantity'] > 1) {
				$total += $item['quantity'];
			}
		}
		return $total;
	}
}