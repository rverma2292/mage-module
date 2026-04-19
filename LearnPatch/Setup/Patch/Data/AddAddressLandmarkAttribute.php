<?php

namespace Advik\LearnPatch\Setup\Patch\Data;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddAddressLandmarkAttribute implements DataPatchInterface
{
	/** @var ModuleDataSetupInterface */
	private $moduleDataSetup;

	/** @var CustomerSetupFactory */
	private $customerSetupFactory;

	/**
	 * @param ModuleDataSetupInterface $moduleDataSetup
	 * @param CustomerSetupFactory $customerSetupFactory
	 */
	public function __construct(
		ModuleDataSetupInterface $moduleDataSetup,
		CustomerSetupFactory $customerSetupFactory
	) {
		$this->moduleDataSetup = $moduleDataSetup;
		$this->customerSetupFactory = $customerSetupFactory;
	}

	/**
	 * @inheritdoc
	 */
	public function apply()
	{
		/** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
		$customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

		$attributeCode = 'address_landmark';

		/**
		 * 1. Create Attribute for 'customer_address' entity
		 */
		$customerSetup->addAttribute('customer_address', $attributeCode, [
			'type' => 'varchar',
			'label' => 'Landmark',
			'input' => 'text',
			'required' => false,
			'visible' => true,
			'user_defined' => true,
			'system' => 0,
			'group' => 'General',
			'global' => true,
			'visible_on_front' => true,
		]);

        // 2. Explicitly Assign to Attribute Set and Group (Ensures no manual SQL needed)
        $customerSetup->addAttributeToSet(
            'customer_address',
            AddressMetadataInterface::ATTRIBUTE_SET_ID_ADDRESS,
            'General',
            $attributeCode,
            100
        );

        // 3. Register in Forms
		$attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', $attributeCode);
		$attribute->setData('used_in_forms', [
			'adminhtml_customer_address', // Admin Panel -> Customer -> Addresses Tab
			'customer_address_edit',      // Frontend -> My Account -> Address Book
			'customer_register_address'   // Frontend -> Registration Page (with Address)
		]);

		$attribute->save();

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