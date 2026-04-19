<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class FixImageVisibilityPatch implements DataPatchInterface
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
		$eavSetup->updateAttribute(Product::ENTITY, $attributeCode, 'is_required', 0);
		$eavSetup->updateAttribute(Product::ENTITY, $attributeCode, 'is_visible', 1);
		$eavSetup->updateAttribute(Product::ENTITY, $attributeCode, 'is_user_defined', 1);
		$eavSetup->updateAttribute(
			Product::ENTITY,
			$attributeCode,
			'is_global',
			\Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL
		);
		$attributeSetId = $eavSetup->getDefaultAttributeSetId(Product::ENTITY);
		$eavSetup->addAttributeToGroup(
			Product::ENTITY,
			$attributeSetId,
			'Images',
			$attributeCode,
			99
		);
	}

	public static function getDependencies() { return []; }
	public function getAliases() { return []; }
}