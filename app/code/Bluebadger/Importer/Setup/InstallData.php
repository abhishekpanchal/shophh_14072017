<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 14:21
 */

namespace Bluebadger\Importer\Setup;

use Bluebadger\Importer\Helper\Config;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group;
use Magento\Store\Model\ResourceModel\Store;
use Magento\Store\Model\ResourceModel\Website;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\WebsiteFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LogLevel;

class InstallData implements InstallDataInterface
{
    /**
     * @var WebsiteFactory
     */
    private $websiteFactory;
    /**
     * @var Website
     */
    private $websiteResourceModel;
    /**
     * @var StoreFactory
     */
    private $storeFactory;
    /**
     * @var GroupFactory
     */
    private $groupFactory;
    /**
     * @var Group
     */
    private $groupResourceModel;
    /**
     * @var Store
     */
    private $storeResourceModel;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;


    /**
     * @var State
     */
    protected $state;

    /**
     * @var DesignInterface
     */
    protected $design;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @param WebsiteFactory $websiteFactory
     * @param Website $websiteResourceModel
     * @param Store $storeResourceModel
     * @param Group $groupResourceModel
     * @param StoreFactory $storeFactory
     * @param GroupFactory $groupFactory
     * @param StoreManagerInterface $storeManager
     * @param State $state
     * @param Config $configHelper
     * @param CategoryFactory $categoryFactory
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        WebsiteFactory $websiteFactory,
        Website $websiteResourceModel,
        Store $storeResourceModel,
        Group $groupResourceModel,
        StoreFactory $storeFactory,
        GroupFactory $groupFactory,
        StoreManagerInterface $storeManager,
        State $state,
        Config $configHelper,
        CategoryFactory $categoryFactory,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->websiteFactory = $websiteFactory;
        $this->websiteResourceModel = $websiteResourceModel;
        $this->storeFactory = $storeFactory;
        $this->groupFactory = $groupFactory;
        $this->groupResourceModel = $groupResourceModel;
        $this->storeResourceModel = $storeResourceModel;
        $this->categoryFactory    = $categoryFactory;
        $this->categoryRepository = $categoryRepository;
        $this->storeManager       = $storeManager;
        $this->state              = $state;
        $this->configHelper = $configHelper;
    }


    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup->endSetup();
    }

    public function log(string $logLevel, $message)
    {
        $this->configHelper->log($logLevel, $message);
    }
}