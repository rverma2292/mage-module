<?php

namespace Advik\Preference\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Bundle\Model\Product\Type as BundleType;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Catalog\Model\Product\Type;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Catalog\Helper\Image;


class Product implements \Magento\Framework\App\ActionInterface
{
	protected ProductCollectionFactory $_productCollectionFactory;
	protected StockStateInterface $_stockState;
	protected GetProductSalableQtyInterface $_getProductSalableQty;
	protected ProductRepositoryInterface $_productRepository;
	protected ResultFactory $resultFactory;
	protected AttributeSetRepositoryInterface $attributeRepository;
	protected PriceHelper $_priceHelper;
	protected RuleFactory $_ruleFactory;
	protected StoreManagerInterface $_storeManager;
	protected TimezoneInterface $_localeDate;
	protected DateTime $_dateTime;
	protected RuleCollectionFactory $ruleCollectionFactory;
	protected StockRegistryInterface $stockRegistry;
	protected StockResolverInterface $stockResolver;
	protected SearchCriteriaBuilder $searchCriteriaBuilder;
	protected FilterBuilder $filterBuilder;
	protected FilterGroupBuilder $filterGroupBuilder;
	protected Image $imageHelper;

	public function __construct(
		ProductCollectionFactory $productCollectionFactory,
		StockStateInterface $stockState,
		GetProductSalableQtyInterface $getProductSalableQty,
		ProductRepositoryInterface $productRepository,
		ResultFactory $resultFactory,
		AttributeSetRepositoryInterface $attributeRepository,
		PriceHelper $priceHelper,
		RuleFactory $ruleFactory,
		StoreManagerInterface $storeManager,
		TimezoneInterface $_localeDate,
		DateTime $dateTime,
		RuleCollectionFactory $ruleCollectionFactory,
		StockRegistryInterface $stockRegistry,
		StockResolverInterface $stockResolver,
		SearchCriteriaBuilder $searchCriteriaBuilder,
		FilterBuilder $filterBuilder,
		FilterGroupBuilder $filterGroupBuilder,
		Image $imageHelper,
	)
	{
		$this->_productCollectionFactory = $productCollectionFactory;
		$this->_stockState = $stockState;
		$this->_getProductSalableQty = $getProductSalableQty;
		$this->_productRepository = $productRepository;
		$this->resultFactory = $resultFactory;
		$this->attributeRepository = $attributeRepository;
		$this->_priceHelper = $priceHelper;
		$this->_ruleFactory = $ruleFactory;
		$this->_storeManager = $storeManager;
		$this->_localeDate = $_localeDate;
		$this->_dateTime = $dateTime;
		$this->ruleCollectionFactory = $ruleCollectionFactory;
		$this->stockRegistry = $stockRegistry;
		$this->stockResolver = $stockResolver;
		$this->searchCriteriaBuilder = $searchCriteriaBuilder;
		$this->filterBuilder = $filterBuilder;
		$this->filterGroupBuilder = $filterGroupBuilder;
		$this->imageHelper = $imageHelper;
	}

	//Old legacy style to get stock item data (Before 2.3 M2 Version)
	public function getStockData($productId)
	{
		$stockItem = $this->stockRegistry->getStockItem($productId);

		return [
			'qty' => $stockItem->getQty(),
			'is_in_stock' => $stockItem->getIsInStock(),
			'min_qty' => $stockItem->getMinQty(), // Low stock threshold
			'backorders' => $stockItem->getBackorders() // Kya negative stock allow hai?
		];
	}

	public function getSalableQuantity($sku)
	{
		// 1. Get Stock Id with Current Website
		$websiteCode = $this->_storeManager->getWebsite()->getCode();
		$stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
		$stockId = $stock->getStockId();
		return $this->_getProductSalableQty->execute($sku, $stockId);
	}

	public function getAttributeSetName($productId)
	{
		// 1. Load Product
		$product = $this->_productRepository->getById($productId);

		// 2. Took Attribute Set ID
		$attributeSetId = $product->getAttributeSetId();

		// 3. Get AttributeSet data from Repository
		$attributeSet = $this->attributeRepository->get($attributeSetId);

		return $attributeSet->getAttributeSetName();
	}

