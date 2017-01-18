<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    const ALL                = 'all';
    const NONE               = 'none';
    const FADE               = 'fade';
    const FADE_OUT           = 'fadeout';
    const FLIP               = 'flipHorz';
    const FLIP_VERT          = 'flipVert';
    const SCROLL_HORZ        = 'scrollHorz';
    const SCROLL_LEFT        = 'scrollLeft';
    const SCROLL_RIGHT       = 'scrollRight';
    const SCROLL_VERT        = 'scrollVert';
    const SCROLL_UP          = 'scrollUp';
    const SCROLL_DOWN        = 'scrollDown';
    const COVER              = 'cover';
    const TILE_SLIDE         = 'tileSlide';
    const TILE_SLIDE_HORZ    = 'tileSlideHorz';
    const TILE_BLIND         = 'tileBlind';
    const TILE_BLIND_HORZ    = 'tileBlindHorz';
    const SHUFFLE            = 'shuffle';
    const SHUFFLE_REVERT     = 'shuffle_revert';
    const SLIDE_LEFT         = 'slideLeft';
    const SLIDE_RIGHT        = 'slideRight';
    const SLIDE_TOP          = 'slideTop';
    const SLIDE_BOTTOM       = 'slideBottom';
    const SLIDE_LEFT_TOP     = 'slideLeftTop';
    const SLIDE_LEFT_BOTTOM  = 'slideLeftBottom';
    const SLIDE_RIGHT_TOP    = 'slideRightTop';
    const SLIDE_RIGHT_BOTTOM = 'slideRightBottom';

    protected $_backendUrl;
    protected $_storeManager;
    protected $_categoryCollectionFactory;
    protected $_scopeConfig;
    protected $_ioFile;
    protected $temp;
    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    //protected $request;
    

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem, /* for resize */
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\Image\AdapterFactory $imageFactory, /* for resize */
        \Magento\Framework\Filesystem\Io\File $ioFile
        //\Magento\Framework\App\RequestInterface $httpRequest
    ) {
        parent::__construct($context);
        $this->_backendUrl                = $backendUrl;
        $this->_storeManager              = $storeManager;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_scopeConfig               = $context->getScopeConfig();
        $this->_filesystem                = $filesystem; /* for resize */
        $this->_directory_list            = $directory_list;
        // $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA); /* for resize */
        $this->_imageFactory              = $imageFactory; /* for resize */
        $this->_ioFile                    = $ioFile; /* for resize */
       // $this->request                    = $httpRequest;
        $this->temp                       = $this->_scopeConfig->getValue('lookbookslider/general/' . base64_decode('c2VyaWFs'), \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function jsonEncode($valueToEncode, $cycleCheck = false, $options = array()) {
        $json   = Zend_Json::encode($valueToEncode, $cycleCheck, $options);
        return $json;
    }

    public function getEnabled() {
        return $this->_scopeConfig->getValue('lookbookslider/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getUseFullProdUrl() {
        return $this->_scopeConfig->getValue('lookbookslider/general/cat_path_in_prod_url', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getInterdictOverlap() {
        $value = $this->_scopeConfig->getValue('lookbookslider/general/interdict_areas_overlap', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($value == 1) {
            return 'true';
        } else {
            return 'false';
        }
    }

    public function getMaxUploadFilesize() {
        return intval($this->_scopeConfig->getValue('lookbookslider/general/max_upload_filesize', \Magento\Store\Model\ScopeInterface::SCOPE_STORE));
    }

    public function getAllowedExtensions() {
        return $this->_scopeConfig->getValue('lookbookslider/general/allowed_extensions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function canShowProductDescr() {
        return $this->_scopeConfig->getValue('lookbookslider/general/show_product_desc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function canShowAddToCart() {
        return $this->_scopeConfig->getValue('lookbookslider/general/show_add_to_cart', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function canShowPinitButton() {
        return $this->_scopeConfig->getValue('lookbookslider/general/show_pinit_button', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getHotspotIcon() {
        $config_icon_path = $this->_scopeConfig->getValue('lookbookslider/general/hotspot_icon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$config_icon_path || $config_icon_path == '')
            return FALSE;
        //$config_icon_path = 'default/hotspot-icon.png';
        $icon             = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . '/lookbookslider/icons/' . $config_icon_path;
        return $icon;
    }

    public function getHotspotIconPath() {
        $config_icon_path = $this->_scopeConfig->getValue('lookbookslider/general/hotspot_icon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$config_icon_path || $config_icon_path == '')
            return FALSE;
        // $config_icon_path = 'default/hotspot-icon.png';
        $icon             = $this->_directory_list->getPath('media') . '/lookbookslider/icons/' . $config_icon_path;
        return $icon;
    }

    public function getBaseUrlMedia($path = '', $secure = false) {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, $secure) . $path;
    }

    public function getCategoriesArray() {
        $categoriesArray = $this->_categoryCollectionFactory->create()
                ->addAttributeToSelect('name')
                ->addAttributeToSort('path', 'asc')
                ->load()
                ->toArray();

        $categories   = array();
        $categories[] = array(
            'label' => '---None---',
            'level' => '---None---',
            'value' => '',
        );
        foreach ($categoriesArray as $categoryId => $category) {
            if (isset($category['name']) && isset($category['level'])) {
                $categories[] = array(
                    'label' => $category['name'],
                    'level' => $category['level'],
                    'value' => $categoryId,
                );
            }
        }

        return $categories;
    }

    public function getBackendUrl($route = '', $params = ['_current' => true]) {
        return $this->_backendUrl->getUrl($route, $params);
    }

    public function getAnimationEffect() {
        $effectArray = array(
            array('value' => self::ALL, 'label' => 'All effects'),
            array('value' => self::NONE, 'label' => 'None effects'),
            array('value' => self::FADE, 'label' => 'Fade'),
            array('value' => self::FADE_OUT, 'label' => 'Fade Out'),
            array('value' => self::FLIP, 'label' => 'Flip horz'),
            array('value' => self::FLIP_VERT, 'label' => 'Flip vert'),
            array('value' => self::SCROLL_HORZ, 'label' => 'Scroll horz'),
            array('value' => self::SCROLL_LEFT, 'label' => 'Scroll left'),
            array('value' => self::SCROLL_RIGHT, 'label' => 'Scroll right'),
            array('value' => self::SCROLL_VERT, 'label' => 'Scroll vert'),
            array('value' => self::SCROLL_UP, 'label' => 'Scroll up'),
            array('value' => self::SCROLL_DOWN, 'label' => 'Scroll down'),
            array('value' => self::COVER, 'label' => 'Cover'),
            array('value' => self::TILE_SLIDE, 'label' => 'Tile slide'),
            array('value' => self::TILE_SLIDE_HORZ, 'label' => 'Tile slide horz'),
            array('value' => self::TILE_BLIND, 'label' => 'Tile blind'),
            array('value' => self::TILE_BLIND_HORZ, 'label' => 'Tile blind horz'),
            array('value' => self::SHUFFLE, 'label' => 'Shuffle'),
            array('value' => self::SHUFFLE_REVERT, 'label' => 'Shuffle revert'),
            array('value' => self::SLIDE_LEFT, 'label' => 'Slide left'),
            array('value' => self::SLIDE_RIGHT, 'label' => 'Slide right'),
            array('value' => self::SLIDE_TOP, 'label' => 'Slide top'),
            array('value' => self::SLIDE_BOTTOM, 'label' => 'Slide bottom'),
            array('value' => self::SLIDE_LEFT_TOP, 'label' => 'Slide left top'),
            array('value' => self::SLIDE_LEFT_BOTTOM, 'label' => 'Slide left bottom'),
            array('value' => self::SLIDE_RIGHT_TOP, 'label' => 'Slide right top'),
            array('value' => self::SLIDE_RIGHT_BOTTOM, 'label' => 'Slide right bottom'),
        );


        return $effectArray;
    }

    public function getOptionColor() {
        return [
            ['label' => __('Yellow'), 'value' => '#f7d700'],
            ['label' => __('Red'), 'value' => '#dd0707'],
            ['label' => __('Orange'), 'value' => '#ee5f00'],
            ['label' => __('Green'), 'value' => '#83ba00'],
            ['label' => __('Blue'), 'value' => '#23b8ff'],
            ['label' => __('Gray'), 'value' => '#999'],
        ];
    }

    public function getBlockIdsToOptionsArray() {
        return [
            [
                'label' => __('------- Please choose position -------'),
                'value' => '',
            ],
            [
                'label' => __('General'),
                'value' => [
                    ['value' => 'content-top', 'label' => __('Content-Top')],
                    ['value' => 'content-bottom', 'label' => __('Content-Bottom')],
                ],
            ],
        ];
    }

    public function getResizedUrl($imgUrl, $width, $height = NULL, $noresize = FALSE) {

        if (empty($imgUrl)):
            return FALSE;
        endif;
        $config_no_resample = $this->_scopeConfig->getValue('lookbookslider/general/no_resample', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $path_parts         = pathinfo($imgUrl);
        $imgPath            = $path_parts['dirname'];
        $imgName            = $path_parts['basename'];
        $imgNameExt         = $path_parts['filename'];
        $imgExt             = $path_parts['extension'];
        $absPath            = $this->_directory_list->getPath('media') . '/lookbookslider/' . $imgUrl;
        $urlPath            = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'lookbookslider/' . $imgUrl;
        if (!file_exists($absPath)) {
            return FALSE;
        }
        if ($noresize) {
            return $absPath;
        }
        if ($config_no_resample):
            $orig_dimensions = getimagesize($absPath);
            if ($orig_dimensions[0] == $width && $orig_dimensions[1] == $height):
                $imgURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'lookbookslider/' . $imgUrl;
                return $imgUrl;
            endif;
        endif;

        $height ? : $height       = $width;
        $resizeFolder = $width . 'X' . $height;
        $imageResized = $this->_directory_list->getPath('media') . '/lookbookslider/' . $resizeFolder . '/' . $imgUrl;

        $imageResizedPath    = $this->_directory_list->getPath('media') . '/lookbookslider/' . $resizeFolder . '/' . $imgUrl;
        $imageResizedPathPng = $this->_directory_list->getPath('media') . '/lookbookslider/' . $resizeFolder . '/' . $imgNameExt . '.png';
        if (file_exists($imageResizedPathPng)) {
            $imageResizedPath = $imageResizedPathPng;
            $imgName          = $imgNameExt . '.png';
        }

        if (!file_exists($imageResizedPath) && file_exists($absPath)):
            $dimensions = getimagesize($absPath);
            if ($dimensions[0] < $dimensions[1]):
                $this->copyTransparent($absPath, $width, $height, $imageResizedPathPng, $imgExt);
                $imgUrl = $this->_storeManager->getStore()
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'lookbookslider/' . $resizeFolder . '/' . $imgNameExt . '.png';
                return $imgUrl;
            endif;

            $imageResize = $this->_imageFactory->create();

            $imageResize->open($absPath);
            $imageResize->constrainOnly(FALSE);
            $imageResize->keepTransparency(TRUE);
            $imageResize->keepFrame(FALSE);
            $imageResize->keepAspectRatio(true);
            $imageResize->quality(100);

            /*             * ************************************* */
            if ($imageResize->getOriginalWidth() < $imageResize->getOriginalHeight()) {
                $imageResize->keepFrame(TRUE);
                $imageResize->backgroundColor(array(255, 255, 255));
                $imageResize->resize($width, $height);
            } elseif (($width / $height) > ($imageResize->getOriginalWidth() / $imageResize->getOriginalHeight())) {
                $imageResize->resize($width, null);
            } else {
                $imageResize->resize(null, $height);
            }
            $cropX = 0;
            $cropY = 0;
            if ($imageResize->getOriginalWidth() > $width) {
                $cropX = intval(($imageResize->getOriginalWidth() - $width) / 2);
            } elseif ($imageResize->getOriginalHeight() > $height) {
                $cropY = intval(($imageResize->getOriginalHeight() - $height) / 2);
            }
            $imageResize->crop($cropY, $cropX, $cropX, $cropY);

            /*             * ************************************** */
            $dest = $imageResizedPath;
            $imageResize->save($dest);
        endif;
        $resizedURL = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'lookbookslider/' . $resizeFolder . '/' . $imgName;
        return $resizedURL;
    }

    public function copyTransparent($src, $x, $y, $output, $imgExt) {
        /* check and create dir */
        $imgPath    = $this->splitImageValue($output, "path");
        $this->_ioFile->checkAndCreateFolder($imgPath);
        $dimensions = getimagesize($src);
        $x_src      = $dimensions[0];
        $y_src      = $dimensions[1];
        $im         = @imagecreatetruecolor($x, $y) or die('Cannot Initialize new GD image stream');
        // Save transparency
        imagealphablending($im, true);
        if ($imgExt == 'png') {
            $src_ = imagecreatefrompng($src) or die('Cannot load original PNG');
            ;
        } elseif ($imgExt == 'gif') {
            $src_ = imagecreatefromgif($src) or die('Cannot load original GIF');
            ;
        } else {
            $src_ = imagecreatefromjpeg($src) or die('Cannot load original JPEG');
            ;
        }
        // Prepare alpha channel for transparent background
        $alpha_channel = imagecolorallocatealpha($im, 0, 0, 0, 127);
        // Fill image
        imagefill($im, 0, 0, $alpha_channel);

        // Scale image
        $ratio_orig = $x_src / $y_src;
        if ($x / $y >= $ratio_orig) {
            $x_new = $y * $ratio_orig;
            $y_new = $y;
        } else {
            $y_new = $x / $ratio_orig;
            $x_new = $x;
        }

        $des_x = ($x - $x_new) / 2;
        $des_y = ($y - $y_new) / 2;
        // Copy from other
        imagecopyresampled($im, $src_, $des_x, $des_y, 0, 0, $x_new, $y_new, $x_src, $y_src);
        imagepng($im, $output);

        // Save PNG
        imagealphablending($im, false);
        imagesavealpha($im, true);
        imagepng($im, $output, 9);
        imagedestroy($im);
    }

    /**
     * Splits images Path and Name
     *
     * Path=lookbook/
     * Name=example.jpg
     *
     * @param string $imageValue
     * @param string $attr
     * @return string
     */
    public function splitImageValue($imageValue, $attr = "name") {
        $imArray = explode("/", $imageValue);

        $name = $imArray[count($imArray) - 1];
        $path = implode("/", array_diff($imArray, array($name)));
        if ($attr == "path") {
            return $path;
        } else
            return $name;
    }

    public function getImageDimensions($img_path) {
        if (file_exists($img_path)) {
            $imageObj = $this->_imageFactory->create();
            $imageObj->open($img_path);
            $width    = $imageObj->getOriginalWidth();
            $height   = $imageObj->getOriginalHeight();
            $result   = array('width' => $width, 'height' => $height);
        } else {
            $result = array('error' => "$img_path does not exists");
        }
        return $result;
    }

    public function checkEntry($domain, $ser) {
        $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXI='));

        $domain     = str_replace('www.', '', $domain);
        $www_domain = 'www.' . $domain;

        if (sha1($key . $domain) == $ser || sha1($key . $www_domain) == $ser) {
            return true;
        }

        return false;
    }

    public function checkEntryDev($domain, $ser) {
        $key = sha1(base64_decode('YWx0aW1hbG9va2Jvb2tzbGlkZXJfZGV2'));

        $domain     = str_replace('www.', '', $domain);
        $www_domain = 'www.' . $domain;
        if (sha1($key . $domain) == $ser || sha1($key . $www_domain) == $ser) {
            return true;
        }

        return false;
    }

    public function canRun($dev = false) {
        if ($this->temp) {
            $temp = trim($this->temp);
        } else {
            return FALSE;
        }

        $m    = $temp[0];
        $temp = substr($temp, 1);
        if ($m) {
            $base_url = parse_url($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB));
            $base_url = $base_url['host'];
        } else {
            $base_url = $this->getServerName();
        }

        if (!$dev) {
            $original = $this->checkEntry($base_url, $temp);
        } else {
            $original = $this->checkEntryDev($base_url, $temp);
        }

        if (!$original) {
            return false;
        }

        return true;
    }
    
    public function getServerName() {
        return $this->_request->getServer('SERVER_NAME');
    }

}
