<?php
/**
 * Customer Helper - Comprehensive guide for Magento 2 customer operations
 *
 * This helper provides examples and methods for:
 * - Creating customers programmatically with all options
 * - Getting customers from database
 * - Loading customers
 * - Getting customer addresses
 * - Getting customer cart and wishlist
 */

declare(strict_types=1);

namespace Advik\CustomerCreation\Helper;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order\Config;

class CustomerHelper extends AbstractHelper
{
    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var AddressFactory
     */
    protected $addressFactory;

    /**
     * @var AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var WishlistFactory
     */
    protected $wishlistFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    protected $orderConfig;

    /**
     * CustomerHelper constructor
     */
    public function __construct(
        Context $context,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        AddressFactory $addressFactory,
        AddressRepositoryInterface $addressRepository,
        CollectionFactory $customerCollectionFactory,
        CartRepositoryInterface $cartRepository,
        WishlistFactory $wishlistFactory,
        StoreManagerInterface $storeManager,
        Config $orderConfig
    ) {
        parent::__construct($context);
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->addressFactory = $addressFactory;
        $this->addressRepository = $addressRepository;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->cartRepository = $cartRepository;
        $this->wishlistFactory = $wishlistFactory;
        $this->storeManager = $storeManager;
        $this->orderConfig = $orderConfig;
    }

    /**
     * ==================== CREATING CUSTOMERS ====================
     */

    /**
     * Create a customer programmatically - BASIC METHOD
     *
     * This is the simplest way to create a customer using CustomerFactory
     * USE CASE: Quick creation when you only need basic info
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $password
     * @param int $websiteId (optional, defaults to current)
     * @return \Magento\Customer\Model\Customer
     * @throws \Exception
     */
    public function createCustomerBasic(
        string $email,
        string $firstName,
        string $lastName,
        string $password,
        int $websiteId = 0
    ) {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }

