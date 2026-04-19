<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class CreateAllProductAttributes implements DataPatchInterface
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
		/** @var EavSetup $eavSetup */
		$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
		$groupName = 'Advik Custom Attributes';

		// 1. TEXT FIELD (Standard Varchar)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_text', [
			'type' => 'varchar', 'label' => 'Simple Text Field', 'input' => 'text',
			'required' => false, 'global' => ScopedAttributeInterface::SCOPE_STORE,
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 2. TEXT AREA (Large Text)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_textarea', [
			'type' => 'text', 'label' => 'Detailed Text Area', 'input' => 'textarea',
			'wysiwyg_enabled' => true, 'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 3. DROPDOWN (Select)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_dropdown', [
			'type' => 'int', 'label' => 'Dropdown Selection', 'input' => 'select',
			'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
			'option' => ['values' => ['Option 1', 'Option 2', 'Option 3']],
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 4. MULTISELECT (Multiple Options)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_multiselect', [
			'type' => 'varchar', 'label' => 'Multiple Selection', 'input' => 'multiselect',
			'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
			'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class, // Zaroori hai array ke liye
			'option' => ['values' => ['Choice A', 'Choice B', 'Choice C']],
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 5. YES/NO (Boolean)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_boolean', [
			'type' => 'int', 'label' => 'Yes or No Switch', 'input' => 'boolean',
			'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 6. DATE PICKER (Datetime)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_date', [
			'type' => 'datetime', 'label' => 'Special Date Picker', 'input' => 'date',
			'backend' => \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class,
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 7. PRICE (Decimal)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_price', [
			'type' => 'decimal', 'label' => 'Extra Fee (Price)', 'input' => 'price',
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 8. WEIGHT (Decimal)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_weight', [
			'type' => 'decimal', 'label' => 'Custom Weight Unit', 'input' => 'weight',
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 9. MEDIA IMAGE (Image Upload)
		$eavSetup->addAttribute(Product::ENTITY, 'custom_image', [
			'type' => 'varchar', 'label' => 'Additional Image', 'input' => 'media_image',
			'frontend' => \Magento\Catalog\Model\Product\Attribute\Frontend\Image::class,
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);

		// 10. VISUAL SWATCH (Colors/Patterns)
		// Note: Swatches ke liye options ko specific format mein dena hota hai
		$eavSetup->addAttribute(Product::ENTITY, 'custom_swatch', [
			'type' => 'int', 'label' => 'Visual Color Swatch', 'input' => 'select',
			'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
			'update_product_preview_image' => true,
			'visible' => true, 'user_defined' => true, 'group' => $groupName,
		]);
	}

	public static function getDependencies() { return []; }
	public function getAliases() { return []; }
}