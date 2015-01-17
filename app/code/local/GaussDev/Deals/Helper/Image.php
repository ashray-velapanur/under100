<?php

class GaussDev_Deals_Helper_Image extends Mage_Core_Helper_Abstract
{
    public $width = null;
    public $height = null;
    public $rawImg = '';
    public $img = false;
    public $cacheDir = '';
    public $croppedCacheDir = '';
    public $croppedImage = '';
    public $cachedImage = '';
    public $cachedImageUrl = '';
    public $ext = '';
    public $bgColor = array(255, 255, 255);
    /* @var $imageObj Varien_Image */
    public $imageObj = '';
    public $baseUrl = '';
    public $placeHolder = false;

    public $keepTransparency = true;
    public $aspectRatio = true;
    public $constrainOnly = true;
    public $keepFrame = true;
    public $quality;

    protected $_topRate = 0.5;
    protected $_bottomRate = 0.5;


    /**
     * Reset all previous data
     */
    protected function _reset()
    {
        $this->width = null;
        $this->height = null;
        $this->rawImg = '';
        $this->img = false;
        $this->cachedImage = '';
        $this->croppedImage = '';
        $this->cachedImageUrl = '';
        $this->ext = '';
        $this->bgColor = array(255, 255, 255);
        $this->imageObj = '';
        $this->baseUrl = Mage::getBaseUrl();
        $this->keepTransparency = true;
        $this->aspectRatio = true;
        $this->constrainOnly = true;
        $this->keepFrame = true;
        $this->quality = null;

        return $this;
    }


    /**
     * @param string $img
     *
     * @return $this
     */
    public function init($img = '')
    {
        $this->_reset();

        if (empty($this->placeHolder)) {
            $this->placeHolder = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg');
        }

        if ($img) {
            $this->rawImg = $img;
        }

        $this->imagePath($this->rawImg);

        $this->imageObj = new Varien_Image($this->img);

        $path_parts = pathinfo($this->img);

        $this->ext = $path_parts['extension'];

        $this->cacheDir();

        return $this;
    }

    /**
     * @param null|int $width
     *
     * @return $this
     */
    public function setWidth($width = null)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * @param null|int $height
     *
     * @return $this
     */
    public function setHeight($height = null)
    {
        $this->height = $height;

        return $this;
    }

    public function adaptiveResize()
    {
        if (is_null($this->getWidth())) {
            return $this;
        }

        if (is_null($this->getHeight())) {
            $this->setHeight($this->getWidth());
        }

        $processor = $this->imageObj;

        $currentRatio = $processor->getOriginalWidth() / $processor->getOriginalHeight();
        $targetRatio = $this->getWidth() / $this->getHeight();

        if ($targetRatio > $currentRatio) {
            $processor->resize($this->getWidth(), null);
        } else {
            $processor->resize(null, $this->getHeight());
        }

        $diffWidth = $processor->getOriginalWidth() - $this->getWidth();
        $diffHeight = $processor->getOriginalHeight() - $this->getHeight();

        $processor->crop(floor($diffHeight * $this->_topRate), floor($diffWidth / 2), ceil($diffWidth / 2),
            ceil($diffHeight * $this->_bottomRate));

        return $this;
    }

    /**
     * @param null|int $width
     * @param null|int $height
     *
     * @return string
     */
    public function resize($width = null, $height = null)
    {
        if ($width) {
            $this->width = $width;
        }

        if ($height) {
            $this->height = $height;
        }

        $this->adaptiveResize();

        $this->cacheIt();

        return $this->cachedImageUrl();
    }

    /**
     * @return string
     */
    public function cachedImageUrl()
    {
        $img = str_replace(BP, '', $this->cachedImage);
        $img = trim(str_replace('\\', '/', $img), '/');

        return $this->baseUrl . $img;
    }

    /**
     * @return string|void
     */
    public function getCroppedCache()
    {
        $this->croppedImage = $this->croppedCacheDir . md5($this->img . $this->width . $this->height) . '.'
            . $this->ext;

        if (file_exists($this->cachedImage)) {
            return $this->cachedImage;
        }

        $this->cropIt();

        return null;
    }

    public function cacheIt()
    {
        $this->cachedImage = $this->cacheDir . md5($this->img . $this->width . $this->height) . '.' . $this->ext;

        if (file_exists($this->cachedImage)) {
            return $this->cachedImage;
        }

        $this->resizer();

        return null;
    }

    /**
     * Set image quality, values in percentage from 0 to 100
     *
     * @param $quality
     *
     * @return $this
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
    }

    /**
     * Guarantee, that image picture width/height will not be distorted.
     * Applicable before calling resize()
     * It is true by default.
     *
     * @param bool $bool
     *
     * @return $this
     */
    public function keepAspectRatio($bool = true)
    {
        $this->aspectRatio = $bool;

        return $this;
    }

