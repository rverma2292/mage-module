<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;

class UpdateAttributeToOptional implements DataPatchInterface
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
		$attributeCode = 'advik_color_swatch';
		$eavSetup->updateAttribute(
			Product::ENTITY,
			$attributeCode,
			'is_required',
			0
		);

		$eavSetup->updateAttribute(
			Product::ENTITY,
			'custom_swatch',
			'is_required',
			0
		);
	}

	public static function getDependencies()
	{
		// Ye patch tabhi chalna chahiye jab purana patch (jisne attribute banaya) chal chuka ho
		return [
			\Advik\LearnPatch\Setup\Patch\Data\CreateCompleteVisualSwatch::class
		];
	}

	public function getAliases()
	{
		return [];
	}
}