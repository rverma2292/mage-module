<?php

namespace Advik\CategoryVideoUpload\Model;

use Magento\Catalog\Model\ImageUploader;

/**
 * Class VideoUploader
 */
class VideoUploader extends ImageUploader
{
    /**
     * @param string $path
     * @return bool
     */
    public function isExist($path)
    {
        return $this->mediaDirectory->isExist($path);
    }

    /**
     * @param string $path
     * @return array
     */
    public function getStat($path)
    {
        return $this->mediaDirectory->stat($path);
    }

    /**
     * @param string $path
     * @return string
     */
    public function getMimeType($path)
    {
        return $this->mediaDirectory->getDriver()->getMimeType($this->mediaDirectory->getAbsolutePath($path));
    }

	/**
	 * Move file from temporary directory to permanent
	 *
	 * @param string $imageName
	 * @param bool $returnRelativePath
	 * @return string
	 */
	public function moveFileFromTmp($imageName, $returnRelativePath = false)
	{
		$baseTmpPath = $this->getBaseTmpPath();
		$basePath = $this->getBasePath();

        // Ensure imageName doesn't have leading slashes that could mess up getFilePath
        $imageName = ltrim($imageName, '/');

		$baseImagePath = $this->getFilePath($basePath, $imageName);
		$baseTmpImagePath = $this->getFilePath($baseTmpPath, $imageName);

		try {
			if ($this->mediaDirectory->isExist($baseTmpImagePath)) {
				$this->coreFileStorageDatabase->copyFile($baseTmpImagePath, $baseImagePath);
				$this->mediaDirectory->renameFile(
					$this->mediaDirectory->getAbsolutePath($baseTmpImagePath),
					$this->mediaDirectory->getAbsolutePath($baseImagePath)
				);
			}
		} catch (\Exception $e) {
			throw new \Magento\Framework\Exception\LocalizedException(
				__('Something went wrong while saving the file.')
			);
		}

		// Return relative path if requested, otherwise just return the filename
		return $returnRelativePath ? $baseImagePath : $imageName;
	}

}
