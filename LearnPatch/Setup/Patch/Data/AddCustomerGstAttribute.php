<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

class AddCustomerGstAttribute implements DataPatchInterface
{
	private $moduleDataSetup;
	private $customerSetupFactory;

	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup,
		CustomerSetupFactory $customerSetupFactory
	) {
		$this->moduleDataSetup = $moduleDataSetup;
		$this->customerSetupFactory = $customerSetupFactory;
	}

	public function apply()
	{
		/** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
		$customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

		$attributeCode = 'gst_number';

		// 1. Attribute Creation
		$customerSetup->addAttribute(Customer::ENTITY, $attributeCode, [
			'type' => 'varchar',
			'label' => 'GST Number',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'system' => 0,
			'position' => 110,
			'is_used_in_grid' => true,
			'is_visible_in_grid' => true,
			'is_filterable_in_grid' => true,
			'is_searchable_in_grid' => true,
		]);

		// 2. IMPORTANT: To auto fulfill the 'eav_entity_attribute'
		// To set Account information in Group
		$customerSetup->addAttributeToSet(
			Customer::ENTITY,
			CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
			'Account Information', // Yeh exact group name hai Admin mein
			$attributeCode,
			110 // Sort order
		);

		// 3. To register in form
		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
		$attribute->setData('used_in_forms', [
			'adminhtml_customer',
			'customer_account_create',
			'customer_account_edit'
		]);

		$attribute->save();
	}

	public static function getDependencies() { return []; }
	public function getAliases() { return []; }
}