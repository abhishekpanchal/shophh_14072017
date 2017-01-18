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

namespace Altima\Lookbookslider\Block\Adminhtml\System\Config\Form;

use Magento\Store\Model\ScopeInterface;

class Info extends \Magento\Config\Block\System\Config\Form\Field {

    protected $moduleList;

    public function __construct(
    \Magento\Framework\Module\ModuleListInterface $moduleList, \Magento\Backend\Block\Template\Context $context, array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleList = $moduleList;
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element) {
        $m    = $this->moduleList->getOne($this->getModuleName());
        $html = '<div style="padding:10px;background-color:#f8f8f8;border:1px solid #ddd;margin-bottom:7px;">
            Lookbookslider Extension v' . $m['setup_version'] . ' was developed by <a href="http://altima.com.ua/" target="_blank">Altima</a>.
        </div>';
        return $html;
    }

}
