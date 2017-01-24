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

namespace Kahanit\AweMenu\Model\ResourceModel;

use Magento\Framework\Module\Dir;
use Magento\Framework\App\Filesystem\DirectoryList;

class AweMenu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $jsonEncoder;

    protected $moduleReader;

    protected $fileSystem;

    protected $themeFactory;

    protected $aweMenuHelper;

    public $css_styles = array();

    public $css_styles_counter = 1;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Theme\Model\ThemeFactory $themeFactory,
        \Kahanit\AweMenu\Helper\AweMenu $aweMenuHelper,
        $connectionName = null
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->moduleReader = $moduleReader;
        $this->fileSystem = $fileSystem;
        $this->themeFactory = $themeFactory;
        $this->aweMenuHelper = $aweMenuHelper;

        parent::__construct($context, $connectionName);
    }

    protected function _construct()
    {
        $this->_init('awemenu', 'id');
    }

    public function getEditorMenu($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(),
            ['*'])->where('deleted = 0 AND edit = 1 AND shop = ' . (int)$storeId);

        $result = $connection->fetchRow($select);

        if (!is_array($result) || empty($result['menu']) || empty($result['theme'])) {
            $result = $this->getEmptyMenu();
        }

        $result['menu'] = $this->aweMenuHelper->stripslashes($result['menu']);
        $result['theme'] = $this->aweMenuHelper->stripslashes($result['theme']);

        return $result;
    }

    public function getEditorMenuId($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(),
            ['id'])->where('deleted = 0 AND edit = 1 AND shop = ' . (int)$storeId);

        return (int)$connection->fetchOne($select);
    }

    public function getLiveMenu($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(),
            ['*'])->where('deleted = 0 AND live = 1 AND (shop = 0 OR shop = ' . (int)$storeId . ')')
            ->order('shop DESC');

        $result = $connection->fetchRow($select);

        if (!is_array($result) || empty($result['menu']) || empty($result['theme'])) {
            $result = $this->getEmptyMenu();
        }

        $result['menu'] = $this->aweMenuHelper->stripslashes($result['menu']);
        $result['theme'] = $this->aweMenuHelper->stripslashes($result['theme']);

        return $result;
    }

    public function getLiveMenuId($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(),
            ['id'])->where('deleted = 0 AND live = 1 AND (shop = 0 OR shop = ' . (int)$storeId . ')')
            ->order('shop DESC');

        return (int)$connection->fetchOne($select);
    }

    public function getEmptyMenu()
    {
        $menuPath = $this->moduleReader->getModuleDir('', 'Kahanit_AweMenu') . '/Setup/menu-empty.txt';
        $themePath = $this->moduleReader->getModuleDir('', 'Kahanit_AweMenu') . '/Setup/theme.txt';

        $row = array(
            'id'      => 0,
            'title'   => 'Untitled',
            'shop'    => 0,
            'author'  => 0,
            'menu'    => file_get_contents($menuPath),
            'theme'   => file_get_contents($themePath),
            'edit'    => 1,
            'live'    => 1,
            'deleted' => 0,
            'date'    => time()
        );

        return $row;
    }

    public function getRevisions($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(),
            ['id', 'title', 'edit', 'live', 'DATE_FORMAT(FROM_UNIXTIME(date), \'%a %D %b, %Y %h:%i %p\') as date'])
            ->where('deleted = 0 AND shop = ' . (int)$storeId)
            ->order('id DESC');

        return $connection->fetchAll($select);
    }

    public function getNumRevisions($storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable(),
            ['COUNT(id)'])->where('deleted = 0 AND shop = ' . (int)$storeId);

        return $connection->fetchOne($select);
    }

    public function getThemeCssfromLess($params)
    {
        $parser = new \Less_Parser();
        $parser->parseFile($this->moduleReader->getModuleDir(Dir::MODULE_VIEW_DIR, 'Kahanit_AweMenu') . '/base/web/less/menu-theme.less');
        $parser->ModifyVars([
            'mb-topclr'     => '#' . $params['mb-topclr'],
            'mb-btmclr'     => '#' . $params['mb-btmclr'],
            'mb-txtclr'     => '#' . $params['mb-txtclr'],
            'mb-hvr-topclr' => '#' . $params['mb-hvr-topclr'],
            'mb-hvr-btmclr' => '#' . $params['mb-hvr-btmclr'],
            'mb-hvr-txtclr' => '#' . $params['mb-hvr-txtclr'],
            'mb-bdrclr'     => '#' . $params['mb-bdrclr'],
            'dd-topclr'     => '#' . $params['dd-topclr'],
            'dd-btmclr'     => '#' . $params['dd-btmclr'],
            'dd-txtclr'     => '#' . $params['dd-txtclr'],
            'dd-ttlclr'     => '#' . $params['dd-ttlclr'],
            'dd-bdrclr'     => '#' . $params['dd-bdrclr']
        ]);

        return $parser->getCss();
    }

    public function getCustomCss($items = array())
    {
        if (!is_array($items) || count($items) == 0) {
            return false;
        }

        foreach ($items as &$item) {
            $css_properties = array();

            if (isset($item['config']['color']) && !empty($item['config']['color'])) {
                $css_properties['color'] = $item['config']['color'];
            }

            if (isset($item['config']['width']) && !empty($item['config']['width'])) {
                $css_properties['width'] = $item['config']['width'];
            }

            if (isset($item['config']['height']) && !empty($item['config']['height'])) {
                $css_properties['height'] = $item['config']['height'];
            }

            if (isset($item['config']['background-color']) && !empty($item['config']['background-color'])) {
                $css_properties['background-color'] = $item['config']['background-color'];
            }

            if (isset($item['config']['background-image']) && !empty($item['config']['background-image'])) {
                $css_properties['background-image'] = $item['config']['background-image'];
            }

            if (isset($item['config']['background-repeat']) && !empty($item['config']['background-repeat'])) {
                $css_properties['background-repeat'] = $item['config']['background-repeat'];
            }

            if (isset($item['config']['background-position']) && !empty($item['config']['background-position'])) {
                $css_properties['background-position'] = $item['config']['background-position'];
            }

            if (isset($item['config']['font-size']) && !empty($item['config']['font-size'])) {
                $css_properties['font-size'] = $item['config']['font-size'];
            }

            if (isset($item['config']['bold']) && !empty($item['config']['bold'])) {
                $css_properties['font-weight'] = (($item['config']['bold']) ? 'bold' : '');
            }

            if (isset($item['config']['italic']) && !empty($item['config']['italic'])) {
                $css_properties['font-style'] = (($item['config']['italic']) ? 'italic' : '');
            }

            if (isset($item['config']['underline']) && !empty($item['config']['underline'])) {
                $css_properties['text-decoration'] = (($item['config']['underline']) ? 'underline' : '');
            }

            $css_style = '';

            $css_style .= implode(';', array_map(function ($v, $k) {
                if ($k == 'background-image') {
                    return sprintf('%s:url(%s)', $k, $v);
                } else {
                    return sprintf('%s:%s', $k, $v);
                }
            },
                $css_properties,
                array_keys($css_properties)));

            if ($css_style != '') {
                if (in_array($css_style, $this->css_styles)) {
                    $item['config']['class-autogen'] = array_search($css_style, $this->css_styles);
                } else {
                    $this->css_styles['am-autogenclass-' . $this->css_styles_counter] = $css_style;
                    $item['config']['class-autogen'] = 'am-autogenclass-' . $this->css_styles_counter;
                    $this->css_styles_counter++;
                }
            }

            // badge styles
            $css_style = '';

            if (isset($item['config']['badge-color']) && !empty($item['config']['badge-color'])) {
                $css_style .= 'color:' . $item['config']['badge-color'] . ';';
            }

            if (isset($item['config']['badge-background-color']) && !empty($item['config']['badge-background-color'])) {
                $css_style .= 'background-color:' . $item['config']['badge-background-color'] . ';';
            }

            if ($css_style != '') {
                if (in_array($css_style, $this->css_styles)) {
                    $item['config']['class-badge-autogen'] = array_search($css_style, $this->css_styles);
                } else {
                    $this->css_styles['am-autogenclass-' . $this->css_styles_counter] = $css_style;
                    $item['config']['class-badge-autogen'] = 'am-autogenclass-' . $this->css_styles_counter;
                    $this->css_styles_counter++;
                }
            }

            // dropdown styles
            $css_style = '';

            if (isset($item['config']['dropdown']['width']) && !empty($item['config']['dropdown']['width'])) {
                $css_style .= 'width:' . $item['config']['dropdown']['width'] . ';';
            }

            if (isset($item['config']['dropdown']['height']) && !empty($item['config']['dropdown']['height'])) {
                $css_style .= 'height:' . $item['config']['dropdown']['height'] . ';';
            }

            if (isset($item['config']['dropdown']['background-color']) && !empty($item['config']['dropdown']['background-color'])) {
                $css_style .= 'background-color:' . $item['config']['dropdown']['background-color'] . ';';
            }

            if (isset($item['config']['dropdown']['background-image']) && !empty($item['config']['dropdown']['background-image'])) {
                $css_style .= 'background-image:url(\'' . $item['config']['dropdown']['background-image'] . '\');';
            }

            if (isset($item['config']['dropdown']['background-repeat']) && !empty($item['config']['dropdown']['background-repeat'])) {
                $css_style .= 'background-repeat:' . $item['config']['dropdown']['background-repeat'] . ';';
            }

            if (isset($item['config']['dropdown']['background-position']) && !empty($item['config']['dropdown']['background-position'])) {
                $css_style .= 'background-position:' . $item['config']['dropdown']['background-position'] . ';';
            }

            if ($css_style != '') {
                if (in_array($css_style, $this->css_styles)) {
                    $item['config']['class-dropdown-autogen'] = array_search($css_style, $this->css_styles);
                } else {
                    $this->css_styles['am-autogenclass-' . $this->css_styles_counter] = $css_style;
                    $item['config']['class-dropdown-autogen'] = 'am-autogenclass-' . $this->css_styles_counter;
                    $this->css_styles_counter++;
                }
            }

            $item['items'] = $this->getCustomCss($item['items']);
        }

        return $items;
    }

    public function saveMenu(\Kahanit\AweMenu\Model\AweMenu $aweMenu, $menu)
    {
        $menu['menu'] = $this->getCustomCss($menu['menu']);

        if ($menu['edit'] == '1') {
            $this->getConnection()->update($this->getMainTable(), array(
                'edit' => 0
            ), 'shop = ' . (int)$menu['shop']);
        }

        if ($menu['live'] == '1') {
            $this->getConnection()->update($this->getMainTable(), array(
                'live' => 0
            ), 'shop = ' . (int)$menu['shop']);
        }

        $aweMenu->setData([
            'title' => $menu['title'],
            'shop'  => (int)$menu['shop'],
            'menu'  => $this->aweMenuHelper->addslashes($this->jsonEncoder->encode($menu['menu'])),
            'theme' => $this->aweMenuHelper->addslashes($this->jsonEncoder->encode($menu['theme'])),
            'edit'  => (int)$menu['edit'],
            'live'  => (int)$menu['live'],
            'date'  => time()
        ]);

        if (!$aweMenu->save()) {
            return false;
        }

        /* prepare theme css */
        $themecss = $this->getThemeCssfromLess($menu['theme']);

        /* prepare custom css css */
        $customcss = "/* autogenerated styles starts */\n";
        foreach ($this->css_styles as $class_name => $css_style) {
            $customcss .= "#awemenu #awemenu-forpriority ." . $class_name . '{' . $css_style . "}\n";
        }
        $customcss .= $menu['theme']['css'];
        $customcss .= "\n/* autogenerated styles ends */";

        /* save or create menu theme and custom css file */
        $themes = $this->themeFactory->create()->getCollection()->getItems();
        foreach ($themes as $theme) {
            $themeFullPath = $this->fileSystem->getDirectoryRead(DirectoryList::STATIC_VIEW)->getAbsolutePath($theme->getFullPath());
            if (!file_exists($themeFullPath)) {
                continue;
            }

            $locales = scandir($themeFullPath);
            foreach ($locales as $locale) {
                if ($locale !== '.' && $locale !== '..') {
                    $dirPath = $themeFullPath . '/' . $locale . '/Kahanit_AweMenu/css/';

                    /* save menu theme css */
                    $filePath = $dirPath . 'menu-theme-' . $aweMenu->getId() . '.css';
                    if (file_exists($filePath)) {
                        file_put_contents($filePath, $themecss);
                    } elseif (file_exists($dirPath)) {
                        fopen($filePath, 'w');
                        file_put_contents($filePath, $themecss);
                    }

                    /* save menu custom css */
                    if ($theme->getData('area') === 'frontend') {
                        $filePath = $dirPath . 'menu-custom-' . $aweMenu->getId() . '.css';
                        if (file_exists($filePath)) {
                            file_put_contents($filePath, $customcss);
                        } elseif (file_exists($dirPath)) {
                            fopen($filePath, 'w');
                            file_put_contents($filePath, $customcss);
                        }
                    }
                }
            }
        }

        return true;
    }

    public function editRevision(\Kahanit\AweMenu\Model\AweMenu $aweMenu, $storeId, $id = null)
    {
        if ($id == null) {
            return false;
        }

        $this->getConnection()->update($this->getMainTable(), ['edit' => 0],
            'edit = 1 AND shop = ' . (int)$storeId);

        return $aweMenu->load($id)->setData('edit', 1)->save();
    }

    public function liveRevision(\Kahanit\AweMenu\Model\AweMenu $aweMenu, $storeId, $id = null)
    {
        if ($id == null) {
            return false;
        }

        $this->getConnection()->update($this->getMainTable(), ['live' => 0],
            'live = 1 AND shop = ' . (int)$storeId);

        return $aweMenu->load($id)->setData('live', 1)->save();
    }

    public function deleteRevision(\Kahanit\AweMenu\Model\AweMenu $aweMenu, $storeId, $id = null)
    {
        if ($id == null) {
            return false;
        }

        return $aweMenu->load($id)->setData('deleted', 1)->save();
    }
}
