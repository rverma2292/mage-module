<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class CreateFreshMediaAttribute implements DataPatchInterface
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

		// Naya Attribute Code use kar rahe hain taaki purane cache se conflict na ho
		$attributeCode = 'advik_additional_image';

		$eavSetup->addAttribute(Product::ENTITY, $attributeCode, [
			'type' => 'varchar',
			'label' => 'Advik Additional Image',
			'input' => 'media_image',
			'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
			'required' => false,
			'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
			'visible' => true,
			'user_defined' => true,
			'used_in_product_listing' => true,
			'visible_on_front' => true,
			'group' => 'Advik Custom Attributes',
			'sort_order' => 10,
		]);
	}

	public static function getDependencies() { return []; }
	public function getAliases() { return []; }
}