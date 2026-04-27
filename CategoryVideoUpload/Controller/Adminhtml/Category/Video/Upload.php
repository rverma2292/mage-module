<?php

namespace Advik\CategoryVideoUpload\Controller\Adminhtml\Category\Video;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Advik\CategoryVideoUpload\Model\VideoUploader;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;

class Upload extends Action implements HttpPostActionInterface
{
    /**
     * @var VideoUploader
     */
    protected $videoUploader;

    /**
     * @param Context $context
     * @param VideoUploader $videoUploader
     */
    public function __construct(
        Context $context,
        VideoUploader $videoUploader
    ) {
        parent::__construct($context);
        $this->videoUploader = $videoUploader;
    }

    /**
     * Check admin permissions
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::categories');
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->videoUploader->saveFileToTmpDir('category_video');
			//echo '<pre>'; print_r($result); echo '</pre>'; die('Endhere');
            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
