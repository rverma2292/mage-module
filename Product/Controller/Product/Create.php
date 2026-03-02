<?php

namespace Advik\Product\Controller\Product;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\StateException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\Framework\App\Filesystem\DirectoryList;

class Create extends Action
{
	protected ProductRepositoryInterface $productRepository;
	protected ProductInterfaceFactory $productFactory;
	protected SourceItemsSaveInterface $sourceItemsSave;
	protected SourceItemInterfaceFactory $sourceItemInterfaceFactory;
	protected DirectoryList $directoryList;

	public function __construct(
		Context $context,
		ProductRepositoryInterface $productRepository,
		ProductInterfaceFactory $productFactory,
		SourceItemsSaveInterface $sourceItemsSave,
		SourceItemInterfaceFactory $sourceItemInterfaceFactory,
		DirectoryList $directoryList,
	)
	{
		$this->productRepository = $productRepository;
		$this->productFactory = $productFactory;
		$this->sourceItemsSave = $sourceItemsSave;
		$this->sourceItemInterfaceFactory = $sourceItemInterfaceFactory;
		$this->directoryList = $directoryList;
		parent::__construct($context);
	}

	/**
	 * @throws StateException
	 * @throws CouldNotSaveException
	 * @throws InputException
	 */
	public function execute()
	{
		/**
		 * @var \Magento\Framework\View\Result\Page $result
		 */
		$result = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
		$result->getConfig()->getTitle()->prepend('Product Create Page');
		try {
			$product = $this->productFactory->create();
			$product->setName("New Programatic Simple Product Two");
			$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
			$product->setSku("new-programatic-simple-product-2");
			$product->setAttributeSetId(4);
			$product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
			$product->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
			$product->setPrice(50.00);
			$product->setStockData([
				'use_config_min_qty' => 1,
				'is_qty_decimal' => 0,
				'show_default_notification_message' => 0,
				'use_config_min_sale_qty' => 1,
				'min_sale_qty' => 1,
				'use_config_max_sale_qty' => 1,
				'max_sale_qty' => 10,
				'use_config_backorders' => 1,
				'backorders' => 0,
				'use_config_notify_stock_qty' => 1,
				'manage_stock' => 1,
				'is_in_stock' => 1,
				'qty' => 80
			]);
			$imageName = "wb04-blue-0.jpg";
			$mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
			$importFilePath = $mediaPath . DIRECTORY_SEPARATOR . 'import' . DIRECTORY_SEPARATOR . $imageName;

			if (file_exists($importFilePath)) {
				$product->addImageToMediaGallery($importFilePath, ['image', 'small_image', 'thumbnail'], false, false);
			}

			$createdProduct = $this->productRepository->save($product);
			if ($createdProduct->getId()) {
				echo "Product created: {$createdProduct->getId()}\n";

				// To save salable qty
				/*
				$sourceItem = $this->sourceItemInterfaceFactory->create();
				$sourceItem->setSourceCode('default');
				$sourceItem->setSku('new-programatic-simple-product');
				$sourceItem->setQuantity(100);
				$sourceItem->setStatus(1);
				$this->sourceItemsSave->execute([$sourceItem]);
				echo "Success: Salable Quantity updated for SKU: new-programatic-simple-product";*/
				die;
			}
		}catch (\Exception $exception) {
			echo "Error : ". $exception->getMessage();
		}
		return $result;
	}
}
