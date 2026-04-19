<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class CreateDeliveryTimeAttribute implements DataPatchInterface
{

	protected EavSetupFactory $eavSetupFactory;
	protected ModuleDataSetupInterface $moduleDataSetup;

	public function __construct(
		EavSetupFactory $eavSetupFactory,
		ModuleDataSetupInterface $moduleDataSetup
	)
	{
		$this->eavSetupFactory = $eavSetupFactory;
		$this->moduleDataSetup = $moduleDataSetup;
	}

	public static function getDependencies(): array
	{
		return [];
	}

	public function getAliases(): array
	{
		return [];
	}

	public function apply()
	{
		$eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
		$eavSetup->addAttribute(
			\Magento\Catalog\Model\Product::ENTITY,
			'delivery_time',
			[
				'type' => 'varchar',
				'label' => 'Delivery Time',
				'input' => 'text',
				'required' => false,
				'visible_on_front' => true,
				'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
				'group' => 'General',
			]
		);
	}
}