<?php
/**
 * Real-world usage examples for Magento 2 Customer Operations
 *
 * This file demonstrates practical scenarios you might encounter
 * in production environments.
 */

declare(strict_types=1);

namespace Advik\CustomerCreation\Examples;

use Advik\CustomerCreation\Helper\CustomerHelper;

/**
 * Example 1: Bulk Customer Import
 *
 * Scenario: Import 1000 customers from CSV file
 */
class BulkCustomerImportExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function importCustomersFromCsv(string $csvFile): void
    {
        $handle = fopen($csvFile, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            try {
                // Check if customer already exists
                if ($this->customerHelper->customerExists($data['email'])) {
                    echo "Skipping existing customer: " . $data['email'] . "\n";
                    continue;
                }

                // Create customer
                $customer = $this->customerHelper->createCustomerComprehensive([
                    'email' => $data['email'],
                    'firstname' => $data['first_name'],
                    'lastname' => $data['last_name'],
                    'password' => $data['password'],
                    'website_id' => 1,
                    'store_id' => 1,
                    'group_id' => 1,
                    'telephone' => $data['phone'] ?? '',
                    'company' => $data['company'] ?? '',
                    'street' => $data['street'] ?? '',
                    'city' => $data['city'] ?? '',
                    'state' => $data['state'] ?? '',
                    'postcode' => $data['postcode'] ?? '',
                    'country_id' => $data['country'] ?? 'US',
                ]);

                echo "✓ Created customer: " . $data['email'] . " (ID: " . $customer->getId() . ")\n";

            } catch (\Exception $e) {
                echo "✗ Error creating customer: " . $data['email'] . " - " . $e->getMessage() . "\n";
            }
        }

        fclose($handle);
    }
}

/**
 * Example 2: Customer Segment Analysis
 *
 * Scenario: Analyze customer segments for marketing campaign
 */
class CustomerSegmentAnalysisExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function analyzeCustomers(): array
    {
        $analysis = [
            'high_value' => [],        // Spent > $1000
            'regular' => [],           // Spent $100-$1000
            'emerging' => [],          // Spent < $100
            'new' => [],              // Registered in last 7 days
            'inactive' => [],         // No purchases
        ];

        $collection = $this->customerHelper->getAllCustomers(1000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $totalSpent = $this->customerHelper->getCustomerTotalSpent($customerId);
            $orderCount = $this->customerHelper->getCustomerOrderCount($customerId);

            // Categorize customers
            if ($totalSpent > 1000) {
                $analysis['high_value'][] = [
                    'id' => $customerId,
                    'email' => $customer->getEmail(),
                    'spent' => $totalSpent,
                    'orders' => $orderCount,
                ];
            } elseif ($totalSpent > 100) {
                $analysis['regular'][] = $customer->getEmail();
            } elseif ($totalSpent > 0) {
                $analysis['emerging'][] = $customer->getEmail();
            } else {
                $analysis['inactive'][] = $customer->getEmail();
            }
        }

        // Get new customers
        $newCustomers = $this->customerHelper->getRecentlyRegisteredCustomers(7);
        foreach ($newCustomers as $customer) {
            $analysis['new'][] = $customer->getEmail();
        }

        return $analysis;
    }
}

/**
 * Example 3: Customer Cleanup Tool
 *
 * Scenario: Find and manage inactive customers
 */
class CustomerCleanupExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function findInactiveCustomers(int $inactiveDays = 180): array
    {
        $inactiveCustomers = [];

        $collection = $this->customerHelper->getAllCustomers(1000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $orderCount = $this->customerHelper->getCustomerOrderCount($customerId);

            // If customer has no orders and hasn't been active, mark as inactive
            if ($orderCount == 0) {
                $createdAt = strtotime($customer->getCreatedAt());
                $daysSinceCreation = floor((time() - $createdAt) / (24 * 60 * 60));

                if ($daysSinceCreation > $inactiveDays) {
                    $inactiveCustomers[] = [
                        'id' => $customerId,
                        'email' => $customer->getEmail(),
                        'created' => $customer->getCreatedAt(),
                        'days_inactive' => $daysSinceCreation,
                    ];
                }
            }
        }

        return $inactiveCustomers;
    }

    public function deleteInactiveCustomers(array $customerIds): void
    {
        foreach ($customerIds as $customerId) {
            try {
                if ($this->customerHelper->deleteCustomer($customerId)) {
                    echo "✓ Deleted inactive customer ID: $customerId\n";
                }
            } catch (\Exception $e) {
                echo "✗ Error deleting customer: " . $e->getMessage() . "\n";
            }
        }
    }
}

/**
 * Example 4: Customer Duplicate Detection & Merge
 *
 * Scenario: Find customers with similar emails (duplicates)
 */
class CustomerDuplicateDetectionExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function findPossibleDuplicates(): array
    {
        $duplicates = [];
        $emailDomains = [];

        $collection = $this->customerHelper->getAllCustomers(1000);

        foreach ($collection as $customer) {
            $email = $customer->getEmail();
            list($name, $domain) = explode('@', $email);

            // Find similar emails
            if (!isset($emailDomains[$domain])) {
                $emailDomains[$domain] = [];
            }

            $emailDomains[$domain][$name] = [
                'id' => $customer->getId(),
                'email' => $email,
                'firstname' => $customer->getFirstname(),
                'lastname' => $customer->getLastname(),
            ];
        }

        // Find near-duplicates (similar names on same domain)
        foreach ($emailDomains as $domain => $emails) {
            if (count($emails) > 1) {
                foreach ($emails as $name => $info) {
                    $similar = [];
                    foreach ($emails as $otherName => $otherInfo) {
                        if ($name !== $otherName) {
                            // Simple similarity check
                            $similarity = levenshtein($name, $otherName);
                            if ($similarity < 3) { // Very similar
                                $similar[] = $otherInfo;
                            }
                        }
                    }

                    if (!empty($similar)) {
                        $duplicates[] = [
                            'primary' => $info,
                            'similar' => $similar,
                        ];
                    }
                }
            }
        }

        return $duplicates;
    }
}

/**
 * Example 5: Customer Communication Targeting
 *
 * Scenario: Find customers for targeted email campaigns
 */
class CustomerCommunicationTargetingExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function findTargetCustomersForCampaign(string $campaignType): array
    {
        $targets = [];

        switch ($campaignType) {
            case 'abandoned_cart':
                // Customers with items in cart but haven't ordered
                $collection = $this->customerHelper->getAllCustomers(1000);
                foreach ($collection as $customer) {
                    $cartItems = $this->customerHelper->getCustomerCartItems($customer->getId());
                    if (!empty($cartItems) && $this->customerHelper->getCustomerOrderCount($customer->getId()) === 0) {
                        $targets[] = [
                            'id' => $customer->getId(),
                            'email' => $customer->getEmail(),
                            'items_in_cart' => count($cartItems),
                        ];
                    }
                }
                break;

            case 'wishlist_interested':
                // Customers with wishlist items
                $collection = $this->customerHelper->getAllCustomers(1000);
                foreach ($collection as $customer) {
                    $wishlistCount = $this->customerHelper->getWishlistItemCount($customer->getId());
                    if ($wishlistCount > 0) {
                        $targets[] = [
                            'id' => $customer->getId(),
                            'email' => $customer->getEmail(),
                            'wishlist_items' => $wishlistCount,
                        ];
                    }
                }
                break;

            case 'vip_customers':
                // High-value customers
                $collection = $this->customerHelper->getAllCustomers(1000);
                foreach ($collection as $customer) {
                    $spent = $this->customerHelper->getCustomerTotalSpent($customer->getId());
                    if ($spent > 1000) {
                        $targets[] = [
                            'id' => $customer->getId(),
                            'email' => $customer->getEmail(),
                            'lifetime_value' => $spent,
                        ];
                    }
                }
                break;

            case 'new_customers':
                // Customers from last 30 days
                $collection = $this->customerHelper->getRecentlyRegisteredCustomers(30);
                foreach ($collection as $customer) {
                    $targets[] = [
                        'id' => $customer->getId(),
                        'email' => $customer->getEmail(),
                        'joined' => $customer->getCreatedAt(),
                    ];
                }
                break;
        }

        return $targets;
    }
}

/**
 * Example 6: Customer Data Export
 *
 * Scenario: Export customer data for reporting/backup
 */
class CustomerDataExportExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function exportCustomerDataToCsv(string $outputFile): void
    {
        $fp = fopen($outputFile, 'w');

        // Write header
        fputcsv($fp, [
            'ID', 'Email', 'First Name', 'Last Name', 'Phone', 'Company',
            'Orders', 'Lifetime Spent', 'Created At', 'Billing Address', 'Shipping Address'
        ]);

        $collection = $this->customerHelper->getAllCustomers(10000);

        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $billingAddress = $this->customerHelper->getDefaultBillingAddress($customerId);
            $shippingAddress = $this->customerHelper->getDefaultShippingAddress($customerId);

            fputcsv($fp, [
                $customerId,
                $customer->getEmail(),
                $customer->getFirstname(),
                $customer->getLastname(),
                $customer->getPrimaryBillingAddress() ? $customer->getPrimaryBillingAddress()->getTelephone() : '',
                $customer->getPrimaryBillingAddress() ? $customer->getPrimaryBillingAddress()->getCompany() : '',
                $this->customerHelper->getCustomerOrderCount($customerId),
                $this->customerHelper->getCustomerTotalSpent($customerId),
                $customer->getCreatedAt(),
                $billingAddress ? $billingAddress->getCity() : '',
                $shippingAddress ? $shippingAddress->getCity() : '',
            ]);
        }

        fclose($fp);
        echo "✓ Customer data exported to: $outputFile\n";
    }
}

