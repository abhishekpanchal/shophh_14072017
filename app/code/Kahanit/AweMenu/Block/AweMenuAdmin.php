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

namespace Kahanit\AweMenu\Block;

use Magento\Store\Model\Store;

class AweMenuAdmin extends \Magento\Framework\View\Element\Template
{
    protected $jsonEncoder;

    protected $formKey;

    protected $aweMenuModel;

    public $storeId;

    public $themeId;

    public $getStoreViewDDItems;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Kahanit\AweMenu\Model\ResourceModel\AweMenu $aweMenuModel
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->formKey = $formKey;
        $this->aweMenuModel = $aweMenuModel;

        parent::__construct($context);
    }

    protected function _construct()
    {
        $this->storeId = $this->getRequest()->getParam('store', Store::DEFAULT_STORE_ID);
        $menuId = $this->aweMenuModel->getEditorMenuId($this->storeId);
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $page = $om->get('Magento\Framework\View\Page\Config');

        $page->addPageAsset('Kahanit_AweMenu::css/bootstrap.min.css');
        $page->addPageAsset('Kahanit_AweMenu::css/dataTables.bootstrap.min.css');
        $page->addPageAsset('Kahanit_AweMenu::css/ui.fancytree.min.css');
        $page->addPageAsset('Kahanit_AweMenu::css/bootstrap-colorpicker.min.css');
        $page->addPageAsset('Kahanit_AweMenu::css/fontawesome.min.css');
        $page->addPageAsset('Kahanit_AweMenu::css/fontawesome-iconpicker.min.css');
        $page->addPageAsset('Kahanit_AweMenu::css/menu-layout.css');
        $page->addPageAsset('Kahanit_AweMenu::css/menu-responsive.css');
        $page->addPageAsset('Kahanit_AweMenu::css/menu-editor.css');
        if ($menuId !== false) {
            $page->addPageAsset('Kahanit_AweMenu::css/menu-theme-' . $menuId . '.css');
        }
        $page->addPageAsset('Kahanit_AweMenu::css/admin.css');
    }

    public function aweMenuMageInit()
    {
        return $this->jsonEncoder->encode((object)[
            'Kahanit_AweMenu_jquery_awemenu_loader' => (object)[
                'url'        => $this->getUrl('awemenu/index/ajax', [
                    'store'    => $this->storeId,
                    'form_key' => $this->formKey->getFormKey()
                ]),
                'jsUrl'      => $this->getViewFileUrl('Kahanit_AweMenu/js'),
                'langs'      => [
                    (object)['id' => '0', 'name' => 'Neutral', 'iso' => 'und']
                ],
                'activeLang' => '0',
                'entities'   => [
                    (object)['entity' => 'product', 'name' => 'Products', 'format' => 'table'],
                    (object)['entity' => 'category', 'name' => 'Categories', 'format' => 'tree'],
                    (object)['entity' => 'cmspage', 'name' => 'CMS Pages', 'format' => 'table']
                ]
            ]
        ]);
    }
}