    /**
     * Guarantee, that image will have dimensions, set in $width/$height
     * Applicable before calling resize()
     * Not applicable, if keepAspectRatio(false)
     *
     * @param bool $bool
     *
     * @return $this
     */
    public function keepFrame($bool = true)
    {
        $this->keepFrame = $bool;

        return $this;
    }

    /**
     * Guarantee, that image picture will not be bigger, than it was.
     * Applicable before calling resize()
     * It is false by default
     *
     * @param bool $bool
     *
     * @return $this
     */
    public function constrainOnly($bool = false)
    {
        $this->constrainOnly = $bool;

        return $this;
    }

    /**
     * Guarantee, that image will not lose transparency if any.
     * Applicable before calling resize()
     * It is true by default.
     *
     * $alphaOpacity - TODO, not used for now
     *
     * @param $flag
     *
     * @internal param null $alphaOpacity
     *
     * @return $this
     */
    public function keepTransparency($flag)
    {
        $this->keepTransparency = $flag;

        return $this;
    }

    /**
     * @param int $top
     * @param int $left
     * @param int $right
     * @param int $bottom
     */
    private function cropIt($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        $this->imageObj->crop($top, $left, $right, $bottom);
        $this->imageObj->save($this->croppedImage);
        $this->img = $this->croppedImage;
    }

    /**
     * Crop an image.
     *
     * @param int $top    . Default value is 0
     * @param int $left   . Default value is 0
     * @param int $right  . Default value is 0
     * @param int $bottom . Default value is 0
     *
     * @access public
     * @return $this
     */
    public function crop($top = 0, $left = 0, $right = 0, $bottom = 0)
    {
        $cache = $this->getCroppedCache();
        if ($cache) {
            $this->img = $cache;
        } else {
            try {
                $width = $this->width;
                $height = $this->height;
                $origWidth = $this->getOriginalWidth();
                $origHeight = $this->getOriginalHeight();
                $cropHeightTrim = $cropWidthTrim = 0;

                if ($width && $height) {

                    $origRatio = $origWidth / $origHeight;
                    $cropRatio = $width / $height;

                    if ($origRatio >= $cropRatio) { // trim width
                        $cropWidth = $origHeight * $cropRatio;
                        $cropWidthTrim = 0 - ($cropWidth - $origWidth) / 2;
                    } else { // trim height
                        $cropHeight = $origWidth / $cropRatio;
                        $cropHeightTrim = 0 - ($cropHeight - $origHeight) / 2;
                    }
                }

                if (!$top && !$left && !$right && !$bottom) {
                    if ($cropWidthTrim) {
                        $right = $left = $cropWidthTrim;
                    } elseif ($cropHeightTrim) {
                        $top = $bottom = $cropHeightTrim;
                    }
                }
                $this->cropIt($top, $left, $right, $bottom);
            } catch (Exception $e) {
                Mage::throwException($e->getMessage());
            }
        }

        return $this;
    }

    public function resizer()
    {
        try {
            $this->imageObj->quality($this->quality);
            $this->imageObj->constrainOnly($this->aspectRatio);
            $this->imageObj->keepAspectRatio($this->aspectRatio);
            $this->imageObj->keepFrame($this->keepFrame);
            $this->imageObj->keepTransparency($this->keepTransparency);
            $this->imageObj->backgroundColor($this->bgColor);
            $this->imageObj->resize($this->width, $this->height);
            $this->imageObj->save($this->cachedImage);
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }
    }

    /**
     * @param string $img
     */
    public function imagePath($img = '')
    {
        $img = str_replace(array(
            Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_UNSECURE_BASE_URL),
            Mage::getStoreConfig(Mage_Core_Model_Store::XML_PATH_SECURE_BASE_URL)
        ), '', $img);
        $img = trim(str_replace('/', DS, $img), DS);
        $this->img = BP . DS . $img;

        if ((!file_exists($this->img) || !is_file($this->img)) && !empty($this->placeHolder)) {
            $this->imagePath($this->placeHolder);
            $this->placeHolder = false;
        }
    }

    public function cacheDir()
    {
        $cache = BP . DS . 'media' . DS . 'catalog' . DS . 'category' . DS . 'cache' . DS;
        $cropCache = $cache . 'cropped' . DS;

        $this->cacheDir = $cache;
        $this->croppedCacheDir = $cropCache;
    }

    /**
     * Retrieve original image width
     *
     * @return int|null
     */
    public function getOriginalWidth()
    {
        return $this->imageObj->getOriginalWidth();
    }

    /**
     * Retrieve original image height
     *
     * @return int|null
     */
    public function getOriginalHeight()
    {
        return $this->imageObj->getOriginalHeight();
    }

    /**
     * @return null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @return null
     */
    public function getHeight()
    {
        return $this->height;
    }
}