	public function getAppliedRuleInfo($productId)
	{
		$storeId = $this->_storeManager->getStore()->getId();
		$websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
		$date = $this->_localeDate->scopeDate($storeId)->format('Y-m-d');

		// Rules Collection
		$collection = $this->ruleCollectionFactory->create();
		$collection->addWebsiteFilter($websiteId);
		$collection->addFieldToFilter('is_active', 1);

		// Date range filter
		$collection->addFieldToFilter('from_date', [['null' => true], ['lteq' => $date]]);
		$collection->addFieldToFilter('to_date', [['null' => true], ['gteq' => $date]]);

		$appliedRules = [];

		foreach ($collection as $rule) {
			//echo '<pre>'; print_r($rule->getData()); echo '</pre>';
			$matchingProductIds = $rule->getMatchingProductIds();
			// To check specific Product is in matching array
			if ($matchingProductIds && array_key_exists($productId, $matchingProductIds)) {
				$appliedRules[] = [
					'rule_id' => $rule->getId(),
					'name'    => $rule->getName(),
					'description' => $rule->getDescription(),
					'action_type' => $rule->getSimpleAction(), // e.g., by_percent, by_fixed
					'discount_amount' => $rule->getDiscountAmount()
				];
			}
		}
		//die('204');
		return $appliedRules;
	}

	public function getFilteredProducts(): array
	{
		$this->searchCriteriaBuilder->addFilter('price', 100, 'lt');
		$this->searchCriteriaBuilder->setPageSize(5);
		$searchCriteria = $this->searchCriteriaBuilder->create();

		$products = $this->_productRepository->getList($searchCriteria);
		return $products->getItems();
	}

	public function getProductsWithComplexFilter()
	{
		$filterRed = $this->filterBuilder->setField('color')
		->setConditionType('eq')
		->setValue(['color' => 'red'])
		->create();

		$filterBlue = $this->filterBuilder->setField('color')
			->setConditionType('eq')
			->setValue(['color' => 'blue'])
			->create();
		$filterStatus = $this->filterBuilder->setField('status')
			->setConditionType('eq')
			->setValue(['status' => 1])
			->create();
		$orGroup = $this->filterGroupBuilder->addFilter($filterRed)->addFilter($filterBlue)->create();
		$andGroup = $this->filterGroupBuilder->addFilter($filterStatus)->addFilter($filterStatus)->create();
		$searchCriteria = $this->searchCriteriaBuilder
			->setFilterGroups([$orGroup, $andGroup])
			->setPageSize(20)
			->create();

		return $this->_productRepository->getList($searchCriteria);
	}

