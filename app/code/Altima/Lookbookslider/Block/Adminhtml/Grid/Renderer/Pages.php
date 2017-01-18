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

namespace Altima\Lookbookslider\Block\Adminhtml\Grid\Renderer;

use Altima\Lookbookslider\Model\Page;

class Pages extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    protected $_modelpage;

    public function __construct(
    \Altima\Lookbookslider\Model\Page $page
    ) {
        $this->_modelpage = $page;
    }

    public function render(\Magento\Framework\DataObject $row) {
        $data = $row->getData();
        $out  = '';
        if (!empty($data['pages'])) {
            $pages = $this->_modelpage->toGridArray($data['pages']);
            $out   = '<ul>';
            foreach ($pages as $page) {
                $out .= '<li>' . $page . '</li>';
            }
            $out .= '</ul>';
        }
        return $out;
    }

}
