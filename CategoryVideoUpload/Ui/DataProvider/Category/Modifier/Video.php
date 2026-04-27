<?php

namespace Advik\CategoryVideoUpload\Ui\DataProvider\Category\Modifier;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Attribute\Backend\Image as ImageBackendModel;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Catalog\Model\Category\Image as CategoryImage;
use Magento\Catalog\Ui\DataProvider\Category\Modifier\AbstractModifier;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class Video implements ModifierInterface
{
    /**
     * @var FileInfo
     */
    protected $fileInfo;

    /**
     * @var CategoryImage
     */
    protected $categoryImage;

    /**
     * @var \Advik\CategoryVideoUpload\Model\VideoUploader
     */
    protected $videoUploader;

    /**
     * @param FileInfo $fileInfo
     * @param CategoryImage $categoryImage
     * @param \Advik\CategoryVideoUpload\Model\VideoUploader $videoUploader
     */
    public function __construct(
        FileInfo $fileInfo,
        CategoryImage $categoryImage,
        \Advik\CategoryVideoUpload\Model\VideoUploader $videoUploader
    ) {
        $this->fileInfo = $fileInfo;
        $this->categoryImage = $categoryImage;
        $this->videoUploader = $videoUploader;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        foreach ($data as $categoryId => &$categoryData) {
            if (isset($categoryData['category_video']) && is_string($categoryData['category_video'])) {
                $fileName = $categoryData['category_video'];
                $foundPath = null;

                // Try standard path
                if ($this->fileInfo->isExist($fileName)) {
                    $foundPath = $fileName;
                } elseif ($this->fileInfo->isExist('catalog/category/' . ltrim($fileName, '/'))) {
                    $foundPath = 'catalog/category/' . ltrim($fileName, '/');
                } elseif ($this->fileInfo->isExist('catalog/tmp/category_video/' . ltrim($fileName, '/'))) {
                    $foundPath = 'catalog/tmp/category_video/' . ltrim($fileName, '/');
                }

                if ($foundPath) {
                    try {
                        $stat = $this->fileInfo->getStat($foundPath);
                        $mime = $this->fileInfo->getMimeType($foundPath);

                        $categoryData['category_video'] = [];
                        $categoryData['category_video'][0] = [
                            'name' => $fileName,
                            'url' => $this->getVideoUrl($foundPath),
                            'size' => $stat['size'],
                            'type' => $mime,
                        ];
                    } catch (\Exception $e) {
                        // Log error or ignore if file cannot be read
                    }
                }
            }
        }
        return $data;
    }


    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    /**
     * @param string $path
     * @return string
     */
    private function getVideoUrl($path)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $mediaBaseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        return rtrim($mediaBaseUrl, '/') . '/' . ltrim(FileInfo::ENTITY_MEDIA_PATH, '/') . '/' . ltrim($path, '/');
    }

    /**
     * @param int $categoryId
     * @return Category
     */
    private function getCategory($categoryId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->create(\Magento\Catalog\Model\Category::class)->load($categoryId);
    }
}