	public function execute()
	{
		/**
		 * @var \Magento\Framework\View\Result\Page $result
		 */
		$result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$result->getConfig()->getTitle()->prepend('Product Controller');
		$productCollection = $this->_productCollectionFactory->create();

		// $productCollection->addFieldToFilter('status', 1);
		// Simple Product Collection

		$productCollection->addAttributeToFilter('type_id', 'simple');
		echo "Product Collection Count: " . $productCollection->getSize();
		echo "<br>";
		$configProductCollection = $this->_productCollectionFactory->create();
		$configProductCollection->addAttributeToFilter('type_id', Configurable::TYPE_CODE);
		echo "Configurable Product Collection Count: " . $configProductCollection->getSize();
		echo "<br>";
		$bundleProductCollection = $this->_productCollectionFactory->create();
		$bundleProductCollection->addAttributeToFilter('type_id', BundleType::TYPE_CODE);
		echo "Bundle Product Collection Count: " . $bundleProductCollection->getSize();
		echo "<br>";
		$groupedProductCollection = $this->_productCollectionFactory->create();
		$groupedProductCollection->addAttributeToFilter('type_id', Grouped::TYPE_CODE);
		echo "Group Product Collection Count: " . $groupedProductCollection->getSize();
		echo "<br>";
		$virtualProductCollection = $this->_productCollectionFactory->create();
		$virtualProductCollection->addAttributeToFilter('type_id', TYPE::TYPE_VIRTUAL);
		echo "Virtual Product Collection Count: " . $virtualProductCollection->getSize();
		echo "<br>";
		$downloadableProductCollection = $this->_productCollectionFactory->create();
		$downloadableProductCollection->addAttributeToFilter('type_id', \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);
		echo "Downloadable Product Collection Count: " . $downloadableProductCollection->getSize();
		echo "<br>================================<br>";

		$productId = 1;
		$defaultStockId = 1;
		$staticSku = "24-MB01";
		echo "Get Sku with ID For: " . $productId . "<br>";
		$product = $this->_productRepository->getById($productId);
		echo $productSku = $product->getSku();
		echo "<br>================================<br>";
		echo "Product Get Quantity for Product  Id ".$productId.": ";
		echo "<br>";
		echo $qty = $this->_stockState->getStockQty($productId);
		echo "<br>================================<br>";
		echo "Product Get Salaable Quantity for Product  Id ".$productId.": ";
		echo "<br>";
		echo $salableqty = $this->_getProductSalableQty->execute($productSku, $defaultStockId);
		echo "<br>================================<br>";

		echo "Product Attribute Set Id for Product  Id ".$productId.": ";
		echo "<br>";
		echo $product->getAttributeSetId();
		echo "<br>================================<br>";
		echo "Attribute Set Name: ";
		echo "<br>";
		echo $this->getAttributeSetName($productId);
		echo "<br>================================<br>";
		echo "Product Price: <br>";
		echo $product->getPrice();
		echo "<br>================================<br>";
		echo "Product Special Price: <br>";
		echo $product->getSpecialPrice();
		echo "<br>================================<br>";
		echo "Product Final Price: <br>";
		echo $product->getFinalPrice();
		echo "<br>================================<br>";
		echo "Product Formated Final Price: <br>";
		echo $this->_priceHelper->currency($product->getFinalPrice(), true, true );
		echo "<br>================================<br>";
		echo "Store ID: ".
		$storeId = $this->_storeManager->getStore()->getStoreId();
		echo "<br>================================<br>";
		$date = $this->_localeDate->scopeDate($storeId);
		echo "<br>================================<br>";
		echo "Rule Price : ".
			$this->_ruleFactory->create()->getRulePrice($date, $storeId, 1, $productId);
		echo "<br>";
		$appliedRules = $this->getAppliedRuleInfo($productId);
		echo '<pre>'; print_r($appliedRules); echo '</pre>';
		echo "<br>================================<br>";
		echo "Get Product Stock Data with Old style: ";
		echo "<br>";
		echo "<pre>"; print_r($this->getStockData($productId)); echo '</pre>';
		echo "<br>================================<br>";
		echo "Get Salable Qty Data (From 2.3V) : ";
		echo $this->getSalableQuantity($product->getSku());
		echo "<br>================================<br>";
		echo "Get Products with Search Builder: ";
		$productList = $this->getFilteredProducts();
		//echo '<pre>'; print_r($productList); echo '</pre>';
		$productsWithORConditions = $this->getProductsWithComplexFilter();
		echo '<pre>'; print_r($productsWithORConditions->getItems()); echo '</pre>';
		echo "<br>================================<br>";
		echo "Get Base Image";
		echo "<br>";
		echo $product->getImage();
		echo "<br>================================<br>";
		echo "Get Small Image";
		echo "<br>";
		echo $product->getSmallImage();
		echo "<br>================================<br>";
		echo "Get Thumbnail Image";
		echo "<br>";
		echo $product->getThumbnail();
		echo "<br>================================<br>";
		echo "Get Product Media Images";
		echo "<br>";
		$images = $product->getMediaGalleryImages();
		foreach ($images as $image) {
			echo $image->getFile();
			echo "<br>";
			echo $image->getUrl();
			echo "<br>";
			echo $image->getLabel();
			echo "<br>";

		}
		echo "<br>================================<br>";
		echo "Get Product Images with Helper class where we can resize the image too";
		$url = $this->imageHelper->init($product, 'product_page_main_image')
		->setImageFile($product->getImage())
		->resize(100, 100)->getUrl();
		echo "<br>";
		echo $url;
		echo "<br>============================<br>";
		die("Line33");
		return $result;
	}
}
