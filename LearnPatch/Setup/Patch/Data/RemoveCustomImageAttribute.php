<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class RemoveCustomImageAttribute implements DataPatchInterface
{
	private $moduleDataSetup;
	private $eavSetupFactory;

	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup,
		EavSetupFactory $eavSetupFactory
	) {
		$this->moduleDataSetup = $moduleDataSetup;
		$this->eavSetupFactory = $eavSetupFactory;
	}

	public function apply()
	{
		/** @var \Magento\Eav\Setup\EavSetup $eavSetup */
		$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

		$attributeCode = 'custom_image';

		// Check karte hain ki attribute exist karta hai ya nahi, phir remove karte hain
		if ($eavSetup->getAttributeId(Product::ENTITY, $attributeCode)) {
			$eavSetup->removeAttribute(Product::ENTITY, $attributeCode);
		}
	}

	public static function getDependencies() { return []; }
	public function getAliases() { return []; }
}