/**
 * Example 7: Customer Validation & Verification
 *
 * Scenario: Verify customer data integrity
 */
class CustomerValidationExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function validateCustomerData(int $customerId): array
    {
        $validation = [
            'valid' => true,
            'errors' => [],
            'warnings' => [],
        ];

        try {
            $customer = $this->customerHelper->loadCustomerById($customerId);

            // Check required fields
            if (empty($customer->getEmail())) {
                $validation['errors'][] = 'Email is missing';
                $validation['valid'] = false;
            } elseif (!$this->customerHelper->isValidEmail($customer->getEmail())) {
                $validation['errors'][] = 'Email format is invalid';
                $validation['valid'] = false;
            }

            if (empty($customer->getFirstname())) {
                $validation['errors'][] = 'First name is missing';
                $validation['valid'] = false;
            }

            if (empty($customer->getLastname())) {
                $validation['errors'][] = 'Last name is missing';
                $validation['valid'] = false;
            }

            // Check for addresses
            $addresses = $this->customerHelper->getCustomerAddresses($customerId);
            if (empty($addresses)) {
                $validation['warnings'][] = 'Customer has no addresses';
            }

            // Check for orders
            $orderCount = $this->customerHelper->getCustomerOrderCount($customerId);
            if ($orderCount === 0) {
                $validation['warnings'][] = 'Customer has no orders (new customer)';
            }

            // Check for inactive cart
            $cart = $this->customerHelper->getCustomerCart($customerId);
            if ($cart && $cart->getItemsCount() > 0) {
                $validation['warnings'][] = 'Customer has items in cart';
            }

        } catch (\Exception $e) {
            $validation['valid'] = false;
            $validation['errors'][] = 'Error loading customer: ' . $e->getMessage();
        }

        return $validation;
    }
}

/**
 * Example 8: Customer Address Validation & Management
 *
 * Scenario: Ensure all customers have proper addresses
 */
class CustomerAddressManagementExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function ensureCustomerHasAddresses(int $customerId): bool
    {
        try {
            $addresses = $this->customerHelper->getCustomerAddresses($customerId);

            if (empty($addresses)) {
                // Add a default address
                $customer = $this->customerHelper->loadCustomerById($customerId);
                $this->customerHelper->addCustomerAddress(
                    $customer,
                    [
                        'firstname' => $customer->getFirstname(),
                        'lastname' => $customer->getLastname(),
                        'street' => '123 Default Street',
                        'city' => 'City',
                        'state' => 'State',
                        'postcode' => '12345',
                        'country_id' => 'US',
                        'telephone' => '555-0000',
                    ],
                    true,  // Default billing
                    true   // Default shipping
                );
                return true;
            }

            // Check if default addresses are set
            $billingAddress = $this->customerHelper->getDefaultBillingAddress($customerId);
            $shippingAddress = $this->customerHelper->getDefaultShippingAddress($customerId);

            if (!$billingAddress || !$shippingAddress) {
                // First address becomes default
                $firstAddress = reset($addresses);
                $this->customerHelper->updateCustomerAddress($firstAddress->getId(), [
                    'is_default_billing' => 1,
                    'is_default_shipping' => 1,
                ]);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function consolidateAddresses(int $customerId): int
    {
        $addresses = $this->customerHelper->getCustomerAddresses($customerId);
        $deleted = 0;

        // Keep only the first two addresses (billing and shipping)
        foreach (array_slice($addresses, 2) as $address) {
            if ($this->customerHelper->deleteCustomerAddress($address->getId())) {
                $deleted++;
            }
        }

        return $deleted;
    }
}

/**
 * Example 9: Monthly Customer Growth Report
 *
 * Scenario: Generate monthly statistics
 */
class MonthlyCustomerReportExample
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    public function generateMonthlyReport(string $month = 'current'): array
    {
        if ($month === 'current') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } else {
            $startDate = date('Y-m-01', strtotime($month));
            $endDate = date('Y-m-t', strtotime($month));
        }

        $filters = [
            'created_from' => $startDate,
            'created_to' => $endDate,
        ];

        $collection = $this->customerHelper->getAllCustomers(10000, 1, $filters);

        $report = [
            'period' => "$startDate to $endDate",
            'new_customers' => $collection->getSize(),
            'by_group' => [],
            'by_store' => [],
            'total_customers' => $this->customerHelper->getCustomerCount(),
        ];

        foreach ($collection as $customer) {
            $groupId = $customer->getGroupId();
            if (!isset($report['by_group'][$groupId])) {
                $report['by_group'][$groupId] = 0;
            }
            $report['by_group'][$groupId]++;
        }

        return $report;
    }
}

