<?php

namespace Bluebadger\Dropship\Block\Vendor;

use Magento\Framework\View\Element\Template;

class HtmlHeader extends \Unirgy\Dropship\Block\Vendor\HtmlHeader
{
    protected $_logo;

    public function __construct(
        \Magento\Theme\Block\Html\Header\Logo $_logo,
        Template\Context $context,
        array $data = [])
    {
        $this->_logo = $_logo;
        parent::__construct($context, $data);
    }

    public function getLogoSrc()
    {
        return $this->_logo->getLogoSrc();
    }

    /**
     * Get logo text
     *
     * @return string
     */
    public function getLogoAlt()
    {
        return $this->_logo->getLogoAlt();
    }

    /**
     * Get logo width
     *
     * @return int
     */
    public function getLogoWidth()
    {
        return $this->_logo->getLogoWidth();
    }

    /**
     * Get logo height
     *
     * @return int
     */
    public function getLogoHeight()
    {
        return $this->_logo->getLogoHeight();
    }


}