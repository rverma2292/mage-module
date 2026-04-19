<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\ResourceModel\Product\Action as ProductAction;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class UpdateDeliveryValue implements DataPatchInterface
{
	protected ProductAction $productAction;
	protected CollectionFactory $productCollectionFactory;

	public function __construct(
		ProductAction $productAction,
		CollectionFactory $productCollectionFactory,
	)
	{
		$this->productAction = $productAction;
		$this->productCollectionFactory = $productCollectionFactory;
	}

	public function apply(){
		$productIds = $this->productCollectionFactory->create()->getAllIds();
		$this->productAction->updateAttributes($productIds, ['delivery_time' => '3-5 Working Days'], 0);
	}

	public function getAliases(): array
	{
		return [];
	}

	public static function getDependencies(): array
	{
		// This patch needs to be run first
		return [
			\Advik\LearnPatch\Setup\Patch\Data\CreateDeliveryTimeAttribute::class
		];
	}
}