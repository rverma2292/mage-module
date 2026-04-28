# Module Setup & Installation Guide

Complete guide to install and configure the `Advik_CustomerCreation` module.

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Module Structure](#module-structure)
3. [Installation Steps](#installation-steps)
4. [Verification](#verification)
5. [Configuration](#configuration)
6. [Troubleshooting](#troubleshooting)
7. [First Run](#first-run)

---

## Prerequisites

### Required

- Magento 2.4.x+ (tested on 2.4.7)
- PHP 7.4+ or 8.0+
- MySQL 5.7+
- Command line access to your Magento installation
- Proper file permissions (755 for directories, 644 for files)

### Optional

- Git (for version control)
- Composer (for dependency management)
- Redis/Varnish (for caching - optional)

---

## Module Structure

Ensure your module is organized as follows:

```
/var/www/html/mage/app/code/Advik/CustomerCreation/
│
├── registration.php                          # Module registration file
│
├── etc/
│   ├── module.xml                           # Module configuration
│   ├── di.xml                               # Dependency injection
│   └── events.xml                           # Event observers (optional)
│
├── Helper/
│   └── CustomerHelper.php                   # Main helper class
│
├── Console/
│   └── Command/
│       └── DemoCommand.php                  # Demo command
│
├── Model/
│   └── [Your custom models here]
│
├── Observer/
│   └── [Your observers here]
│
├── Examples/
│   └── RealWorldExamples.php               # Real-world examples
│
├── README.md                                # Complete guide
├── USAGE_GUIDE.md                          # Usage examples
├── QUICK_REFERENCE.md                      # Quick reference
└── SETUP_GUIDE.md                          # This file
```

---

## Installation Steps

### Step 1: Create Module Directory

```bash
# Create the module directory structure
mkdir -p /var/www/html/mage/app/code/Advik/CustomerCreation/{etc,Helper,Console/Command,Examples}
```

### Step 2: Add Registration File

Create `/var/www/html/mage/app/code/Advik/CustomerCreation/registration.php`:

```php
<?php
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Advik_CustomerCreation',
    __DIR__
);
```

### Step 3: Add Module Configuration

Create `/var/www/html/mage/app/code/Advik/CustomerCreation/etc/module.xml`:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">
    <module name="Advik_CustomerCreation"/>
</config>
```

### Step 4: Add Dependency Injection Configuration

Create `/var/www/html/mage/app/code/Advik/CustomerCreation/etc/di.xml`:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">
    
    <preference for="Advik\CustomerCreation\Helper\CustomerHelper"
                type="Advik\CustomerCreation\Helper\CustomerHelper"/>
    
    <type name="Advik\CustomerCreation\Console\Command\DemoCommand">
        <arguments>
            <argument name="customerHelper" xsi:type="object">Advik\CustomerCreation\Helper\CustomerHelper</argument>
        </arguments>
    </type>
    
</config>
```

### Step 5: Add Helper Class

Copy the `CustomerHelper.php` file to:
`/var/www/html/mage/app/code/Advik/CustomerCreation/Helper/CustomerHelper.php`

### Step 6: Add Console Command

Copy the `DemoCommand.php` file to:
`/var/www/html/mage/app/code/Advik/CustomerCreation/Console/Command/DemoCommand.php`

### Step 7: Set Proper Permissions

```bash
# Set directory permissions
chmod -R 755 /var/www/html/mage/app/code/Advik/

# Set file permissions
chmod 644 /var/www/html/mage/app/code/Advik/CustomerCreation/*.php
chmod 644 /var/www/html/mage/app/code/Advik/CustomerCreation/**/*.php

# Fix ownership (if needed)
chown -R www-data:www-data /var/www/html/mage/app/code/Advik/
```

---

## Verification

### Step 1: Check Module Status (Before Enabling)

```bash
cd /var/www/html/mage

# List all modules (should show disabled)
php bin/magento module:status

# Look for: Advik_CustomerCreation (should be in "disabled" list)
```

### Step 2: Enable the Module

```bash
cd /var/www/html/mage

# Enable module
php bin/magento module:enable Advik_CustomerCreation
```

**Expected Output:**
```
The following modules have been enabled:
- Advik_CustomerCreation
```

### Step 3: Run Setup Upgrade

```bash
cd /var/www/html/mage

# Run setup upgrade
php bin/magento setup:upgrade
```

**Expected Output:**
```
Magento setup:upgrade done
Cache cleared successfully
```

### Step 4: Verify Module is Enabled

```bash
# Check module status (should now show enabled)
php bin/magento module:status | grep Advik_CustomerCreation

# Expected: Advik_CustomerCreation
```

### Step 5: Compile Code (if needed)

For production environments:

```bash
cd /var/www/html/mage

# Clear generated code
rm -rf generated/code generated/metadata

# Compile code
php bin/magento setup:di:compile
```

**Expected Output:**
```
Compilation complete.
```

### Step 6: Verify Command Registration

```bash
cd /var/www/html/mage

# List available commands
php bin/magento list | grep customer

# Should show: customer:demo
```

### Step 7: Clear Cache

```bash
cd /var/www/html/mage

# Clear all caches
php bin/magento cache:flush

# Or specific cache types
php bin/magento cache:clean config
php bin/magento cache:clean full_page
```

---

## Configuration

### Optional: Add Events Configuration

Create `/var/www/html/mage/app/code/Advik/CustomerCreation/etc/events.xml` if you want to add observers:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:Event/etc/events.xsd">
    
    <!-- Optional event observers can be added here -->
    
</config>
```

### Optional: Add Frontend Routes

Create `/var/www/html/mage/app/code/Advik/CustomerCreation/etc/frontend/routes.xml` if you want frontend routes:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:App/etc/routes.xsd">
    
    <router id="standard">
        <route id="customercreation" frontName="customercreation">
            <module name="Advik_CustomerCreation"/>
        </route>
    </router>
    
</config>
```

### Optional: Add Admin Routes

Create `/var/www/html/mage/app/code/Advik/CustomerCreation/etc/adminhtml/routes.xml`:

```xml
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:magento:framework:App/etc/routes.xsd">
    
    <router id="admin">
        <route id="customercreation" frontName="customercreation">
            <module name="Advik_CustomerCreation"/>
        </route>
    </router>
    
</config>
```

---

## Troubleshooting

### Issue: Module not recognized

**Solution:**

```bash
# Check if registration.php exists and is correct
cat /var/www/html/mage/app/code/Advik/CustomerCreation/registration.php

# Clear cache and regenerate
rm -rf /var/www/html/mage/var/cache/*
rm -rf /var/www/html/mage/var/page_cache/*
rm -rf /var/www/html/mage/generated/*

php bin/magento cache:flush
```

### Issue: Command not found

**Solution:**

```bash
# Recompile DI
php bin/magento setup:di:compile

# Clear cache
php bin/magento cache:flush

# Verify command
php bin/magento list | grep customer:demo
```

### Issue: Class not found error

**Solution:**

```bash
# Check file paths are correct
ls -la /var/www/html/mage/app/code/Advik/CustomerCreation/Helper/CustomerHelper.php

# Fix namespace if needed - first line should be:
# namespace Advik\CustomerCreation\Helper;

# Recompile
php bin/magento setup:di:compile
```

### Issue: Permission denied

**Solution:**

```bash
# Check permissions
ls -la /var/www/html/mage/app/code/Advik/

# Fix if needed
chmod -R 755 /var/www/html/mage/app/code/Advik/
chmod 644 /var/www/html/mage/app/code/Advik/CustomerCreation/*.php
chown -R www-data:www-data /var/www/html/mage/app/code/Advik/
```

### Issue: DI compilation error

**Solution:**

```bash
# Clear generated code
rm -rf /var/www/html/mage/generated/code/*
rm -rf /var/www/html/mage/generated/metadata/*

# Recompile
php bin/magento setup:di:compile

# Check for errors
# Should see: "Compilation complete."
```

### Issue: "Class not found" when running command

**Solution:**

```bash
# Update autoloader
composer dump-autoload

# Regenerate bootstrap
php bin/magento setup:upgrade

# Clear cache again
php bin/magento cache:flush
```

---

## First Run

### Step 1: Run the Demo Command

```bash
cd /var/www/html/mage

php bin/magento customer:demo
```

**Expected Output:**
Rich output showing all customer operations with results.

### Step 2: Use Helper in Code

Test by creating a custom module or temporarily adding to an existing one:

```php
<?php
use Advik\CustomerCreation\Helper\CustomerHelper;

// In constructor with DI
public function __construct(CustomerHelper $customerHelper)
{
    $this->customerHelper = $customerHelper;
}

// Test basic functionality
try {
    $exists = $this->customerHelper->customerExists('test@example.com');
    echo $exists ? "Customer exists" : "Customer does not exist";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Step 3: Verify All Methods Work

```bash
cd /var/www/html/mage

# Test with a custom script
php -r "
$autoload = require 'app/bootstrap.php';
\$app = \Magento\Framework\App\Bootstrap::create(BP, \$_SERVER);

\$objectManager = \$app->getObjectManager();
\$helper = \$objectManager->get(\Advik\CustomerCreation\Helper\CustomerHelper::class);

// Test 1: Get customer count
echo 'Total customers: ' . \$helper->getCustomerCount() . PHP_EOL;

// Test 2: Get active customers
\$active = \$helper->getActiveCustomers()->getSize();
echo 'Active customers: ' . \$active . PHP_EOL;

// Test 3: Validate email
\$valid = \$helper->isValidEmail('test@example.com');
echo 'Valid email: ' . (\$valid ? 'Yes' : 'No') . PHP_EOL;
"
```

---

## Maintenance

### Regular Tasks

```bash
# Weekly: Check module status
php bin/magento module:status | grep Advik

# Monthly: Clear logs if using
tail -f var/log/system.log | grep CustomerCreation

# Before updates: Backup your code
cp -r app/code/Advik app/code/Advik.backup
```

### Upgrading the Module

```bash
# Backup
cp -r app/code/Advik app/code/Advik.backup

# Update files
# (replace with new versions)

# Verify
php bin/magento setup:di:compile

# Update database if needed
php bin/magento setup:upgrade

# Clear cache
php bin/magento cache:flush
```

---

## Performance Considerations

### Database Indexes

Magento automatically creates indexes for customer attributes:

```bash
# Verify indexes are up to date
php bin/magento indexer:reindex

# Check indexer status
php bin/magento indexer:status
```

### Cache Configuration

Add to `.env.php` for better performance:

```php
return [
    'system' => [
        'default' => [
            'dev' => [
                'static' => [
                    'sign' => 0  // Disable for development
                ]
            ]
        ]
    ]
];
```

### Memory Usage

For bulk operations, increase PHP memory:

```bash
# In bin/magento or php.ini
php -d memory_limit=2G bin/magento customer:demo
```

---

## Development Mode

For development, enable debug mode:

```php
// In app/etc/env.php
return [
    'MAGE_MODE' => 'developer',
    'debug' => [
        'debug_logging' => true,
    ],
];
```

Also check logs:

```bash
# Real-time log viewing
tail -f var/log/system.log

# Search for errors
grep "ERROR\|Exception" var/log/system.log
```

---

## Production Deployment

### Pre-deployment Checklist

- [ ] Test all functionality in staging
- [ ] Backup current code
- [ ] Backup database
- [ ] Enable maintenance mode: `php bin/magento maintenance:enable`

### Deployment Steps

```bash
# 1. Backup
cp -r app/code/Advik app/code/Advik.backup

# 2. Deploy code
# (copy files to production)

# 3. Set permissions
chmod -R 755 app/code/Advik/
chown -R www-data:www-data app/code/Advik/

# 4. Run setup upgrade
php bin/magento setup:upgrade

# 5. Compile code
php bin/magento setup:di:compile

# 6. Deploy static files
php bin/magento setup:static-content:deploy

# 7. Clear cache
php bin/magento cache:flush

# 8. Disable maintenance mode
php bin/magento maintenance:disable
```

---

## Verification Checklist

- [ ] Module created in correct directory
- [ ] registration.php exists and correct
- [ ] module.xml exists and correct
- [ ] di.xml exists and correct
- [ ] Helper file exists and correct namespace
- [ ] Console command file exists (optional)
- [ ] Module enabled: `php bin/magento module:status`
- [ ] Setup upgrade run: `php bin/magento setup:upgrade`
- [ ] DI compiled: `php bin/magento setup:di:compile`
- [ ] Cache flushed: `php bin/magento cache:flush`
- [ ] Command works: `php bin/magento customer:demo` (optional)
- [ ] No errors in logs: `tail -f var/log/system.log`

---

## Support & Resources

- **README.md** - Complete functionality guide
- **USAGE_GUIDE.md** - Context-specific usage examples
- **QUICK_REFERENCE.md** - Quick method lookup
- **RealWorldExamples.php** - 9 practical scenarios

---

## Next Steps

After installation:

1. Read [README.md](README.md) for complete functionality overview
2. Check [QUICK_REFERENCE.md](QUICK_REFERENCE.md) for method shortcuts
3. Review [USAGE_GUIDE.md](USAGE_GUIDE.md) for integration patterns
4. Study [RealWorldExamples.php](Examples/RealWorldExamples.php) for practical scenarios

---

**Installation Complete!** Your Magento 2 Customer Creation module is ready to use. 🚀

