<?php
/**
 * Awe Menu is quick, easy to setup and WYSIWYG menu management system
 *
 * Awe Menu by Kahanit(https://www.kahanit.com) is licensed under a
 * Creative Creative Commons Attribution-NoDerivatives 4.0 International License.
 * Based on a work at https://www.kahanit.com.
 * Permissions beyond the scope of this license may be available at https://www.kahanit.com.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nd/4.0/.
 *
 * @author    Amit Sidhpura <amit@kahanit.com>
 * @copyright 2016 Kahanit
 * @license   http://creativecommons.org/licenses/by-nd/4.0/
 * @version   1.0.1.0
 */

namespace Kahanit\AweMenu\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Store\Model\Store;

class InstallData implements InstallDataInterface
{
    protected $jsonDecoder;

    protected $moduleReader;

    protected $aweMenuModel;

    protected $aweMenuHelper;

    public function __construct(
        \Magento\Framework\Json\Decoder $jsonDecoder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Kahanit\AweMenu\Model\ResourceModel\AweMenu $aweMenuModel,
        \Kahanit\AweMenu\Helper\AweMenu $aweMenuHelper
    ) {
        $this->jsonDecoder = $jsonDecoder;
        $this->moduleReader = $moduleReader;
        $this->aweMenuModel = $aweMenuModel;
        $this->aweMenuHelper = $aweMenuHelper;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $menuPath = file_get_contents($this->moduleReader->getModuleDir('', 'Kahanit_AweMenu') . '/Setup/menu.txt');
        $themePath = file_get_contents($this->moduleReader->getModuleDir('', 'Kahanit_AweMenu') . '/Setup/theme.txt');

        $menu = [
            'title'   => 'Main Menu',
            'shop'    => Store::DEFAULT_STORE_ID,
            'author'  => '0',
            'menu'    => $this->jsonDecoder->decode($this->aweMenuHelper->stripslashes($menuPath)),
            'theme'   => $this->jsonDecoder->decode($this->aweMenuHelper->stripslashes($themePath)),
            'edit'    => '1',
            'live'    => '1',
            'deleted' => '0',
            'date'    => time()
        ];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->aweMenuModel->saveMenu($objectManager->create('Kahanit\AweMenu\Model\AweMenu'), $menu);
    }
}
