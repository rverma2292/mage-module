<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Product;
use Magento\Swatches\Model\Swatch;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory as OptionCollectionFactory;

class CreateCompleteVisualSwatch implements DataPatchInterface
{
	private $moduleDataSetup;
	private $eavSetupFactory;
	private $optionCollectionFactory;

	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup,
		EavSetupFactory $eavSetupFactory,
		OptionCollectionFactory $optionCollectionFactory
	) {
		$this->moduleDataSetup = $moduleDataSetup;
		$this->eavSetupFactory = $eavSetupFactory;
		$this->optionCollectionFactory = $optionCollectionFactory;
	}

	public function apply()
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

		// 1. Attribute Create Karna
		$eavSetup->addAttribute(Product::ENTITY, 'advik_color_swatch', [
			'type' => 'int',
			'label' => 'Advik Color Swatch',
			'input' => 'select',
			'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
			'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
			'visible' => true,
			'user_defined' => true,
			'visible_on_front' => true,
			'used_in_product_listing' => true,
			'group' => 'General',
			'option' => [
				'values' => ['Lal Color', 'Neela Color']
			]
		]);

		$attributeId = $eavSetup->getAttributeId(Product::ENTITY, 'advik_color_swatch');

		// 2. CORRECT TABLE: catalog_eav_attribute mein additional_data update karna
		$additionalData = json_encode(['swatch_input_type' => 'visual']);
		$this->moduleDataSetup->getConnection()->update(
			$this->moduleDataSetup->getTable('catalog_eav_attribute'),
			['additional_data' => $additionalData], // JSON encoded data
			['attribute_id = ?' => $attributeId]
		);

		// 3. Colors Map Karna (Lal aur Neela)
		$colorMap = [
			'Lal Color' => '#FF0000',
			'Neela Color' => '#0000FF'
		];

		foreach ($colorMap as $label => $hexCode) {
			$optionId = $this->getOptionIdByLabel($attributeId, $label);
			if ($optionId) {
				// Delete existing swatch to avoid duplicate error if re-running
				$this->moduleDataSetup->getConnection()->delete(
					$this->moduleDataSetup->getTable('eav_attribute_option_swatch'),
					['option_id = ?' => $optionId]
				);

				$this->moduleDataSetup->getConnection()->insert(
					$this->moduleDataSetup->getTable('eav_attribute_option_swatch'),
					[
						'option_id' => $optionId,
						'store_id' => 0,
						'type' => Swatch::SWATCH_TYPE_VISUAL_COLOR,
						'value' => $hexCode
					]
				);
			}
		}
	}

	private function getOptionIdByLabel($attributeId, $label)
	{
		$collection = $this->optionCollectionFactory->create()
			->setAttributeFilter($attributeId)
			->setStoreFilter(0);

		foreach ($collection as $option) {
			// Magento collection items usually store label in 'value'
			if ($option->getValue() == $label) {
				return $option->getId();
			}
		}
		return null;
	}

	public static function getDependencies() { return []; }
	public function getAliases() { return []; }
}