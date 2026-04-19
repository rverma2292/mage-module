<?php

namespace Advik\CategoryImageAttribute\Setup\Patch\Data;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateMobileBanner implements DataPatchInterface
{
	/**
	 * @var ModuleDataSetupInterface
	 */
	private $moduleDataSetup;

	/**
	 * @var CategorySetupFactory
	 */
	private $categorySetupFactory;

	/**
	 * @param ModuleDataSetupInterface $moduleDataSetup
	 * @param CategorySetupFactory $categorySetupFactory
	 */
	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup,
		CategorySetupFactory $categorySetupFactory
	) {
		$this->moduleDataSetup = $moduleDataSetup;
		$this->categorySetupFactory = $categorySetupFactory;
	}

	/**
	 * @inheritdoc
	 */
	public function apply()
	{
		$categorySetup = $this->categorySetupFactory->create(['setup' => $this->moduleDataSetup]);
		$categorySetup->addAttribute(
			Category::ENTITY,
			'custom_mobile_image',
			[
				'type' => 'varchar',
				'label' => 'Upload Mobile Banner',
				'input' => 'image',
				'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
				'required' => false,
				'sort_order' => 8,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				'group' => 'General Information',
			]
		);

		return $this;
	}
	/**
	 * @inheritdoc
	 */
	public static function getDependencies()
	{
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public function getAliases()
	{
		return [];
	}
}
