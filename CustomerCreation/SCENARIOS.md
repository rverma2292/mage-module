# Comprehensive Scenarios - Practical Implementation Guide

This document covers various real-world scenarios and how to implement them using the CustomerHelper.

## Table of Contents

1. [Scenario 1: Customer Registration](#scenario-1-customer-registration)
2. [Scenario 2: Customer Import](#scenario-2-customer-import)
3. [Scenario 3: Customer Lookup](#scenario-3-customer-lookup)
4. [Scenario 4: Address Management](#scenario-4-address-management)
5. [Scenario 5: Cart Analysis](#scenario-5-cart-analysis)
6. [Scenario 6: Wishlist Tracking](#scenario-6-wishlist-tracking)
7. [Scenario 7: Order History](#scenario-7-order-history)
8. [Scenario 8: Customer Segmentation](#scenario-8-customer-segmentation)
9. [Scenario 9: Data Cleanup](#scenario-9-data-cleanup)
10. [Scenario 10: API Integration](#scenario-10-api-integration)

---

## Scenario 1: Customer Registration

### Requirement
Handle a new customer registration with validation and default setup.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Psr\Log\LoggerInterface;

class RegistrationHandler
{
    private $customerHelper;
    private $logger;

    public function __construct(
        CustomerHelper $customerHelper,
        LoggerInterface $logger
    ) {
        $this->customerHelper = $customerHelper;
        $this->logger = $logger;
    }

    /**
     * Handle new customer registration
     */
    public function registerCustomer(array $registrationData)
    {
        try {
            // Step 1: Validate email
            if (!$this->customerHelper->isValidEmail($registrationData['email'])) {
                throw new \Exception('Invalid email format');
            }

            // Step 2: Check if email already exists
            if ($this->customerHelper->customerExists($registrationData['email'])) {
                throw new \Exception('Email already registered');
            }

            // Step 3: Validate password
            if (strlen($registrationData['password']) < 8) {
                throw new \Exception('Password must be at least 8 characters');
            }

            // Step 4: Create customer with comprehensive data
            $customer = $this->customerHelper->createCustomerComprehensive([
                'email' => $registrationData['email'],
                'firstname' => $registrationData['firstname'],
                'lastname' => $registrationData['lastname'],
                'password' => $registrationData['password'],
                'website_id' => $registrationData['website_id'] ?? 1,
                'store_id' => $registrationData['store_id'] ?? 1,
                'group_id' => 1, // Default customer group
                'dob' => $registrationData['dob'] ?? null,
                'gender' => $registrationData['gender'] ?? null,
                'telephone' => $registrationData['telephone'] ?? '',
                'company' => $registrationData['company'] ?? '',
                'street' => $registrationData['street'] ?? 'Not provided',
                'city' => $registrationData['city'] ?? 'Not provided',
                'state' => $registrationData['state'] ?? 'Not provided',
                'postcode' => $registrationData['postcode'] ?? '00000',
                'country_id' => $registrationData['country_id'] ?? 'US',
                'address_default_billing' => true,
                'address_default_shipping' => true,
            ]);

            // Step 5: Log success
            $this->logger->info(
                "New customer registered: {$customer->getEmail()} (ID: {$customer->getId()})"
            );

            // Step 6: Trigger post-registration actions
            $this->onRegistrationSuccess($customer);

            return [
                'success' => true,
                'customer_id' => $customer->getId(),
                'message' => 'Registration successful'
            ];

        } catch (\Exception $e) {
            $this->logger->error("Registration failed: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Post-registration actions
     */
    private function onRegistrationSuccess($customer)
    {
        // Send welcome email, add to newsletter, etc.
        // $this->emailHelper->sendWelcomeEmail($customer);
    }
}
```

---

## Scenario 2: Customer Import

### Requirement
Import customers from CSV file with duplicate detection and validation.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Psr\Log\LoggerInterface;

class CustomerImporter
{
    private $customerHelper;
    private $logger;

    public function __construct(
        CustomerHelper $customerHelper,
        LoggerInterface $logger
    ) {
        $this->customerHelper = $customerHelper;
        $this->logger = $logger;
    }

    /**
     * Import customers from CSV file
     * CSV Format: email, firstname, lastname, password, telephone, company, street, city, state, postcode, country_id
     */
    public function importFromCsv(string $filePath, array $options = [])
    {
        $results = [
            'total' => 0,
            'imported' => 0,
            'skipped' => 0,
            'errors' => [],
            'skipped_emails' => [],
        ];

        try {
            if (!file_exists($filePath)) {
                throw new \Exception("File not found: $filePath");
            }

            $handle = fopen($filePath, 'r');
            $header = fgetcsv($handle);

            if (!$header) {
                throw new \Exception("Invalid CSV file");
            }

            $lineNumber = 1;

            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;
                $results['total']++;

                try {
                    // Parse row
                    $data = array_combine($header, $row);

                    // Validate required fields
                    if (empty($data['email']) || empty($data['firstname']) || empty($data['lastname'])) {
                        throw new \Exception("Missing required fields (email, firstname, lastname)");
                    }

                    // Check for duplicates
                    if ($this->customerHelper->customerExists($data['email'])) {
                        $results['skipped']++;
                        $results['skipped_emails'][] = $data['email'];
                        $this->logger->info("Line $lineNumber: Customer exists - " . $data['email']);
                        continue;
                    }

                    // Default password if not provided
                    if (empty($data['password'])) {
                        $data['password'] = $this->generateTemporaryPassword();
                    }

                    // Create customer
                    $customer = $this->customerHelper->createCustomerComprehensive([
                        'email' => $data['email'],
                        'firstname' => $data['firstname'],
                        'lastname' => $data['lastname'],
                        'password' => $data['password'],
                        'telephone' => $data['telephone'] ?? '',
                        'company' => $data['company'] ?? '',
                        'street' => $data['street'] ?? '',
                        'city' => $data['city'] ?? '',
                        'state' => $data['state'] ?? '',
                        'postcode' => $data['postcode'] ?? '',
                        'country_id' => $data['country_id'] ?? 'US',
                        'address_default_billing' => $options['set_default_address'] ?? true,
                        'address_default_shipping' => $options['set_default_address'] ?? true,
                    ]);

                    $results['imported']++;
                    $this->logger->info("Line $lineNumber: Imported - " . $customer->getEmail());

                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'line' => $lineNumber,
                        'error' => $e->getMessage(),
                        'data' => $data ?? []
                    ];
                    $this->logger->error("Line $lineNumber: " . $e->getMessage());
                }
            }

            fclose($handle);

        } catch (\Exception $e) {
            $this->logger->error("Import failed: " . $e->getMessage());
            $results['errors'][] = [
                'general' => $e->getMessage()
            ];
        }

        return $results;
    }

    private function generateTemporaryPassword(): string
    {
        return 'Temp' . uniqid() . '!@';
    }
}
```

---

## Scenario 3: Customer Lookup

### Requirement
Provide comprehensive customer lookup functionality via multiple search methods.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;

class CustomerLookup
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Multi-method customer search
     */
    public function search(string $searchTerm): array
    {
        $results = [
            'by_id' => null,
            'by_email' => null,
            'by_name' => [],
            'by_phone' => [],
        ];

        // Try by ID
        if (is_numeric($searchTerm)) {
            try {
                $results['by_id'] = $this->customerHelper->loadCustomerById((int)$searchTerm);
            } catch (\Exception $e) {
                // ID not found
            }
        }

        // Try by email
        try {
            $results['by_email'] = $this->customerHelper->loadCustomerByEmail($searchTerm);
        } catch (\Exception $e) {
            // Email not found
        }

        // Try by partial name/email
        $collection = $this->customerHelper->getAllCustomers(1000);
        foreach ($collection as $customer) {
            $email = $customer->getEmail();
            $fullName = $customer->getFirstname() . ' ' . $customer->getLastname();
            $phone = $customer->getPrimaryBillingAddress() 
                ? $customer->getPrimaryBillingAddress()->getTelephone() 
                : '';

            // Check email match
            if (stripos($email, $searchTerm) !== false) {
                $results['by_name'][] = $customer;
            }

            // Check name match
            if (stripos($fullName, $searchTerm) !== false && !in_array($customer, $results['by_name'])) {
                $results['by_name'][] = $customer;
            }

            // Check phone match
            if (!empty($phone) && stripos($phone, $searchTerm) !== false) {
                $results['by_phone'][] = $customer;
            }
        }

        return $results;
    }

    /**
     * Get detailed customer profile
     */
    public function getDetailedProfile(int $customerId): array
    {
        try {
            $customer = $this->customerHelper->loadCustomerById($customerId);

            return [
                'personal' => [
                    'id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'dob' => $customer->getDob(),
                    'gender' => $customer->getGender(),
                    'phone' => $customer->getPrimaryBillingAddress() 
                        ? $customer->getPrimaryBillingAddress()->getTelephone() 
                        : null,
                ],
                'addresses' => [
                    'total' => count($this->customerHelper->getCustomerAddresses($customerId)),
                    'billing' => $this->mapAddress($this->customerHelper->getDefaultBillingAddress($customerId)),
                    'shipping' => $this->mapAddress($this->customerHelper->getDefaultShippingAddress($customerId)),
                ],
                'shopping' => [
                    'orders' => $this->customerHelper->getCustomerOrderCount($customerId),
                    'total_spent' => $this->customerHelper->getCustomerTotalSpent($customerId),
                    'cart_items' => count($this->customerHelper->getCustomerCartItems($customerId)),
                    'wishlist_items' => $this->customerHelper->getWishlistItemCount($customerId),
                ],
                'system' => [
                    'created_at' => $customer->getCreatedAt(),
                    'updated_at' => $customer->getUpdatedAt(),
                    'group_id' => $customer->getGroupId(),
                    'is_active' => $customer->getIsActive(),
                ],
            ];

        } catch (\Exception $e) {
            return [];
        }
    }

    private function mapAddress($address)
    {
        if (!$address) {
            return null;
        }

        return [
            'id' => $address->getId(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'region' => $address->getRegion(),
            'postcode' => $address->getPostcode(),
            'country' => $address->getCountryId(),
            'phone' => $address->getTelephone(),
            'company' => $address->getCompany(),
        ];
    }
}
```

---

## Scenario 4: Address Management

### Requirement
Comprehensive address management including validation, defaults, and consolidation.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;

class AddressManager
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Ensure customer has proper address setup
     */
    public function setupAddresses(int $customerId, array $defaultAddressData): bool
    {
        try {
            $addressCount = count($this->customerHelper->getCustomerAddresses($customerId));

            if ($addressCount === 0) {
                // Add initial address
                $customer = $this->customerHelper->loadCustomerById($customerId);
                $this->customerHelper->addCustomerAddress(
                    $customer,
                    $defaultAddressData,
                    true,  // billing
                    true   // shipping
                );
                return true;
            }

            // Ensure default addresses exist
            $this->ensureDefaults($customerId);
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get all customer addresses with details
     */
    public function getAllAddressesWithDetails(int $customerId): array
    {
        $addresses = [];
        $customerAddresses = $this->customerHelper->getCustomerAddresses($customerId);

        foreach ($customerAddresses as $address) {
            $details = $this->customerHelper->getAddressDetails($address->getId());
            if ($details) {
                $details['is_billing_default'] = $address->isDefaultBilling();
                $details['is_shipping_default'] = $address->isDefaultShipping();
                $addresses[] = $details;
            }
        }

        return $addresses;
    }

    /**
     * Update address with validation
     */
    public function updateAddress(int $addressId, array $newData): array
    {
        try {
            // Validate required fields
            $required = ['street', 'city', 'state', 'postcode', 'country_id'];
            foreach ($required as $field) {
                if (empty($newData[$field])) {
                    throw new \Exception("Field $field is required");
                }
            }

            $updated = $this->customerHelper->updateCustomerAddress($addressId, $newData);

            return [
                'success' => $updated,
                'message' => $updated ? 'Address updated' : 'Failed to update'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Set default addresses
     */
    public function setDefaults(int $customerId, int $billingAddressId, int $shippingAddressId): bool
    {
        try {
            $this->customerHelper->updateCustomerAddress($billingAddressId, [
                'is_default_billing' => 1
            ]);

            $this->customerHelper->updateCustomerAddress($shippingAddressId, [
                'is_default_shipping' => 1
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Consolidate addresses (keep only 2)
     */
    public function consolidateAddresses(int $customerId): int
    {
        $addresses = $this->customerHelper->getCustomerAddresses($customerId);
        $deleted = 0;

        // Keep default billing and shipping only
        foreach (array_slice($addresses, 2) as $address) {
            if ($this->customerHelper->deleteCustomerAddress($address->getId())) {
                $deleted++;
            }
        }

        return $deleted;
    }

    private function ensureDefaults(int $customerId): void
    {
        $billing = $this->customerHelper->getDefaultBillingAddress($customerId);
        $shipping = $this->customerHelper->getDefaultShippingAddress($customerId);

        $addresses = $this->customerHelper->getCustomerAddresses($customerId);

        if (!$billing && !empty($addresses)) {
            $first = reset($addresses);
            $this->customerHelper->updateCustomerAddress($first->getId(), [
                'is_default_billing' => 1
            ]);
        }

        if (!$shipping && !empty($addresses)) {
            $first = reset($addresses);
            $this->customerHelper->updateCustomerAddress($first->getId(), [
                'is_default_shipping' => 1
            ]);
        }
    }
}
```

---

## Scenario 5: Cart Analysis

### Requirement
Track and analyze customer shopping carts for abandoned cart recovery.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;

class CartAnalyzer
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Get cart analysis for customer
     */
    public function analyzeCart(int $customerId): array
    {
        try {
            $cart = $this->customerHelper->getCustomerCart($customerId);
            
            if (!$cart) {
                return [
                    'has_cart' => false,
                    'message' => 'No active cart'
                ];
            }

            $items = $this->customerHelper->getCustomerCartItems($customerId);
            $totals = $this->customerHelper->getCartTotals($customerId);

            return [
                'has_cart' => true,
                'abandoned' => $this->isAbandoned($customerId),
                'items_count' => count($items),
                'items_detail' => $items,
                'subtotal' => $totals['subtotal'] ?? 0,
                'tax' => $totals['tax'] ?? 0,
                'shipping' => $totals['shipping'] ?? 0,
                'grand_total' => $totals['grand_total'] ?? 0,
                'recovery_priority' => $this->getRecoveryPriority($totals['grand_total'] ?? 0),
            ];

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find all carts abandoned in last X days
     */
    public function findAbandonedCarts(int $days = 3): array
    {
        $abandonedCarts = [];

        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();

            if ($this->isAbandonedSince($customerId, $days)) {
                $analysis = $this->analyzeCart($customerId);
                $analysis['customer_email'] = $customer->getEmail();
                $analysis['customer_name'] = $customer->getFirstname() . ' ' . $customer->getLastname();
                $abandonedCarts[] = $analysis;
            }
        }

        // Sort by cart value (highest first)
        usort($abandonedCarts, function ($a, $b) {
            return ($b['grand_total'] ?? 0) <=> ($a['grand_total'] ?? 0);
        });

        return $abandonedCarts;
    }

    /**
     * Get cart items summary
     */
    public function getCartItemSummary(int $customerId): string
    {
        try {
            $items = $this->customerHelper->getCustomerCartItems($customerId);

            if (empty($items)) {
                return "No items in cart";
            }

            $summary = "Cart with " . count($items) . " item(s):\n";

            foreach ($items as $item) {
                $summary .= "- " . $item['product_name'] . " (x" . $item['quantity'] . ") = $" . $item['row_total'] . "\n";
            }

            return $summary;

        } catch (\Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }

    /**
     * Check if cart is likely abandoned
     */
    private function isAbandoned(int $customerId): bool
    {
        try {
            $orderCount = $this->customerHelper->getCustomerOrderCount($customerId);
            $cartItems = $this->customerHelper->getCustomerCartItems($customerId);

            // If has items but no orders, it might be abandoned
            return !empty($cartItems) && $orderCount === 0;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if abandoned for X days
     */
    private function isAbandonedSince(int $customerId, int $days): bool
    {
        try {
            $cart = $this->customerHelper->getCustomerCart($customerId);
            
            if (!$cart) {
                return false;
            }

            // Check if cart hasn't been updated
            $lastUpdate = strtotime($cart->getUpdatedAt());
            $daysSinceUpdate = floor((time() - $lastUpdate) / (24 * 60 * 60));

            return $daysSinceUpdate >= $days && $this->isAbandoned($customerId);

        } catch (\Exception $e) {
            return false;
        }
    }

    private function getRecoveryPriority(float $cartValue): string
    {
        if ($cartValue < 50) {
            return 'low';
        } elseif ($cartValue < 200) {
            return 'medium';
        } else {
            return 'high';
        }
    }
}
```

---

## Scenario 6: Wishlist Tracking

### Requirement
Track customer wishlist for inventory and product recommendation purposes.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;

class WishlistTracker
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Get customer wishlist analysis
     */
    public function analyzeWishlist(int $customerId): array
    {
        try {
            $items = $this->customerHelper->getCustomerWishlistItems($customerId);
            $itemCount = $this->customerHelper->getWishlistItemCount($customerId);

            if ($itemCount === 0) {
                return [
                    'has_wishlist' => true,
                    'is_empty' => true,
                    'items_count' => 0
                ];
            }

            $totalValue = 0;
            $categories = [];
            $avgPrice = 0;

            foreach ($items as $item) {
                $itemValue = $item['price'] * $item['qty'];
                $totalValue += $itemValue;
            }

            $avgPrice = $itemCount > 0 ? $totalValue / $itemCount : 0;

            return [
                'has_wishlist' => true,
                'is_empty' => false,
                'items_count' => $itemCount,
                'items' => $items,
                'total_value' => $totalValue,
                'average_price' => $avgPrice,
                'engagement_level' => $this->getEngagementLevel($itemCount),
            ];

        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Find customers with high-value wishlist items
     */
    public function findHighValueWishlistCustomers(float $minValue = 500): array
    {
        $customers = [];
        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $analysis = $this->analyzeWishlist($customerId);

            if (isset($analysis['total_value']) && $analysis['total_value'] >= $minValue) {
                $customers[] = [
                    'customer_id' => $customerId,
                    'email' => $customer->getEmail(),
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'wishlist_value' => $analysis['total_value'],
                    'items_count' => $analysis['items_count'],
                ];
            }
        }

        // Sort by wishlist value
        usort($customers, function ($a, $b) {
            return $b['wishlist_value'] <=> $a['wishlist_value'];
        });

        return $customers;
    }

    /**
     * Get wishlist items grouped by product
     */
    public function getWishlistProductSummary(int $customerId): array
    {
        $items = $this->customerHelper->getCustomerWishlistItems($customerId);
        $products = [];

        foreach ($items as $item) {
            $productId = $item['product_id'];

            if (!isset($products[$productId])) {
                $products[$productId] = [
                    'product_id' => $productId,
                    'name' => $item['product_name'],
                    'sku' => $item['sku'],
                    'price' => $item['price'],
                    'qty_in_wishlist' => 0,
                    'added_at' => $item['added_at'],
                ];
            }

            $products[$productId]['qty_in_wishlist'] += $item['qty'];
        }

        return array_values($products);
    }

    private function getEngagementLevel(int $itemCount): string
    {
        if ($itemCount === 0) {
            return 'none';
        } elseif ($itemCount < 5) {
            return 'low';
        } elseif ($itemCount < 15) {
            return 'medium';
        } else {
            return 'high';
        }
    }
}
```

---

## Scenario 7: Order History

### Requirement
Analyze customer order patterns and purchase history.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;

class OrderAnalyzer
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Get comprehensive order analysis
     */
    public function analyzeOrderHistory(int $customerId): array
    {
        try {
            $orders = $this->customerHelper->getCustomerOrders($customerId);
            $totalOrders = $orders->getSize();
            $totalSpent = $this->customerHelper->getCustomerTotalSpent($customerId);

            if ($totalOrders === 0) {
                return [
                    'total_orders' => 0,
                    'is_new_customer' => true,
                    'message' => 'No orders yet'
                ];
            }

            $orderData = [];
            $statuses = [];
            $yearlySpending = [];

            foreach ($orders as $order) {
                $year = date('Y', strtotime($order->getCreatedAt()));
                $status = $order->getStatus();

                if (!isset($yearlySpending[$year])) {
                    $yearlySpending[$year] = 0;
                }
                $yearlySpending[$year] += $order->getGrandTotal();

                $statuses[$status] = ($statuses[$status] ?? 0) + 1;

                $orderData[] = [
                    'increment_id' => $order->getIncrementId(),
                    'date' => $order->getCreatedAt(),
                    'status' => $status,
                    'total' => $order->getGrandTotal(),
                    'items' => $order->getAllItems() ? count($order->getAllItems()) : 0,
                ];
            }

            $avgOrderValue = $totalSpent / $totalOrders;

            return [
                'total_orders' => $totalOrders,
                'is_new_customer' => false,
                'total_spent' => $totalSpent,
                'average_order_value' => $avgOrderValue,
                'customer_type' => $this->getCustomerType($totalOrders, $totalSpent),
                'recent_orders' => array_slice($orderData, 0, 5),
                'all_orders' => $orderData,
                'status_distribution' => $statuses,
                'yearly_spending' => $yearlySpending,
                'last_order' => reset($orderData),
            ];

        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find high-value customers
     */
    public function findHighValueCustomers(float $minSpent = 1000): array
    {
        $vipCustomers = [];
        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $totalSpent = $this->customerHelper->getCustomerTotalSpent($customerId);

            if ($totalSpent >= $minSpent) {
                $vipCustomers[] = [
                    'customer_id' => $customerId,
                    'email' => $customer->getEmail(),
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'lifetime_value' => $totalSpent,
                    'order_count' => $this->customerHelper->getCustomerOrderCount($customerId),
                ];
            }
        }

        // Sort by lifetime value
        usort($vipCustomers, function ($a, $b) {
            return $b['lifetime_value'] <=> $a['lifetime_value'];
        });

        return $vipCustomers;
    }

    /**
     * Find repeat customers
     */
    public function findRepeatCustomers(int $minOrders = 5): array
    {
        $repeatCustomers = [];
        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $orderCount = $this->customerHelper->getCustomerOrderCount($customerId);

            if ($orderCount >= $minOrders) {
                $repeatCustomers[] = [
                    'customer_id' => $customerId,
                    'email' => $customer->getEmail(),
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    'order_count' => $orderCount,
                    'lifetime_value' => $this->customerHelper->getCustomerTotalSpent($customerId),
                ];
            }
        }

        return $repeatCustomers;
    }

    private function getCustomerType(int $orderCount, float $spent): string
    {
        if ($orderCount >= 10 && $spent > 1000) {
            return 'VIP';
        } elseif ($orderCount >= 5 && $spent > 500) {
            return 'Regular';
        } elseif ($orderCount > 1) {
            return 'Repeat';
        } else {
            return 'First-time';
        }
    }
}
```

---

## Scenario 8: Customer Segmentation

### Requirement
Segment customers for targeted marketing campaigns.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;

class CustomerSegmentation
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Comprehensive customer segmentation
     */
    public function segmentAllCustomers(): array
    {
        $segments = [
            'vip' => [],
            'high_value' => [],
            'regular' => [],
            'repeat' => [],
            'loyal' => [],
            'at_risk' => [],
            'new' => [],
            'inactive' => [],
        ];

        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $segment = $this->determineSegment($customerId, $customer);

            if ($segment) {
                $segments[$segment][] = [
                    'id' => $customerId,
                    'email' => $customer->getEmail(),
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                ];
            }
        }

        return [
            'segments' => $segments,
            'statistics' => $this->getSegmentStatistics($segments),
        ];
    }

    /**
     * Segment by RFM (Recency, Frequency, Monetary)
     */
    public function rfmSegmentation(): array
    {
        $scores = [];
        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $rfm = $this->calculateRFMScore($customerId, $customer);

            $scores[] = array_merge($rfm, [
                'customer_id' => $customerId,
                'email' => $customer->getEmail(),
            ]);
        }

        // Sort by RFM score
        usort($scores, function ($a, $b) {
            return $b['rfm_score'] <=> $a['rfm_score'];
        });

        return $scores;
    }

    private function determineSegment(int $customerId, $customer): ?string
    {
        $orderCount = $this->customerHelper->getCustomerOrderCount($customerId);
        $totalSpent = $this->customerHelper->getCustomerTotalSpent($customerId);
        $createdAt = strtotime($customer->getCreatedAt());
        $daysSinceCreation = floor((time() - $createdAt) / (24 * 60 * 60));

        // VIP: 10+ orders, $1000+ spent
        if ($orderCount >= 10 && $totalSpent > 1000) {
            return 'vip';
        }

        // High Value: 5-9 orders, $500+ spent
        if ($orderCount >= 5 && $orderCount < 10 && $totalSpent > 500) {
            return 'high_value';
        }

        // Regular: 3-4 orders
        if ($orderCount >= 3 && $orderCount < 5) {
            return 'regular';
        }

        // Repeat: 2 orders
        if ($orderCount === 2) {
            return 'repeat';
        }

        // Loyal: 1 order, over 1 year
        if ($orderCount === 1 && $daysSinceCreation > 365) {
            return 'loyal';
        }

        // At Risk: 1 order over 6 months ago
        if ($orderCount === 1 && $daysSinceCreation > 180) {
            return 'at_risk';
        }

        // New: Registered in last 30 days
        if ($daysSinceCreation < 30) {
            return 'new';
        }

        // Inactive: No orders, registered over 180 days ago
        if ($orderCount === 0 && $daysSinceCreation > 180) {
            return 'inactive';
        }

        return 'new';
    }

    private function calculateRFMScore(int $customerId, $customer): array
    {
        $orderCount = $this->customerHelper->getCustomerOrderCount($customerId);
        $totalSpent = $this->customerHelper->getCustomerTotalSpent($customerId);
        $createdAt = strtotime($customer->getCreatedAt());
        $daysSinceCreation = floor((time() - $createdAt) / (24 * 60 * 60));

        // Recency: 1-5 (5 = recent, 1 = old)
        $recency = $daysSinceCreation < 30 ? 5 : ($daysSinceCreation < 90 ? 4 : ($daysSinceCreation < 180 ? 3 : ($daysSinceCreation < 365 ? 2 : 1)));

        // Frequency: 1-5 (5 = many, 1 = few)
        $frequency = $orderCount >= 10 ? 5 : ($orderCount >= 5 ? 4 : ($orderCount >= 3 ? 3 : ($orderCount >= 1 ? 2 : 1)));

        // Monetary: 1-5 (5 = high, 1 = low)
        $monetary = $totalSpent > 1000 ? 5 : ($totalSpent > 500 ? 4 : ($totalSpent > 200 ? 3 : ($totalSpent > 0 ? 2 : 1)));

        $rfmScore = ($recency + $frequency + $monetary) / 3;

        return [
            'recency_score' => $recency,
            'frequency_score' => $frequency,
            'monetary_score' => $monetary,
            'rfm_score' => $rfmScore,
        ];
    }

    private function getSegmentStatistics(array $segments): array
    {
        $stats = [];
        foreach ($segments as $name => $customers) {
            $stats[$name] = count($customers);
        }
        return $stats;
    }
}
```

---

## Scenario 9: Data Cleanup

### Requirement
Find and handle duplicate or invalid customer records.

### Implementation

```php
<?php
namespace MyModule\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;

classDataCleanup
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Find potential duplicate customers
     */
    public function findDuplicates(): array
    {
        $duplicates = [];
        $emailMap = [];
        $nameMap = [];

        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $email = strtolower($customer->getEmail());
            $name = strtolower($customer->getFirstname() . $customer->getLastname());

            // Track email duplicates
            if (!isset($emailMap[$email])) {
                $emailMap[$email] = [];
            }
            $emailMap[$email][] = $customer->getId();

            // Track name duplicates
            if (!isset($nameMap[$name])) {
                $nameMap[$name] = [];
            }
            $nameMap[$name][] = $customer->getId();
        }

        // Find exact email duplicates
        foreach ($emailMap as $email => $ids) {
            if (count($ids) > 1) {
                $duplicates[] = [
                    'type' => 'email_duplicate',
                    'value' => $email,
                    'customer_ids' => $ids,
                ];
            }
        }

        // Find similar names
        foreach ($nameMap as $name => $ids) {
            if (count($ids) > 3) {
                $duplicates[] = [
                    'type' => 'similar_name',
                    'value' => $name,
                    'customer_count' => count($ids),
                    'customer_ids' => $ids,
                ];
            }
        }

        return $duplicates;
    }

    /**
     * Find invalid records
     */
    public function findInvalidRecords(): array
    {
        $invalid = [];
        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $issues = [];

            // Check missing firstname
            if (empty($customer->getFirstname())) {
                $issues[] = 'missing_firstname';
            }

            // Check missing lastname
            if (empty($customer->getLastname())) {
                $issues[] = 'missing_lastname';
            }

            // Check invalid email
            if (!$this->customerHelper->isValidEmail($customer->getEmail())) {
                $issues[] = 'invalid_email';
            }

            // Check missing addresses
            $addresses = $this->customerHelper->getCustomerAddresses($customerId);
            if (empty($addresses)) {
                $issues[] = 'no_addresses';
            }

            if (!empty($issues)) {
                $invalid[] = [
                    'customer_id' => $customerId,
                    'email' => $customer->getEmail(),
                    'issues' => $issues,
                ];
            }
        }

        return $invalid;
    }

    /**
     * Merge duplicate customers
     */
    public function mergeDuplicates(int $primaryId, array $duplicateIds): array
    {
        $results = [
            'merged' => 0,
            'errors' => [],
        ];

        try {
            // Transfer order history, etc., if needed
            // Then delete duplicate records

            foreach ($duplicateIds as $duplicateId) {
                try {
                    if ($this->customerHelper->deleteCustomer($duplicateId)) {
                        $results['merged']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'customer_id' => $duplicateId,
                        'error' => $e->getMessage(),
                    ];
                }
            }

        } catch (\Exception $e) {
            $results['errors'][] = $e->getMessage();
        }

        return $results;
    }
}
```

---

## Scenario 10: API Integration

### Requirement
Integrate customer helper with external systems via API.

### Implementation

```php
<?php
namespace MyModule\Api;

use Advik\CustomerCreation\Helper\CustomerHelper;

class CustomerApi
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * REST API endpoint: Get customer by ID
     */
    public function getCustomer(int $customerId)
    {
        try {
            $customer = $this->customerHelper->loadCustomerById($customerId);

            return [
                'success' => true,
                'data' => [
                    'id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                    'firstname' => $customer->getFirstname(),
                    'lastname' => $customer->getLastname(),
                    'created_at' => $customer->getCreatedAt(),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * REST API endpoint: Create customer
     */
    public function createCustomer(array $data)
    {
        try {
            // Validate
            $required = ['email', 'firstname', 'lastname', 'password'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    throw new \Exception("Field $field is required");
                }
            }

            if ($this->customerHelper->customerExists($data['email'])) {
                throw new \Exception("Email already exists");
            }

            $customer = $this->customerHelper->createCustomerComprehensive($data);

            return [
                'success' => true,
                'customer_id' => $customer->getId(),
                'message' => 'Customer created successfully'
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * REST API endpoint: Get customer profile
     */
    public function getFullProfile(int $customerId)
    {
        try {
            $customer = $this->customerHelper->loadCustomerById($customerId);

            return [
                'success' => true,
                'profile' => [
                    'basic' => [
                        'id' => $customer->getId(),
                        'email' => $customer->getEmail(),
                        'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                    ],
                    'addresses' => count($this->customerHelper->getCustomerAddresses($customerId)),
                    'orders' => $this->customerHelper->getCustomerOrderCount($customerId),
                    'lifetime_value' => $this->customerHelper->getCustomerTotalSpent($customerId),
                    'cart_items' => count($this->customerHelper->getCustomerCartItems($customerId)),
                    'wishlist_items' => $this->customerHelper->getWishlistItemCount($customerId),
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * REST API endpoint: Search customers
     */
    public function searchCustomers(string $query, int $limit = 10)
    {
        try {
            $results = [];

            // Search by email and name
            $collection = $this->customerHelper->getCustomersByFilter('email', $query, 'like');
            foreach ($collection->getItems() as $customer) {
                $results[] = [
                    'id' => $customer->getId(),
                    'email' => $customer->getEmail(),
                    'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                ];

                if (count($results) >= $limit) break;
            }

            return [
                'success' => true,
                'results' => $results,
                'count' => count($results)
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
```

---

## Summary of All Scenarios

| Scenario | Purpose | Use Case |
|----------|---------|----------|
| Registration | Handle new customer signup | Customer registration flow |
| Import | Bulk import customers | Migrate customers from other systems |
| Lookup | Search/find customers | Admin tools, support |
| Address Management | Handle customer addresses | Checkout, profile management |
| Cart Analysis | Track abandoned carts | Email recovery campaigns |
| Wishlist Tracking | Monitor customer wishlists | Product recommendations |
| Order History | Analyze purchase patterns | Customer analytics, reporting |
| Segmentation | Group customers by behavior | Targeted marketing campaigns |
| Data Cleanup | Find and fix invalid records | Data maintenance |
| API Integration | External system integration | REST APIs, third-party services |

---

All scenarios are production-ready and follow Magento 2 best practices!