        $customer = $this->customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->setEmail($email);
        $customer->setFirstname($firstName);
        $customer->setLastname($lastName);
        $customer->setPassword($password);
        $customer->setGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);

        // Save the customer
        $customer->save();

        return $customer;
    }

    /**
     * Create a customer programmatically - COMPREHENSIVE METHOD
     *
     * Creates customer with ALL available options
     * USE CASE: Complete customer setup with addresses, attributes, etc.
     *
     * @param array $customerData [
     *     'email' => 'customer@example.com',
     *     'firstname' => 'John',
     *     'lastname' => 'Doe',
     *     'password' => 'secure_password',
     *     'website_id' => 1,
     *     'store_id' => 1,
     *     'group_id' => 1,
     *     'dob' => '1990-01-01',
     *     'gender' => 1, // 1 = Male, 2 = Female
     *     'prefix' => 'Mr.',
     *     'suffix' => 'Jr.',
     *     'taxvat' => '12345678',
     *     'is_active' => 1,
     *     'company' => 'ACME Corp',
     *     'street' => '123 Main Street',
     *     'city' => 'New York',
     *     'state' => 'NY',
     *     'postcode' => '10001',
     *     'country_id' => 'US',
     *     'telephone' => '555-1234',
     *     'fax' => '555-5678',
     *     'address_default_billing' => true,
     *     'address_default_shipping' => true,
     * ]
     * @return \Magento\Customer\Model\Customer
     * @throws \Exception
     */
    public function createCustomerComprehensive(array $customerData)
    {
        try {
            // Set defaults
            if (empty($customerData['website_id'])) {
                $customerData['website_id'] = $this->storeManager->getWebsite()->getId();
            }
            if (empty($customerData['store_id'])) {
                $customerData['store_id'] = $this->storeManager->getStore()->getId();
            }
            if (empty($customerData['group_id'])) {
                $customerData['group_id'] = \Magento\Customer\Model\Group::NOT_LOGGED_IN_ID;
            }

            // Create customer object
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($customerData['website_id']);
            $customer->setStoreId($customerData['store_id']);
            $customer->setEmail($customerData['email']);
            $customer->setFirstname($customerData['firstname']);
            $customer->setLastname($customerData['lastname']);
            $customer->setPassword($customerData['password']);
            $customer->setGroupId($customerData['group_id']);

            // Optional fields
            if (isset($customerData['dob'])) {
                $customer->setDob($customerData['dob']);
            }
            if (isset($customerData['gender'])) {
                $customer->setGender($customerData['gender']);
            }
            if (isset($customerData['prefix'])) {
                $customer->setPrefix($customerData['prefix']);
            }
            if (isset($customerData['suffix'])) {
                $customer->setSuffix($customerData['suffix']);
            }
            if (isset($customerData['taxvat'])) {
                $customer->setTaxvat($customerData['taxvat']);
            }
            if (isset($customerData['is_active'])) {
                $customer->setIsActive($customerData['is_active']);
            }

            // Save customer first to get ID
            $customer->save();

            // Add address if provided
            if (isset($customerData['street'])) {
                $this->addCustomerAddress(
                    $customer,
                    $customerData,
                    isset($customerData['address_default_billing']) ? $customerData['address_default_billing'] : false,
                    isset($customerData['address_default_shipping']) ? $customerData['address_default_shipping'] : false
                );
            }

            return $customer;

        } catch (\Exception $e) {
            throw new \Exception("Failed to create customer: " . $e->getMessage());
        }
    }

    /**
     * Create customer using CustomerRepository (API method)
     *
     * This uses the Customer API which is more reliable and respects all validations
     * USE CASE: When you need API-level validation and consistency
     *
     * @param array $customerData
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws \Exception
     */
    public function createCustomerViaApi(array $customerData)
    {
        try {
            // Create customer data object
            $customerObject = $this->customerFactory->create();

            foreach ($customerData as $key => $value) {
                if ($key !== 'password') {
                    $customerObject->setData($key, $value);
                }
            }

            // Save via repository
            $customer = $this->customerRepository->save(
                $customerObject,
                $customerData['password'] ?? null
            );

            return $customer;

        } catch (\Exception $e) {
            throw new \Exception("Failed to create customer via API: " . $e->getMessage());
        }
    }

    /**
     * Add address to customer
     *
     * @param \Magento\Customer\Model\Customer $customer
     * @param array $addressData
     * @param bool $isDefaultBilling
     * @param bool $isDefaultShipping
     * @return \Magento\Customer\Model\Address
     */
    public function addCustomerAddress(
        $customer,
        array $addressData,
        bool $isDefaultBilling = false,
        bool $isDefaultShipping = false
    ) {
        $address = $this->addressFactory->create();
        $address->setCustomerId($customer->getId());
        $address->setFirstname($addressData['firstname'] ?? $customer->getFirstname());
        $address->setLastname($addressData['lastname'] ?? $customer->getLastname());
        $address->setCompany($addressData['company'] ?? '');
        $address->setStreet($addressData['street'] ?? '');
        $address->setCity($addressData['city'] ?? '');
        $address->setRegion($addressData['state'] ?? '');
        $address->setPostcode($addressData['postcode'] ?? '');
        $address->setCountryId($addressData['country_id'] ?? 'US');
        $address->setTelephone($addressData['telephone'] ?? '');
        $address->setFax($addressData['fax'] ?? '');

        if ($isDefaultBilling) {
            $address->setIsDefaultBilling(1);
        }
        if ($isDefaultShipping) {
            $address->setIsDefaultShipping(1);
        }

        $address->save();

        return $address;
    }

    /**
     * ==================== GETTING CUSTOMERS ====================
     */

    /**
     * Get all customers from database (with filters)
     *
     * USE CASE: Get list of customers with various filters
     *
     * @param int $limit (optional)
     * @param int $page (optional)
     * @param array $filters ['email' => 'test@example.com', 'name' => 'John']
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getAllCustomers(int $limit = 20, int $page = 1, array $filters = [])
    {
        $collection = $this->customerCollectionFactory->create();

        // Add filters
        if (isset($filters['email'])) {
            $collection->addAttributeToFilter('email', ['like' => '%' . $filters['email'] . '%']);
        }

        if (isset($filters['firstname'])) {
            $collection->addAttributeToFilter('firstname', ['like' => '%' . $filters['firstname'] . '%']);
        }

        if (isset($filters['lastname'])) {
            $collection->addAttributeToFilter('lastname', ['like' => '%' . $filters['lastname'] . '%']);
        }

        if (isset($filters['group_id'])) {
            $collection->addAttributeToFilter('group_id', $filters['group_id']);
        }

        if (isset($filters['created_from'])) {
            $collection->addAttributeToFilter('created_at', ['from' => $filters['created_from']]);
        }

        if (isset($filters['created_to'])) {
            $collection->addAttributeToFilter('created_at', ['to' => $filters['created_to']]);
        }

        // Add pagination
        $collection->setPageSize($limit)
            ->setCurPage($page);

        // Add order
        $collection->setOrder('entity_id', 'desc');

        return $collection;
    }

    /**
     * Get customers by specific criteria
     *
     * USE CASE: Get customers with complex filtering
     *
     * @param string $criteria Field name (email, firstname, lastname, etc.)
     * @param string $value
     * @param string $condition ['eq', 'like', 'from', 'to', 'gteq', 'lteq']
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCustomersByFilter(string $criteria, string $value, string $condition = 'like')
    {
        $collection = $this->customerCollectionFactory->create();

        $filter = [$condition => $value];
        if ($condition === 'like') {
            $filter = ['like' => '%' . $value . '%'];
        }

        $collection->addAttributeToFilter($criteria, $filter);

        return $collection;
    }

    /**
     * Get active customers
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getActiveCustomers()
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToFilter('is_active', 1);
        $collection->setOrder('created_at', 'desc');

        return $collection;
    }

    /**
     * Get customers by group
     *
     * @param int $groupId
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getCustomersByGroup(int $groupId)
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToFilter('group_id', $groupId);

        return $collection;
    }

    /**
     * Get recently registered customers
     *
     * @param int $days (optional, default 30 days)
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function getRecentlyRegisteredCustomers(int $days = 30)
    {
        $fromDate = date('Y-m-d H:i:s', strtotime("-$days days"));

        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToFilter('created_at', ['gteq' => $fromDate]);
        $collection->setOrder('created_at', 'desc');

        return $collection;
    }

    /**
     * Get customer count
     *
     * @return int
     */
    public function getCustomerCount()
    {
        $collection = $this->customerCollectionFactory->create();
        return $collection->getSize();
    }

    /**
     * ==================== LOADING CUSTOMERS ====================
     */

    /**
     * Load customer by ID
     *
     * USE CASE: Most common way to load a specific customer
     *
     * @param int $customerId
     * @return \Magento\Customer\Model\Customer
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadCustomerById(int $customerId)
    {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            throw new \Exception("Customer not found with ID: " . $customerId);
        }
    }

    /**
     * Load customer by email
     *
     * USE CASE: When you have email but not customer ID
     *
     * @param string $email
     * @param int $websiteId (optional)
     * @return \Magento\Customer\Model\Customer
     * @throws \Exception
     */
    public function loadCustomerByEmail(string $email, int $websiteId = 0)
    {
        try {
            if (!$websiteId) {
                $websiteId = $this->storeManager->getWebsite()->getId();
            }

            return $this->customerRepository->get($email, $websiteId);
        } catch (\Exception $e) {
            throw new \Exception("Customer not found with email: " . $email);
        }
    }

    /**
     * Load customer by attribute
     *
     * USE CASE: Load customer by custom attribute or any other attribute
     *
     * @param string $attributeCode
     * @param string|int $value
     * @return \Magento\Customer\Model\Customer|null
     */
    public function loadCustomerByAttribute(string $attributeCode, $value)
    {
        $collection = $this->customerCollectionFactory->create();
        $collection->addAttributeToFilter($attributeCode, $value)
            ->setPageSize(1);

        if ($collection->getSize()) {
            return $collection->getFirstItem();
        }

        return null;
    }

    /**
     * Check if customer exists
     *
     * @param string $email
     * @return bool
     */
    public function customerExists(string $email): bool
    {
        try {
            $this->loadCustomerByEmail($email);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ==================== CUSTOMER ADDRESSES ====================
     */

    /**
     * Get all customer addresses
     *
     * USE CASE: Get all addresses for a customer
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerAddresses(int $customerId): array
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            return $customer->getAddresses();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get customer default billing address
     *
     * USE CASE: Get the primary billing address
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getDefaultBillingAddress(int $customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer->getDefaultBilling()) {
                return $this->addressRepository->getById($customer->getDefaultBilling());
            }
        } catch (\Exception $e) {
            // Exception handled
        }

        return null;
    }

    /**
     * Get customer default shipping address
     *
     * USE CASE: Get the primary shipping address
     *
     * @param int $customerId
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getDefaultShippingAddress(int $customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            if ($customer->getDefaultShipping()) {
                return $this->addressRepository->getById($customer->getDefaultShipping());
            }
        } catch (\Exception $e) {
            // Exception handled
        }

        return null;
    }

    /**
     * Get address details
     *
     * USE CASE: Get formatted address information
     *
     * @param int $addressId
     * @return array|null
     */
    public function getAddressDetails(int $addressId)
    {
        try {
            $address = $this->addressRepository->getById($addressId);

            return [
                'id' => $address->getId(),
                'firstname' => $address->getFirstname(),
                'lastname' => $address->getLastname(),
                'company' => $address->getCompany(),
                'street' => $address->getStreet(),
                'city' => $address->getCity(),
                'region' => $address->getRegion(),
                'postcode' => $address->getPostcode(),
                'country_id' => $address->getCountryId(),
                'telephone' => $address->getTelephone(),
                'fax' => $address->getFax(),
                'is_default_billing' => $address->isDefaultBilling(),
                'is_default_shipping' => $address->isDefaultShipping(),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Update customer address
     *
     * @param int $addressId
     * @param array $addressData
     * @return bool
     */
    public function updateCustomerAddress(int $addressId, array $addressData): bool
    {
        try {
            $address = $this->addressRepository->getById($addressId);

            foreach ($addressData as $key => $value) {
                $address->setData($key, $value);
            }

            $this->addressRepository->save($address);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete customer address
     *
     * @param int $addressId
     * @return bool
     */
    public function deleteCustomerAddress(int $addressId): bool
    {
        try {
            $address = $this->addressRepository->getById($addressId);
            $this->addressRepository->delete($address);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ==================== CUSTOMER CART ====================
     */

    /**
     * Get customer shopping cart
     *
     * USE CASE: Get active shopping cart for a customer
     *
     * @param int $customerId
     * @return \Magento\Quote\Model\Quote|null
     */
    public function getCustomerCart(int $customerId)
    {
        try {
            // Get active cart (quote) for customer
            $quote = $this->cartRepository->getActiveForCustomer($customerId);
            return $quote;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get customer cart items
     *
     * USE CASE: Get all items in customer's cart
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerCartItems(int $customerId): array
    {
        $quote = $this->getCustomerCart($customerId);

        if (!$quote) {
            return [];
        }

        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $items[] = [
                'item_id' => $item->getId(),
                'product_id' => $item->getProductId(),
                'product_name' => $item->getName(),
                'sku' => $item->getSku(),
                'quantity' => $item->getQty(),
                'price' => $item->getPrice(),
                'row_total' => $item->getRowTotal(),
            ];
        }

        return $items;
    }

    /**
     * Get cart totals
     *
     * @param int $customerId
     * @return array
     */
    public function getCartTotals(int $customerId): array
    {
        $quote = $this->getCustomerCart($customerId);

        if (!$quote) {
            return [];
        }

        return [
            'subtotal' => $quote->getSubtotal(),
            'tax' => $quote->getShippingAddress()->getTaxAmount(),
            'shipping' => $quote->getShippingAddress()->getShippingAmount(),
            'grand_total' => $quote->getGrandTotal(),
            'item_count' => $quote->getItemsCount(),
            'quantity_count' => $quote->getItemsQty(),
        ];
    }

    /**
     * ==================== CUSTOMER WISHLIST ====================
     */

    /**
     * Get customer wishlist
     *
     * USE CASE: Get all wishlist items for a customer
     *
     * @param int $customerId
     * @return \Magento\Wishlist\Model\Wishlist|null
     */
    public function getCustomerWishlist(int $customerId)
    {
        try {
            $wishlist = $this->wishlistFactory->create()
                ->loadByCustomerId($customerId, false);

            return $wishlist;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get customer wishlist items
     *
     * USE CASE: Get all items in customer's wishlist
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerWishlistItems(int $customerId): array
    {
        $wishlist = $this->getCustomerWishlist($customerId);

        if (!$wishlist) {
            return [];
        }

        $items = [];
        $itemCollection = $wishlist->getItemCollection();

        foreach ($itemCollection as $item) {
            $items[] = [
                'item_id' => $item->getId(),
                'product_id' => $item->getProductId(),
                'product_name' => $item->getProduct()->getName(),
                'sku' => $item->getProduct()->getSku(),
                'price' => $item->getProduct()->getPrice(),
                'qty' => $item->getQty(),
                'added_at' => $item->getAddedAt(),
            ];
        }

        return $items;
    }

    /**
     * Get wishlist item count
     *
     * @param int $customerId
     * @return int
     */
    public function getWishlistItemCount(int $customerId): int
    {
        $wishlist = $this->getCustomerWishlist($customerId);

        if (!$wishlist) {
            return 0;
        }

        return $wishlist->getItemCollection()->getSize();
    }

    /**
     * ==================== CUSTOMER ORDERS ====================
     */

    /**
     * Get customer orders
     *
     * USE CASE: Get all orders for a customer
     *
     * @param int $customerId
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getCustomerOrders(int $customerId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $orderCollection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\CollectionFactory::class)
            ->create()
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at', 'desc');

        return $orderCollection;
    }

    /**
     * Get customer order count
     *
     * @param int $customerId
     * @return int
     */
    public function getCustomerOrderCount(int $customerId): int
    {
        return $this->getCustomerOrders($customerId)->getSize();
    }

    /**
     * Get customer total spent
     *
     * USE CASE: Get total amount spent by customer
     *
     * @param int $customerId
     * @return float
     */
    public function getCustomerTotalSpent(int $customerId): float
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            return $customer->getLifetime() ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * ==================== CUSTOMER OPERATIONS ====================
     */

    /**
     * Update customer information
     *
     * USE CASE: Update customer data
     *
     * @param int $customerId
     * @param array $customerData
     * @return bool
     */
    public function updateCustomer(int $customerId, array $customerData): bool
    {
        try {
            $customer = $this->customerRepository->getById($customerId);

            foreach ($customerData as $key => $value) {
                $customer->setData($key, $value);
            }

            $this->customerRepository->save($customer);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Delete customer
     *
     * @param int $customerId
     * @return bool
     */
    public function deleteCustomer(int $customerId): bool
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $this->customerRepository->delete($customer);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get customer custom attributes
     *
     * @param int $customerId
     * @return array
     */
    public function getCustomerAttributes(int $customerId): array
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            return $customer->getCustomAttributes() ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * ==================== HELPER METHODS ====================
     */

    /**
     * Validate email format
     *
     * @param string $email
     * @return bool
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Generate report of all customers
     *
     * @return array
     */
    public function generateCustomerReport(): array
    {
        $collection = $this->customerCollectionFactory->create();

        return [
            'total_customers' => $collection->getSize(),
            'active_customers' => $this->getActiveCustomers()->getSize(),
            'recent_customers_30d' => $this->getRecentlyRegisteredCustomers(30)->getSize(),
            'recent_customers_7d' => $this->getRecentlyRegisteredCustomers(7)->getSize(),
        ];
    }
}

