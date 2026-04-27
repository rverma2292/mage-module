<?php

namespace Advik\CategoryVideoUpload\Model\Category\Attribute\Backend;

use Magento\Catalog\Model\Category\Attribute\Backend\Image;
use Advik\CategoryVideoUpload\Model\VideoUploader;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Video extends Image
{
    /**
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploaderFactory
     * @param StoreManagerInterface $storeManager
     * @param VideoUploader $videoUploader
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        UploaderFactory $fileUploaderFactory,
        StoreManagerInterface $storeManager,
        VideoUploader $videoUploader
    ) {
        parent::__construct($logger, $filesystem, $fileUploaderFactory, $storeManager, $videoUploader);
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        return parent::beforeSave($object);
    }
}
