<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddCustomerMobileAttribute implements DataPatchInterface
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

		$attributeCode = 'mobile_number';

		// 1. Attribute creation
		$customerSetup->addAttribute(Customer::ENTITY, $attributeCode, [
			'type' => 'varchar',
			'label' => 'Mobile Number',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'system' => 0,
			'position' => 100,
			'is_used_in_grid' => true,
			'is_visible_in_grid' => true,
			'is_filterable_in_grid' => true,
			'is_searchable_in_grid' => true,
		]);

		$customerSetup->addAttributeToSet(
			Customer::ENTITY,
			CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
			'Account Information', // Yeh exact group name hai Admin mein
			$attributeCode,
			100 // Sort order
		);

		// 2. Register in form
		$attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $attributeCode);
		$attribute->setData('used_in_forms', [
			'adminhtml_customer',    // Admin Panel ke liye
			'customer_account_create', // Registration page ke liye
			'customer_account_edit'    // Account update page ke liye
		]);
		$attribute->save();
	}

	public static function getDependencies() { return []; }
	public function getAliases() { return []; }
}