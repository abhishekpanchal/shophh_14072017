<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipVendorImport
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipVendorImport\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\View\DesignInterface;
use Unirgy\DropshipVendorImport\Model\Source;

class Data extends AbstractHelper
{
    protected $_hlp;

    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        Context $context
    )
    {
        $this->_hlp = $udropshipHelper;

        parent::__construct($context);
    }

    /**
     * @return \Unirgy\DropshipVendorImport\Model\Source
     */
    public function src()
    {
        return $this->_hlp->getObj('\Unirgy\DropshipVendorImport\Model\Source');
    }

    public function getImportFieldsConfig()
    {
        $fieldsConfig = $this->src()->getVendorPreferences(false);
        $fields = $this->_hlp->config()->getField();//->asCanonicalArray();
        if (!array_key_exists('uvimport_dummy', $fields)) {
            $fieldsConfig['vendor_info']['value'][] = [
                'position' => 99999,
                'label' => 'Dummy',
                'value' => 'uvimport_dummy',
            ];
        }
        return $fieldsConfig;
    }
}
