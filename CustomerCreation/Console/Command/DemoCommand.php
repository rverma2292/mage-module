<?php
/**
 * Console Command to demonstrate all customer operations
 * Usage: php bin/magento customer:demo
 */

declare(strict_types=1);

namespace Advik\CustomerCreation\Console\Command;

use Advik\CustomerCreation\Helper\CustomerHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DemoCommand extends Command
{
    /**
     * @var CustomerHelper
     */
    protected $customerHelper;

    /**
     * DemoCommand constructor
     */
    public function __construct(CustomerHelper $customerHelper)
    {
        parent::__construct();
        $this->customerHelper = $customerHelper;
    }

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this->setName('customer:demo');
        $this->setDescription('Demonstrate all customer operations from CustomerHelper');
        parent::configure();
    }

    /**
     * Execute the command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>========== MAGENTO 2 CUSTOMER OPERATIONS DEMO ==========</info>');

        try {
            // 1. Create a new customer
            $output->writeln('\n<fg=green>1. CREATE CUSTOMER - Basic Method</fg=green>');
            $customer = $this->customerHelper->createCustomerBasic(
                'demo.basic@example.com',
                'John',
                'Doe',
                'Password123'
            );
            $output->writeln($customer->getId() ? "✓ Created customer ID: {$customer->getId()}" : '✗ Failed to create');

            // 2. Create comprehensive customer with address
            $output->writeln('\n<fg=green>2. CREATE CUSTOMER - Comprehensive Method with Address</fg=green>');
            $customerData = [
                'email' => 'demo.full@example.com',
                'firstname' => 'Jane',
                'lastname' => 'Smith',
                'password' => 'SecurePass123',
                'website_id' => 1,
                'store_id' => 1,
                'group_id' => 1,
                'dob' => '1990-01-15',
                'gender' => 2,
                'prefix' => 'Ms.',
                'taxvat' => '12345678',
                'is_active' => 1,
                'company' => 'Tech Corp',
                'street' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postcode' => '90001',
                'country_id' => 'US',
                'telephone' => '555-9876',
                'fax' => '555-5432',
                'address_default_billing' => true,
                'address_default_shipping' => true,
            ];

            $comprehensiveCustomer = $this->customerHelper->createCustomerComprehensive($customerData);
            $output->writeln($comprehensiveCustomer->getId()
                ? "✓ Created customer ID: {$comprehensiveCustomer->getId()} with address"
                : '✗ Failed to create'
            );

            // 3. Get all customers
            $output->writeln('\n<fg=green>3. GET ALL CUSTOMERS</fg=green>');
            $collection = $this->customerHelper->getAllCustomers(10);
            $output->writeln("✓ Total customers (showing first 10): " . count($collection->getItems()));

            // 4. Get active customers
            $output->writeln('\n<fg=green>4. GET ACTIVE CUSTOMERS</fg=green>');
            $activeCustomers = $this->customerHelper->getActiveCustomers();
            $output->writeln("✓ Active customers count: " . $activeCustomers->getSize());

            // 5. Load customer by ID
            $output->writeln('\n<fg=green>5. LOAD CUSTOMER BY ID</fg=green>');
            if ($customer->getId()) {
                $loadedCustomer = $this->customerHelper->loadCustomerById($customer->getId());
                $output->writeln("✓ Loaded customer: {$loadedCustomer->getEmail()}");
            }

            // 6. Load customer by email
            $output->writeln('\n<fg=green>6. LOAD CUSTOMER BY EMAIL</fg=green>');
            $emailCustomer = $this->customerHelper->loadCustomerByEmail('demo.basic@example.com');
            $output->writeln("✓ Found customer: {$emailCustomer->getFirstname()} {$emailCustomer->getLastname()}");

            // 7. Check if customer exists
            $output->writeln('\n<fg=green>7. CHECK IF CUSTOMER EXISTS</fg=green>');
            $exists = $this->customerHelper->customerExists('demo.basic@example.com');
            $output->writeln($exists ? "✓ Customer exists" : "✗ Customer not found");

            // 8. Get customer addresses
            if ($comprehensiveCustomer->getId()) {
                $output->writeln('\n<fg=green>8. GET CUSTOMER ADDRESSES</fg=green>');
                $addresses = $this->customerHelper->getCustomerAddresses($comprehensiveCustomer->getId());
                $output->writeln("✓ Customer has " . count($addresses) . " address(es)");

                // 9. Get default billing address
                $output::writeln('\n<fg=green>9. GET DEFAULT BILLING ADDRESS</fg=green>');
                $billingAddress = $this->customerHelper->getDefaultBillingAddress($comprehensiveCustomer->getId());
                if ($billingAddress) {
                    $output->writeln("✓ Billing Address: {$billingAddress->getCity()}, {$billingAddress->getRegion()}");
                } else {
                    $output->writeln("✗ No default billing address");
                }

                // 10. Get default shipping address
                $output->writeln('\n<fg=green>10. GET DEFAULT SHIPPING ADDRESS</fg=green>');
                $shippingAddress = $this->customerHelper->getDefaultShippingAddress($comprehensiveCustomer->getId());
                if ($shippingAddress) {
                    $output->writeln("✓ Shipping Address: {$shippingAddress->getStreet()}, {$shippingAddress->getCity()}");
                } else {
                    $output->writeln("✗ No default shipping address");
                }
            }

            // 11. Get customer cart
            $output->writeln('\n<fg=green>11. GET CUSTOMER CART</fg=green>');
            if ($customer->getId()) {
                $cart = $this->customerHelper->getCustomerCart($customer->getId());
                $output->writeln($cart ? "✓ Customer has active cart" : "✗ No active cart");

                // 12. Get cart items
                $output->writeln('\n<fg=green>12. GET CART ITEMS</fg=green>');
                $cartItems = $this->customerHelper->getCustomerCartItems($customer->getId());
                $output->writeln("✓ Cart items count: " . count($cartItems));

                // 13. Get cart totals
                $output->writeln('\n<fg=green>13. GET CART TOTALS</fg=green>');
                $cartTotals = $this->customerHelper->getCartTotals($customer->getId());
                if (!empty($cartTotals)) {
                    $output->writeln("✓ Subtotal: {$cartTotals['subtotal']}, Grand Total: {$cartTotals['grand_total']}");
                } else {
                    $output->writeln("✗ No cart data");
                }
            }

            // 14. Get customer wishlist
            $output->writeln('\n<fg=green>14. GET CUSTOMER WISHLIST</fg=green>');
            if ($customer->getId()) {
                $wishlist = $this->customerHelper->getCustomerWishlist($customer->getId());
                $output->writeln($wishlist ? "✓ Customer wishlist loaded" : "✗ No wishlist found");

                // 15. Get wishlist items
                $output->writeln('\n<fg=green>15. GET WISHLIST ITEMS</fg=green>');
                $wishlistItems = $this->customerHelper->getCustomerWishlistItems($customer->getId());
                $output->writeln("✓ Wishlist items count: " . count($wishlistItems));
            }

            // 16. Get customer orders
            $output->writeln('\n<fg=green>16. GET CUSTOMER ORDERS</fg=green>');
            if ($customer->getId()) {
                $orders = $this->customerHelper->getCustomerOrders($customer->getId());
                $output->writeln("✓ Customer orders count: " . $orders->getSize());

                // 17. Get customer order count
                $output->writeln('\n<fg=green>17. GET CUSTOMER ORDER COUNT</fg=green>');
                $orderCount = $this->customerHelper->getCustomerOrderCount($customer->getId());
                $output->writeln("✓ Total orders: $orderCount");

                // 18. Get customer total spent
                $output->writeln('\n<fg=green>18. GET CUSTOMER TOTAL SPENT</fg=green>');
                $totalSpent = $this->customerHelper->getCustomerTotalSpent($customer->getId());
                $output->writeln("✓ Total spent: $totalSpent");
            }

            // 19. Get customer statistics
            $output->writeln('\n<fg=green>19. CUSTOMER STATISTICS</fg=green>');
            $report = $this->customerHelper->generateCustomerReport();
            $output->writeln("✓ Total: {$report['total_customers']}, Active: {$report['active_customers']}, New (30d): {$report['recent_customers_30d']}");

            // 20. Update customer
            $output->writeln('\n<fg=green>20. UPDATE CUSTOMER</fg=green>');
            if ($customer->getId()) {
                $updated = $this->customerHelper->updateCustomer($customer->getId(), [
                    'firstname' => 'Johnny',
                    'lastname' => 'Updated'
                ]);
                $output->writeln($updated ? "✓ Customer updated successfully" : "✗ Failed to update");
            }

            $output->writeln('\n<info>========== DEMO COMPLETED SUCCESSFULLY ==========</info>');

        } catch (\Exception $e) {
            $output->writeln("<error>Error: {$e->getMessage()}</error>");
            return 1;
        }

        return 0;
    }
}

