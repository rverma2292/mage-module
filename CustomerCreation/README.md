# Magento 2 Customer Creation Module - Complete Learning Guide

This module provides a comprehensive learning resource for all Magento 2 customer-related operations.

## Module Overview

**Module Name:** `Advik_CustomerCreation`  
**Purpose:** Learn and master Magento 2 customer functionalities

## Table of Contents

1. [Creating Customers](#creating-customers)
2. [Getting Customers](#getting-customers)
3. [Loading Customers](#loading-customers)
4. [Customer Addresses](#customer-addresses)
5. [Customer Cart](#customer-cart)
6. [Customer Wishlist](#customer-wishlist)
7. [Customer Orders](#customer-orders)
8. [Advanced Operations](#advanced-operations)
9. [Running the Demo](#running-the-demo)

---

## Creating Customers

### Method 1: Basic Customer Creation

The simplest way to create a customer with minimal information.

**Use Case:** Quick customer creation when you only need basic info

```php
$customerHelper = $objectManager->get(\Advik\CustomerCreation\Helper\CustomerHelper::class);

$customer = $customerHelper->createCustomerBasic(
    'customer@example.com',    // email
    'John',                     // firstname
    'Doe',                      // lastname
    'MyPassword123',            // password
    1                           // website_id (optional)
);

echo "Created customer ID: " . $customer->getId();
```

**Parameters:**
- `email` (string): Customer email address
- `firstname` (string): Customer first name
- `lastname` (string): Customer last name
- `password` (string): Customer password
- `website_id` (int, optional): Website ID (defaults to current site)

**Returns:** `\Magento\Customer\Model\Customer` object

---

### Method 2: Comprehensive Customer Creation

Create a customer with ALL available options including addresses, attributes, and more.

**Use Case:** Complete customer setup with all details

```php
$customerData = [
    'email' => 'john.doe@example.com',
    'firstname' => 'John',
    'lastname' => 'Doe',
    'password' => 'SecurePass123',
    'website_id' => 1,
    'store_id' => 1,
    'group_id' => 1,                    // Customer group
    'dob' => '1990-01-15',               // Date of birth
    'gender' => 2,                       // 1 = Male, 2 = Female
    'prefix' => 'Mr.',                   // Name prefix
    'suffix' => 'Jr.',                   // Name suffix
    'taxvat' => '12345678',              // Tax VAT number
    'is_active' => 1,                    // 1 = Active, 0 = Inactive
    
    // Address information (optional)
    'company' => 'ACME Corp',
    'street' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'postcode' => '10001',
    'country_id' => 'US',
    'telephone' => '555-1234',
    'fax' => '555-5678',
    'address_default_billing' => true,   // Set as default billing
    'address_default_shipping' => true,  // Set as default shipping
];

$customer = $customerHelper->createCustomerComprehensive($customerData);
echo "Created customer with ID: " . $customer->getId();
```

**Key Parameters:**
- Basic Info: email, firstname, lastname, password
- Demographics: dob, gender, prefix, suffix
- Company: company, taxvat
- Address: street, city, state, postcode, country_id, telephone, fax
- Defaults: website_id, store_id, group_id, is_active

---

### Method 3: Using Customer API (Via Repository)

Create customer using the Magento Customer API - more reliable for validations.

**Use Case:** When you need API-level validation and best practices

```php
$customerData = [
    'email' => 'api@example.com',
    'firstname' => 'API',
    'lastname' => 'Customer',
    'password' => 'Password123',
    'website_id' => 1,
    'store_id' => 1,
    'group_id' => 1,
];

$customer = $customerHelper->createCustomerViaApi($customerData);
echo "Customer created via API with ID: " . $customer->getId();
```

---

### Adding Address to Existing Customer

```php
$addressData = [
    'firstname' => 'John',
    'lastname' => 'Doe',
    'company' => 'ACME',
    'street' => '123 Main Street',
    'city' => 'New York',
    'state' => 'NY',
    'postcode' => '10001',
    'country_id' => 'US',
    'telephone' => '555-1234',
    'fax' => '555-5678',
];

$address = $customerHelper->addCustomerAddress(
    $customer,              // Customer object
    $addressData,
    true,                   // Is default billing?
    true                    // Is default shipping?
);

echo "Address added with ID: " . $address->getId();
```

---

## Getting Customers

### Get All Customers (with filters)

```php
// Get all customers with pagination
$collection = $customerHelper->getAllCustomers(
    20,  // limit per page
    1    // page number
);

foreach ($collection as $customer) {
    echo "Customer: " . $customer->getEmail() . "\n";
}
```

### Get with Specific Filters

```php
// Get customers by specific criteria
$filters = [
    'email' => 'john',          // LIKE search
    'firstname' => 'John',
    'group_id' => 1,
    'created_from' => '2024-01-01',
    'created_to' => '2024-12-31',
];

$collection = $customerHelper->getAllCustomers(20, 1, $filters);
echo "Found: " . $collection->getSize() . " customers";
```

### Get Customers by Specific Criteria

```php
// Advanced filtering with different conditions
$collection = $customerHelper->getCustomersByFilter(
    'email',        // attribute name
    'example.com',  // value
    'like'          // condition: 'like', 'eq', 'gteq', 'lteq', etc.
);

foreach ($collection as $customer) {
    echo $customer->getEmail() . "\n";
}
```

### Get Active Customers

```php
$activeCustomers = $customerHelper->getActiveCustomers();
echo "Total active customers: " . $activeCustomers->getSize();

foreach ($activeCustomers as $customer) {
    echo $customer->getFirstname() . " " . $customer->getLastname() . "\n";
}
```

### Get Customers by Group

```php
$groupId = 1; // Customer group ID

$customers = $customerHelper->getCustomersByGroup($groupId);
echo "Customers in group $groupId: " . $customers->getSize();
```

### Get Recently Registered Customers

```php
// Get customers registered in last 30 days
$recentCustomers = $customerHelper->getRecentlyRegisteredCustomers(30);
echo "New customers (30 days): " . $recentCustomers->getSize();

// Get customers from last 7 days
$weekOld = $customerHelper->getRecentlyRegisteredCustomers(7);
echo "New customers (7 days): " . $weekOld->getSize();
```

### Get Customer Count

```php
$totalCount = $customerHelper->getCustomerCount();
echo "Total customers in system: " . $totalCount;
```

---

## Loading Customers

### Load Customer by ID

```php
try {
    $customer = $customerHelper->loadCustomerById(5); // Customer ID = 5
    echo "Customer found: " . $customer->getEmail();
} catch (\Exception $e) {
    echo "Customer not found";
}
```

### Load Customer by Email

```php
try {
    // Get customer by email (specific to current website)
    $customer = $customerHelper->loadCustomerByEmail('john@example.com');
    echo "Name: " . $customer->getFirstname() . " " . $customer->getLastname();
} catch (\Exception $e) {
    echo "Customer not found with this email";
}
```

### Load Customer by Custom Attribute

```php
// Load customer by any attribute (custom or standard)
$customer = $customerHelper->loadCustomerByAttribute('taxvat', '12345678');

if ($customer) {
    echo "Found customer: " . $customer->getEmail();
} else {
    echo "No customer found";
}
```

### Check if Customer Exists

```php
$exists = $customerHelper->customerExists('test@example.com');

if ($exists) {
    echo "Customer already exists";
} else {
    echo "Customer does not exist";
}
```

---

## Customer Addresses

### Get All Customer Addresses

```php
$customerId = 5;

$addresses = $customerHelper->getCustomerAddresses($customerId);
echo "Total addresses: " . count($addresses);

foreach ($addresses as $address) {
    echo "Address: " . $address->getStreet() . ", " . $address->getCity() . "\n";
}
```

### Get Default Billing Address

```php
$customerId = 5;

$billingAddress = $customerHelper->getDefaultBillingAddress($customerId);

if ($billingAddress) {
    echo "Billing Address:\n";
    echo "Name: " . $billingAddress->getFirstname() . " " . $billingAddress->getLastname() . "\n";
    echo "Street: " . $billingAddress->getStreet() . "\n";
    echo "City: " . $billingAddress->getCity() . "\n";
    echo "State: " . $billingAddress->getRegion() . "\n";
    echo "Postcode: " . $billingAddress->getPostcode() . "\n";
    echo "Country: " . $billingAddress->getCountryId() . "\n";
    echo "Phone: " . $billingAddress->getTelephone() . "\n";
} else {
    echo "No default billing address set";
}
```

### Get Default Shipping Address

```php
$customerId = 5;

$shippingAddress = $customerHelper->getDefaultShippingAddress($customerId);

if ($shippingAddress) {
    echo "Shipping Address:\n";
    echo "Company: " . $shippingAddress->getCompany() . "\n";
    echo "Street: " . $shippingAddress->getStreet() . "\n";
    echo "City: " . $shippingAddress->getCity() . "\n";
    echo "Fax: " . $shippingAddress->getFax() . "\n";
} else {
    echo "No default shipping address set";
}
```

### Get Address Details

```php
$addressId = 10;

$addressDetails = $customerHelper->getAddressDetails($addressId);

if ($addressDetails) {
    echo "Address Details:\n";
    foreach ($addressDetails as $key => $value) {
        echo "$key: $value\n";
    }
}
```

### Update Customer Address

```php
$addressId = 10;
$addressData = [
    'street' => 'New Street Address',
    'city' => 'New York',
    'postcode' => '10001',
];

$updated = $customerHelper->updateCustomerAddress($addressId, $addressData);
echo $updated ? "Address updated" : "Failed to update";
```

### Delete Customer Address

```php
$addressId = 10;

$deleted = $customerHelper->deleteCustomerAddress($addressId);
echo $deleted ? "Address deleted" : "Failed to delete";
```

---

## Customer Cart

### Get Customer Shopping Cart

```php
$customerId = 5;

$cart = $customerHelper->getCustomerCart($customerId);

if ($cart) {
    echo "Active cart found\n";
    echo "Cart ID: " . $cart->getId() . "\n";
    echo "Items in cart: " . $cart->getItemsCount() . "\n";
} else {
    echo "No active cart for this customer";
}
```

### Get Cart Items

```php
$customerId = 5;

$cartItems = $customerHelper->getCustomerCartItems($customerId);

echo "Cart has " . count($cartItems) . " items:\n";

foreach ($cartItems as $item) {
    echo "Product: " . $item['product_name'] . "\n";
    echo "SKU: " . $item['sku'] . "\n";
    echo "Quantity: " . $item['quantity'] . "\n";
    echo "Price: $" . $item['price'] . "\n";
    echo "Row Total: $" . $item['row_total'] . "\n";
    echo "---\n";
}
```

### Get Cart Totals

```php
$customerId = 5;

$totals = $customerHelper->getCartTotals($customerId);

if (!empty($totals)) {
    echo "Cart Summary:\n";
    echo "Subtotal: $" . $totals['subtotal'] . "\n";
    echo "Tax: $" . $totals['tax'] . "\n";
    echo "Shipping: $" . $totals['shipping'] . "\n";
    echo "Grand Total: $" . $totals['grand_total'] . "\n";
    echo "Item Count: " . $totals['item_count'] . "\n";
    echo "Quantity Total: " . $totals['quantity_count'] . "\n";
} else {
    echo "No cart data available";
}
```

**Possible Scenarios:**
- Customer has items in cart
- Cart is empty but exists
- Customer has no cart (new customer)
- Cart includes promotions/discounts
- Cart includes multiple products with different quantities

---

## Customer Wishlist

### Get Customer Wishlist

```php
$customerId = 5;

$wishlist = $customerHelper->getCustomerWishlist($customerId);

if ($wishlist) {
    echo "Wishlist found\n";
    echo "Wishlist ID: " . $wishlist->getId() . "\n";
} else {
    echo "No wishlist for this customer";
}
```

### Get Wishlist Items

```php
$customerId = 5;

$wishlistItems = $customerHelper->getCustomerWishlistItems($customerId);

echo "Wishlist has " . count($wishlistItems) . " items:\n";

foreach ($wishlistItems as $item) {
    echo "Product: " . $item['product_name'] . "\n";
    echo "SKU: " . $item['sku'] . "\n";
    echo "Price: $" . $item['price'] . "\n";
    echo "Quantity: " . $item['qty'] . "\n";
    echo "Added: " . $item['added_at'] . "\n";
    echo "---\n";
}
```

### Get Wishlist Item Count

```php
$customerId = 5;

$itemCount = $customerHelper->getWishlistItemCount($customerId);
echo "Wishlist items: " . $itemCount;
```

**Possible Scenarios:**
- Wishlist is empty
- Wishlist has multiple items
- Wishlist items include both available and out-of-stock products
- Wishlist items have price changes

---

## Customer Orders

### Get Customer Orders

```php
$customerId = 5;

$orders = $customerHelper->getCustomerOrders($customerId);

foreach ($orders as $order) {
    echo "Order #" . $order->getIncrementId() . "\n";
    echo "Date: " . $order->getCreatedAt() . "\n";
    echo "Status: " . $order->getStatus() . "\n";
    echo "Grand Total: $" . $order->getGrandTotal() . "\n";
    echo "---\n";
}
```

### Get Customer Order Count

```php
$customerId = 5;

$orderCount = $customerHelper->getCustomerOrderCount($customerId);
echo "Customer has placed " . $orderCount . " orders";
```

### Get Customer Lifetime Spent

```php
$customerId = 5;

$totalSpent = $customerHelper->getCustomerTotalSpent($customerId);
echo "Customer has spent: $" . $totalSpent;
```

**Possible Scenarios:**
- New customer with no orders
- Customer with multiple orders
- Customer with completed and pending orders
- Customer with refunded orders

---

## Advanced Operations

### Update Customer Information

```php
$customerId = 5;

$customerData = [
    'firstname' => 'NewFirstname',
    'lastname' => 'NewLastname',
    'dob' => '1995-05-20',
    'gender' => 1,
];

$updated = $customerHelper->updateCustomer($customerId, $customerData);
echo $updated ? "Customer updated" : "Failed to update";
```

### Delete Customer

```php
$customerId = 5;

$deleted = $customerHelper->deleteCustomer($customerId);
echo $deleted ? "Customer deleted" : "Failed to delete";
```

### Get Customer Custom Attributes

```php
$customerId = 5;

$attributes = $customerHelper->getCustomerAttributes($customerId);

foreach ($attributes as $attribute) {
    echo "Attribute: " . $attribute['attribute_code'] . "\n";
    echo "Value: " . $attribute['value'] . "\n";
}
```

### Validate Email Format

```php
$email = 'john@example.com';

$isValid = $customerHelper->isValidEmail($email);
echo $isValid ? "Valid email" : "Invalid email";
```

### Generate Customer Report

```php
$report = $customerHelper->generateCustomerReport();

echo "=== Customer Statistics ===\n";
echo "Total Customers: " . $report['total_customers'] . "\n";
echo "Active Customers: " . $report['active_customers'] . "\n";
echo "New (30 days): " . $report['recent_customers_30d'] . "\n";
echo "New (7 days): " . $report['recent_customers_7d'] . "\n";
```

---

## Running the Demo

Run the demonstration command to see all operations in action:

```bash
# From Magento root directory
php bin/magento customer:demo
```

This will execute all customer operations and show results for each scenario.

---

## File Structure

```
Advik/CustomerCreation/
├── registration.php                          # Module registration
├── etc/
│   └── module.xml                           # Module configuration
├── Helper/
│   └── CustomerHelper.php                   # Main helper with all methods
└── Console/Command/
    └── DemoCommand.php                      # Demo command for testing
```

---

## Key Takeaways

### Three Ways to Create Customers:
1. **BasicMethod** - Fastest, minimal info
2. **ComprehensiveMethod** - Full details with addresses
3. **APIMethod** - Most reliable with validations

### Four Ways to Load Customers:
1. **By ID** - Most common
2. **By Email** - When you only have email
3. **By Custom Attribute** - Flexible filtering
4. **Check existence** - Before operations

### Customer Data You Can Retrieve:
- **Personal:** Name, email, DOB, gender, phone
- **Addresses:** Billing, shipping, all addresses
- **Shopping:** Cart items, cart totals, wishlist
- **Orders:** Order list, count, lifetime spent
- **Custom:** Any custom attributes

### Collections vs Direct Loading:
- **Collections:** Get multiple customers, filtering, pagination
- **Direct Loading:** Get single customer, more complete data

---

## Common Use Cases

| Use Case | Method |
|----------|--------|
| Register new customer | `createCustomerBasic()` or `createCustomerComprehensive()` |
| Find customer by email | `loadCustomerByEmail()` |
| Get all customers | `getAllCustomers()` |
| Customer has defaultaddresses | `getDefaultBillingAddress()`, `getDefaultShippingAddress()` |
| Display customer cart | `getCustomerCart()`, `getCustomerCartItems()` |
| Show wishlist | `getCustomerWishlist()`, `getCustomerWishlistItems()` |
| Customer statistics | `generateCustomerReport()` |
| Update profile | `updateCustomer()` |

---

## Best Practices

1. **Always validate email** before creating customer
2. **Check if customer exists** to avoid duplicates
3. **Use try/catch** blocks for error handling
4. **Use collections** when you need multiple records
5. **Use API methods** for complex operations
6. **Set default addresses** when creating customers
7. **Paginate collections** for large datasets
8. **Clear cache** after customer updates

---

## Resources

- Magento 2 Customer API: `Magento\Customer\Api\CustomerRepositoryInterface`
- Magento 2 Customer Model: `Magento\Customer\Model\Customer`
- Magento 2 Address API: `Magento\Customer\Api\AddressRepositoryInterface`
- Quote (Cart) Model: `Magento\Quote\Model\Quote`
- Wishlist Model: `Magento\Wishlist\Model\Wishlist`
- Order Model: `Magento\Sales\Model\Order`

---

## Notes

- This module is for **learning purposes only**
- All methods include comprehensive documentation
- Examples are ready to use in your own modules
- Extend these methods for your custom needs
- Follow Magento 2 best practices when using these examples

---

**Module Version:** 1.0  
**Magento Version:** 2.4.x+  
**Last Updated:** 2024

