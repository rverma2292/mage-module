# Customer Module - Usage Guide

This comprehensive guide shows how to use the `Advik_CustomerCreation` module in various scenarios and contexts.

## Table of Contents

1. [Installation & Setup](#installation--setup)
2. [Using in Controllers](#using-in-controllers)
3. [Using in Models](#using-in-models)
4. [Using in Console Commands](#using-in-console-commands)
5. [Using in Cron Jobs](#using-in-cron-jobs)
6. [Using in Observers](#using-in-observers)
7. [Using in Plugins](#using-in-plugins)
8. [Best Practices](#best-practices)
9. [Common Patterns](#common-patterns)
10. [Troubleshooting](#troubleshooting)

---

## Installation & Setup

### 1. Enable the Module

First, ensure your module directory exists and module files are in place:

```bash
app/code/Advik/CustomerCreation/
├── registration.php
├── etc/
│   ├── module.xml
│   └── di.xml
├── Helper/
│   └── CustomerHelper.php
├── Console/
│   └── Command/
│       └── DemoCommand.php
├── Examples/
│   └── RealWorldExamples.php
└── README.md
```

### 2. Run Setup Commands

```bash
# Enable the module
php bin/magento module:enable Advik_CustomerCreation

# Run setup upgrade
php bin/magento setup:upgrade

# Compile (required for production)
php bin/magento setup:di:compile

# Flush cache
php bin/magento cache:flush
```

### 3. Verify Installation

```bash
# Check if module is enabled
php bin/magento module:status | grep Advik_CustomerCreation

# Check if command is available
php bin/magento list | grep customer:demo
```

---

## Using in Controllers

### Basic Controller Example

Create a new controller to handle customer operations:

```php
<?php
declare(strict_types=1);

namespace Advik\CustomerCreation\Controller\Customer;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class Create extends Action
{
    private $customerHelper;
    private $jsonFactory;

    public function __construct(
        Context $context,
        CustomerHelper $customerHelper,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->customerHelper = $customerHelper;
        $this->jsonFactory = $jsonFactory;
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();

        try {
            $post = $this->getRequest()->getPostValue();

            // Validate email
            if (!$this->customerHelper->isValidEmail($post['email'] ?? '')) {
                throw new LocalizedException(__('Invalid email address'));
            }

            // Check if customer already exists
            if ($this->customerHelper->customerExists($post['email'])) {
                throw new LocalizedException(__('Customer already exists'));
            }

            // Create customer
            $customer = $this->customerHelper->createCustomerComprehensive([
                'email' => $post['email'],
                'firstname' => $post['firstname'],
                'lastname' => $post['lastname'],
                'password' => $post['password'],
                'telephone' => $post['telephone'] ?? '',
                'company' => $post['company'] ?? '',
                'street' => $post['street'] ?? '',
                'city' => $post['city'] ?? '',
                'state' => $post['region'] ?? '',
                'postcode' => $post['postcode'] ?? '',
                'country_id' => $post['country_id'] ?? 'US',
                'address_default_billing' => true,
                'address_default_shipping' => true,
            ]);

            $result->setData([
                'success' => true,
                'message' => 'Customer created successfully',
                'customer_id' => $customer->getId(),
            ]);

        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        return $result;
    }
}
```

### Get Customer Data in Controller

```php
public function execute()
{
    $customerId = $this->getRequest()->getParam('customer_id');

    try {
        $customer = $this->customerHelper->loadCustomerById($customerId);
        $addresses = $this->customerHelper->getCustomerAddresses($customerId);
        $cart = $this->customerHelper->getCustomerCart($customerId);
        $orders = $this->customerHelper->getCustomerOrders($customerId);

        $result = $this->jsonFactory->create();
        $result->setData([
            'customer' => [
                'id' => $customer->getId(),
                'email' => $customer->getEmail(),
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
            ],
            'addresses' => count($addresses),
            'cart_items' => $cart ? $cart->getItemsCount() : 0,
            'orders' => $orders->getSize(),
        ]);

        return $result;

    } catch (\Exception $e) {
        // Handle error
    }
}
```

---

## Using in Models

### Customer Model with Helper

Create a custom model that extends the helper:

```php
<?php
declare(strict_types=1);

namespace Advik\CustomerCreation\Model;

use Advik\CustomerCreation\Helper\CustomerHelper;

class CustomerManager
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Bulk register customers
     */
    public function bulkRegisterCustomers(array $customersData): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($customersData as $data) {
            try {
                if ($this->customerHelper->customerExists($data['email'])) {
                    $results['failed'][] = [
                        'email' => $data['email'],
                        'reason' => 'Customer already exists',
                    ];
                    continue;
                }

                $customer = $this->customerHelper->createCustomerComprehensive($data);
                $results['success'][] = $customer->getId();

            } catch (\Exception $e) {
                $results['failed'][] = [
                    'email' => $data['email'],
                    'reason' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get customer summary
     */
    public function getCustomerSummary(int $customerId): array
    {
        try {
            $customer = $this->customerHelper->loadCustomerById($customerId);

            return [
                'id' => $customerId,
                'email' => $customer->getEmail(),
                'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
                'created_at' => $customer->getCreatedAt(),
                'addresses_count' => count($this->customerHelper->getCustomerAddresses($customerId)),
                'orders_count' => $this->customerHelper->getCustomerOrderCount($customerId),
                'total_spent' => $this->customerHelper->getCustomerTotalSpent($customerId),
                'cart_items' => count($this->customerHelper->getCustomerCartItems($customerId)),
                'wishlist_items' => $this->customerHelper->getWishlistItemCount($customerId),
            ];

        } catch (\Exception $e) {
            return [];
        }
    }
}
```

---

## Using in Console Commands

### Demo Command (Already Provided)

Run the built-in demo command:

```bash
php bin/magento customer:demo
```

### Create Your Own Command

```php
<?php
declare(strict_types=1);

namespace Advik\CustomerCreation\Console\Command;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindByEmailCommand extends Command
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        parent::__construct();
        $this->customerHelper = $customerHelper;
    }

    protected function configure()
    {
        $this->setName('customer:find-by-email');
        $this->setDescription('Find customer by email');
        $this->addArgument('email', InputArgument::REQUIRED, 'Customer email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');

        try {
            $customer = $this->customerHelper->loadCustomerByEmail($email);
            
            $output->writeln("<info>Customer Found:</info>");
            $output->writeln("ID: " . $customer->getId());
            $output->writeln("Email: " . $customer->getEmail());
            $output->writeln("Name: " . $customer->getFirstname() . " " . $customer->getLastname());
            $output->writeln("Created: " . $customer->getCreatedAt());

            return 0;

        } catch (\Exception $e) {
            $output->writeln("<error>Error: " . $e->getMessage() . "</error>");
            return 1;
        }
    }
}
```

Run your custom command:

```bash
php bin/magento customer:find-by-email john@example.com
```

---

## Using in Cron Jobs

### Create a Cron Model

```php
<?php
declare(strict_types=1);

namespace Advik\CustomerCreation\Cron;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Psr\Log\LoggerInterface;

class CleanupInactiveCustomers
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
     * Daily job to remove inactive customers
     */
    public function execute()
    {
        try {
            // Get customers with no orders from last 180 days
            $collection = $this->customerHelper->getRecentlyRegisteredCustomers(180);
            
            $deleted = 0;
            foreach ($collection as $customer) {
                if ($this->customerHelper->getCustomerOrderCount($customer->getId()) === 0) {
                    if ($this->customerHelper->deleteCustomer($customer->getId())) {
                        $deleted++;
                    }
                }
            }

            $this->logger->info("CustomerCreation: Deleted $deleted inactive customers");
            return $this;

        } catch (\Exception $e) {
            $this->logger->error("CustomerCreation Cron Error: " . $e->getMessage());
            return $this;
        }
    }
}
```

### Register Cron in crontab.xml

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Cron:etc/crontab.xsd">
    <group id="default">
        <job name="advik_cleanup_inactive_customers" instance="Advik\CustomerCreation\Cron\CleanupInactiveCustomers" method="execute">
            <schedule>0 2 * * *</schedule>
        </job>
    </group>
</config>
```

Run cron jobs:

```bash
# Run cron
php bin/magento cron:run

# Schedule cron (for linux)
*/1 * * * * cd /var/www/html/mage && php bin/magento cron:run >> /var/log/mage_cron.log
```

---

## Using in Observers

### Observer for Customer Creation Events

```php
<?php
declare(strict_types=1);

namespace Advik\CustomerCreation\Observer;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;

class AfterCustomerCreateObserver implements ObserverInterface
{
    private $logger;
    private $customerHelper;

    public function __construct(
        LoggerInterface $logger,
        CustomerHelper $customerHelper
    ) {
        $this->logger = $logger;
        $this->customerHelper = $customerHelper;
    }

    public function execute(Observer $observer)
    {
        try {
            $customer = $observer->getEvent()->getCustomer();

            // Log customer creation
            $this->logger->info("Customer created: " . $customer->getEmail());

            // Ensure customer has default address
            $addresses = $this->customerHelper->getCustomerAddresses($customer->getId());
            if (empty($addresses)) {
                $this->customerHelper->addCustomerAddress(
                    $customer,
                    [
                        'firstname' => $customer->getFirstname(),
                        'lastname' => $customer->getLastname(),
                        'street' => 'TBD',
                        'city' => 'TBD',
                        'state' => 'TBD',
                        'postcode' => '00000',
                        'country_id' => 'US',
                    ],
                    true,
                    true
                );
                $this->logger->info("Default address added for customer: " . $customer->getId());
            }

            return $this;

        } catch (\Exception $e) {
            $this->logger->error("Error in Customer Observer: " . $e->getMessage());
            return $this;
        }
    }
}
```

### Register Observer in events.xml

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    <event name="customer_register_success">
        <observer name="advik_customerhelper_register_success" 
                  instance="Advik\CustomerCreation\Observer\AfterCustomerCreateObserver"/>
    </event>
</config>
```

---

## Using in Plugins

### Customer Management Plugin

```php
<?php
declare(strict_types=1);

namespace Advik\CustomerCreation\Plugin;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Magento\Customer\Model\Customer;

class CustomerValidationPlugin
{
    private $customerHelper;

    public function __construct(CustomerHelper $customerHelper)
    {
        $this->customerHelper = $customerHelper;
    }

    /**
     * Before save validation
     */
    public function beforeSave(Customer $subject)
    {
        // Validate email
        if ($subject->isObjectNew()) {
            $email = $subject->getEmail();
            if ($this->customerHelper->customerExists($email)) {
                throw new \Exception("Email already exists: $email");
            }
        }

        return [$subject];
    }

    /**
     * After save logging
     */
    public function afterSave(Customer $subject, $result)
    {
        // Log customer operations
        error_log("Customer saved: " . $subject->getEmail());
        return $result;
    }
}
```

### Register Plugin in di.xml

```xml
<type name="Magento\Customer\Model\Customer">
    <plugin name="advik_customer_validation" 
            type="Advik\CustomerCreation\Plugin\CustomerValidationPlugin"/>
</type>
```

---

## Best Practices

### 1. Always Validate Email

```php
if (!$this->customerHelper->isValidEmail($email)) {
    // Invalid email
}
```

### 2. Use Try-Catch for Error Handling

```php
try {
    $customer = $this->customerHelper->loadCustomerById($id);
} catch (\Exception $e) {
    $this->logger->error("Error: " . $e->getMessage());
    // Handle error gracefully
}
```

### 3. Check If Customer Exists Before Creating

```php
if (!$this->customerHelper->customerExists($email)) {
    $customer = $this->customerHelper->createCustomerBasic(...);
}
```

### 4. Use Collections for Multiple Customers

```php
// Good - efficient
$collection = $this->customerHelper->getAllCustomers(100);

// Avoid - inefficient
$count = 10000;
for ($i = 1; $i <= $count; $i++) {
    $this->customerHelper->loadCustomerById($i);
}
```

### 5. Implement Pagination for Large Data Sets

```php
$page = 1;
do {
    $collection = $this->customerHelper->getAllCustomers(100, $page);
    
    foreach ($collection as $customer) {
        // Process customer
    }
    
    if ($collection->getSize() < 100) {
        break; // Last page
    }
    $page++;
} while (true);
```

### 6. Log Important Operations

```php
try {
    $customer = $this->customerHelper->createCustomerComprehensive($data);
    $this->logger->info("Customer created: " . $customer->getId());
} catch (\Exception $e) {
    $this->logger->error("Failed to create customer: " . $e->getMessage());
}
```

### 7. Use Transactions for Bulk Operations

```php
// Start transaction
try {
    foreach ($customersData as $data) {
        $this->customerHelper->createCustomerComprehensive($data);
    }
    // Commit
} catch (\Exception $e) {
    // Rollback
    $this->logger->error("Bulk operation failed: " . $e->getMessage());
}
```

### 8. Cache Results When Appropriate

```php
$cacheKey = 'customer_' . $customerId;
if (!$this->cache->load($cacheKey)) {
    $customer = $this->customerHelper->loadCustomerById($customerId);
    $this->cache->save(serialize($customer), $cacheKey, [], 3600);
} else {
    $customer = unserialize($this->cache->load($cacheKey));
}
```

---

## Common Patterns

### Pattern 1: Customer Registration with Validation

```php
public function registerNewCustomer(array $data): \Magento\Customer\Model\Customer
{
    // Validate
    if (!$this->customerHelper->isValidEmail($data['email'])) {
        throw new \Exception("Invalid email");
    }

    if ($this->customerHelper->customerExists($data['email'])) {
        throw new \Exception("Email already registered");
    }

    if (strlen($data['password']) < 8) {
        throw new \Exception("Password too short");
    }

    // Create
    return $this->customerHelper->createCustomerComprehensive($data);
}
```

### Pattern 2: Customer Data Enrichment

```php
public function enrichCustomerData(int $customerId): array
{
    $customer = $this->customerHelper->loadCustomerById($customerId);

    return [
        'basic_info' => [
            'id' => $customer->getId(),
            'email' => $customer->getEmail(),
            'name' => $customer->getFirstname() . ' ' . $customer->getLastname(),
        ],
        'addresses' => $this->customerHelper->getCustomerAddresses($customerId),
        'billing_address' => $this->customerHelper->getDefaultBillingAddress($customerId),
        'shopping_data' => [
            'orders' => $this->customerHelper->getCustomerOrderCount($customerId),
            'spent' => $this->customerHelper->getCustomerTotalSpent($customerId),
            'cart_items' => count($this->customerHelper->getCustomerCartItems($customerId)),
            'wishlist_items' => $this->customerHelper->getWishlistItemCount($customerId),
        ],
    ];
}
```

### Pattern 3: Customer Segmentation

```php
public function segmentCustomers(): array
{
    $segments = [
        'vip' => [],
        'regular' => [],
        'new' => [],
    ];

    $collection = $this->customerHelper->getAllCustomers(1000);

    foreach ($collection as $customer) {
        $spent = $this->customerHelper->getCustomerTotalSpent($customer->getId());
        
        if ($spent > 1000) {
            $segments['vip'][] = $customer;
        } elseif ($spent > 0) {
            $segments['regular'][] = $customer;
        } else {
            $segments['new'][] = $customer;
        }
    }

    return $segments;
}
```

---

## Troubleshooting

### Issue: "Customer not found"

**Solution:** Check if customer ID exists

```php
try {
    $customer = $this->customerHelper->loadCustomerById(999);
} catch (\Exception $e) {
    // Customer ID 999 doesn't exist
}
```

### Issue: "Email already exists"

**Solution:** Validate before creating

```php
if (!$this->customerHelper->customerExists($email)) {
    $customer = $this->customerHelper->createCustomerBasic(...);
} else {
    // Customer already exists
}
```

### Issue: "Address not found"

**Solution:** Check if customer has addresses

```php
$addresses = $this->customerHelper->getCustomerAddresses($customerId);
if (empty($addresses)) {
    // Add default address
    $this->customerHelper->addCustomerAddress($customer, $data, true, true);
}
```

### Issue: "Cart is empty/not found"

**Solution:** New customer or cart doesn't exist

```php
$cart = $this->customerHelper->getCustomerCart($customerId);
if (!$cart) {
    // No active cart
} else {
    $items = $cart->getAllVisibleItems();
}
```

### Issue: "Command not found"

**Solution:** Run setup commands

```bash
php bin/magento module:enable Advik_CustomerCreation
php bin/magento setup:upgrade
php bin/magento cache:flush
```

### Issue: "DI compilation error"

**Solution:** Recompile DI

```bash
rm -rf generated/
php bin/magento setup:di:compile
php bin/magento cache:flush
```

---

## Performance Tips

### Use Batch Processing

```php
$page = 1;
while (true) {
    $collection = $this->customerHelper->getAllCustomers(1000, $page++);
    if ($collection->getSize() === 0) break;

    // Process 1000 customers at a time
    foreach ($collection as $customer) {
        // Do something
    }
}
```

### Enable Query Caching

```php
$collection = $this->customerHelper->getAllCustomers();
$collection->addFieldToSelect('*');
$collection->setOrder('entity_id', 'desc');
$collection->getSelect()->limit(100);
```

### Use Indexes

Ensure database indexes are properly configured:

```bash
php bin/magento indexer:status
php bin/magento indexer:reindex
```

---

This completes the usage guide. For more examples, refer to the README.md and RealWorldExamples.php files.

