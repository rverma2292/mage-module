# Magento 2 Customer Helper - Quick Reference

This is a quick reference guide for the most commonly used methods in the CustomerHelper.

## Getting the Helper

In any class using Dependency Injection:

```php
use Advik\CustomerCreation\Helper\CustomerHelper;

public function __construct(CustomerHelper $customerHelper)
{
    $this->customerHelper = $customerHelper;
}
```

---

## CUSTOMER CREATION

### Basic Creation
```php
$customer = $this->customerHelper->createCustomerBasic(
    'email@example.com',
    'John',
    'Doe',
    'Password123'
);
```

### Full Creation
```php
$customer = $this->customerHelper->createCustomerComprehensive([
    'email' => 'john@example.com',
    'firstname' => 'John',
    'lastname' => 'Doe',
    'password' => 'Pass123',
    'dob' => '1990-01-15',
    'gender' => 2, // 1=Male, 2=Female
    'company' => 'ACME',
    'street' => '123 Main',
    'city' => 'NYC',
    'state' => 'NY',
    'postcode' => '10001',
    'country_id' => 'US',
    'telephone' => '555-1234',
    'address_default_billing' => true,
    'address_default_shipping' => true,
]);
```

---

## LOADING CUSTOMERS

### By ID
```php
$customer = $this->customerHelper->loadCustomerById(5);
```

### By Email
```php
$customer = $this->customerHelper->loadCustomerByEmail('john@example.com');
```

### By Attribute
```php
$customer = $this->customerHelper->loadCustomerByAttribute('taxvat', '12345');
```

### Check Exists
```php
if ($this->customerHelper->customerExists('john@example.com')) {
    // Customer exists
}
```

---

## GETTING CUSTOMERS (COLLECTIONS)

### All Customers
```php
$collection = $this->customerHelper->getAllCustomers(20, 1); // limit, page
foreach ($collection as $customer) {
    echo $customer->getEmail();
}
```

### With Filters
```php
$collection = $this->customerHelper->getAllCustomers(20, 1, [
    'email' => 'john',
    'firstname' => 'John',
    'group_id' => 1,
    'created_from' => '2024-01-01',
    'created_to' => '2024-12-31',
]);
```

### By Filter
```php
$collection = $this->customerHelper->getCustomersByFilter(
    'email',        // attribute
    'example.com',  // value
    'like'          // condition
);
```

### Active Customers
```php
$collection = $this->customerHelper->getActiveCustomers();
```

### By Group
```php
$collection = $this->customerHelper->getCustomersByGroup(1);
```

### Recent Customers
```php
$collection = $this->customerHelper->getRecentlyRegisteredCustomers(30); // days
```

### Count
```php
$count = $this->customerHelper->getCustomerCount();
```

---

## ADDRESSES

### Get All Addresses
```php
$addresses = $this->customerHelper->getCustomerAddresses($customerId);
```

### Get Default Billing
```php
$address = $this->customerHelper->getDefaultBillingAddress($customerId);
if ($address) {
    echo $address->getCity();
}
```

### Get Default Shipping
```php
$address = $this->customerHelper->getDefaultShippingAddress($customerId);
```

### Get Address Details
```php
$details = $this->customerHelper->getAddressDetails($addressId);
// Returns array: id, firstname, lastname, company, street, city, etc.
```

### Add Address
```php
$address = $this->customerHelper->addCustomerAddress(
    $customer,
    ['street' => '123 Main', 'city' => 'NYC', ...],
    true,  // is_default_billing
    true   // is_default_shipping
);
```

### Update Address
```php
$this->customerHelper->updateCustomerAddress($addressId, [
    'city' => 'New York',
    'postcode' => '10001',
]);
```

### Delete Address
```php
$this->customerHelper->deleteCustomerAddress($addressId);
```

---

## CART

### Get Cart
```php
$cart = $this->customerHelper->getCustomerCart($customerId);
if ($cart) {
    echo $cart->getItemsCount();
}
```

### Get Cart Items
```php
$items = $this->customerHelper->getCustomerCartItems($customerId);
foreach ($items as $item) {
    echo $item['product_name'];  // array keys: product_id, sku, quantity, price, row_total
}
```

### Get Cart Totals
```php
$totals = $this->customerHelper->getCartTotals($customerId);
echo $totals['subtotal'];       // Keys: subtotal, tax, shipping, grand_total, item_count
echo $totals['grand_total'];
```

---

## WISHLIST

### Get Wishlist
```php
$wishlist = $this->customerHelper->getCustomerWishlist($customerId);
```

### Get Wishlist Items
```php
$items = $this->customerHelper->getCustomerWishlistItems($customerId);
foreach ($items as $item) {
    echo $item['product_name'];  // Keys: product_id, sku, price, qty, added_at
}
```

### Count Wishlist Items
```php
$count = $this->customerHelper->getWishlistItemCount($customerId);
```

---

## ORDERS

### Get Orders
```php
$orders = $this->customerHelper->getCustomerOrders($customerId);
foreach ($orders as $order) {
    echo $order->getIncrementId();
}
```

### Order Count
```php
$count = $this->customerHelper->getCustomerOrderCount($customerId);
```

### Total Spent
```php
$total = $this->customerHelper->getCustomerTotalSpent($customerId);
// Returns float of total lifetime spend
```

---

## UPDATE & DELETE

### Update Customer
```php
$this->customerHelper->updateCustomer($customerId, [
    'firstname' => 'NewName',
    'dob' => '1995-05-20',
]);
```

### Delete Customer
```php
$this->customerHelper->deleteCustomer($customerId);
```

### Get Customer Attributes
```php
$attrs = $this->customerHelper->getCustomerAttributes($customerId);
```

---

## UTILITIES

### Validate Email
```php
if ($this->customerHelper->isValidEmail('john@example.com')) {
    // Valid
}
```

### Generate Report
```php
$report = $this->customerHelper->generateCustomerReport();
// Returns: total_customers, active_customers, recent_customers_30d, recent_customers_7d
```

---

## ERROR HANDLING

```php
try {
    $customer = $this->customerHelper->loadCustomerById(999);
} catch (\Exception $e) {
    $this->logger->error($e->getMessage());
}
```

---

## COMMON PATTERNS

### Register New Customer
```php
if (!$this->customerHelper->customerExists($email)) {
    $customer = $this->customerHelper->createCustomerBasic(
        $email, $firstname, $lastname, $password
    );
}
```

### Get Full Customer Profile
```php
$customer = $this->customerHelper->loadCustomerById($id);
$addresses = $this->customerHelper->getCustomerAddresses($id);
$billing = $this->customerHelper->getDefaultBillingAddress($id);
$orders = $this->customerHelper->getCustomerOrderCount($id);
$spent = $this->customerHelper->getCustomerTotalSpent($id);
```

### Find VIP Customers
```php
$collection = $this->customerHelper->getAllCustomers(1000);
foreach ($collection as $customer) {
    $spent = $this->customerHelper->getCustomerTotalSpent($customer->getId());
    if ($spent > 1000) {
        echo "VIP: " . $customer->getEmail();
    }
}
```

---

## METHOD SIGNATURES

```
// CREATING
createCustomerBasic(email, firstname, lastname, password, websiteId=0)
createCustomerComprehensive(customerData[])
createCustomerViaApi(customerData[])
addCustomerAddress(customer, addressData, isDefaultBilling, isDefaultShipping)

// LOADING
loadCustomerById(customerId)
loadCustomerByEmail(email, websiteId=0)
loadCustomerByAttribute(attributeCode, value)
customerExists(email)

// GETTING (Collections)
getAllCustomers(limit=20, page=1, filters=[])
getCustomersByFilter(criteria, value, condition='like')
getActiveCustomers()
getCustomersByGroup(groupId)
getRecentlyRegisteredCustomers(days=30)
getCustomerCount()

// ADDRESSES
getCustomerAddresses(customerId)
getDefaultBillingAddress(customerId)
getDefaultShippingAddress(customerId)
getAddressDetails(addressId)
updateCustomerAddress(addressId, addressData[])
deleteCustomerAddress(addressId)

// CART
getCustomerCart(customerId)
getCustomerCartItems(customerId)
getCartTotals(customerId)

// WISHLIST
getCustomerWishlist(customerId)
getCustomerWishlistItems(customerId)
getWishlistItemCount(customerId)

// ORDERS
getCustomerOrders(customerId)
getCustomerOrderCount(customerId)
getCustomerTotalSpent(customerId)

// OTHER
updateCustomer(customerId, customerData[])
deleteCustomer(customerId)
getCustomerAttributes(customerId)
isValidEmail(email)
generateCustomerReport()
```

---

## Data Structure Returns

### Customer Object
```php
$customer->getId()
$customer->getEmail()
$customer->getFirstname()
$customer->getLastname()
$customer->getDob()
$customer->getGender()
$customer->getPrefix()
$customer->getSuffix()
$customer->getTaxvat()
$customer->getCreatedAt()
$customer->getUpdatedAt()
$customer->getGroupId()
$customer->getIsActive()
```

### Address Object
```php
$address->getId()
$address->getFirstname()
$address->getLastname()
$address->getCompany()
$address->getStreet()
$address->getCity()
$address->getRegion()
$address->getPostcode()
$address->getCountryId()
$address->getTelephone()
$address->getFax()
$address->isDefaultBilling()
$address->isDefaultShipping()
```

### Cart Items Array
```php
[
    'item_id' => 1,
    'product_id' => 10,
    'product_name' => 'Product Name',
    'sku' => 'SKU123',
    'quantity' => 2,
    'price' => 29.99,
    'row_total' => 59.98,
]
```

### Cart Totals Array
```php
[
    'subtotal' => 100.00,
    'tax' => 8.00,
    'shipping' => 5.00,
    'grand_total' => 113.00,
    'item_count' => 3,
    'quantity_count' => 5,
]
```

---

## Running Demo

```bash
php bin/magento customer:demo
```

---

## Module Structure

```
Advik/CustomerCreation/
├── registration.php
├── etc/
│   ├── module.xml
│   ├── di.xml
│   └── events.xml (for observers)
├── Helper/
│   └── CustomerHelper.php (350+ lines, all main methods)
├── Console/Command/
│   └── DemoCommand.php (Runnable examples)
├── Examples/
│   └── RealWorldExamples.php (9 practical scenarios)
├── README.md (Complete guide)
├── USAGE_GUIDE.md (Context-specific usage)
└── QUICK_REFERENCE.md (This file)
```

---

**Save this for quick lookup!**

