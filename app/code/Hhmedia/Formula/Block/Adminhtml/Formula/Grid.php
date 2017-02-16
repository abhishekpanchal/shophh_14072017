<?php
namespace Hhmedia\Formula\Block\Adminhtml\Formula;

/**
 * Adminhtml Formula grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Hhmedia\Formula\Model\ResourceModel\Formula\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Hhmedia\Formula\Model\Formula
     */
    protected $_formula;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Hhmedia\Formula\Model\Formula $formulaPage
     * @param \Hhmedia\Formula\Model\ResourceModel\Formula\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\PageLayout\Config\Builder $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Hhmedia\Formula\Model\Formula $formula,
        \Hhmedia\Formula\Model\ResourceModel\Formula\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_formula = $formula;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('formulaGrid');
        $this->setDefaultSort('formula_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        /* @var $collection \Hhmedia\Formula\Model\ResourceModel\Formula\Collection */
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn('formula_id', [
            'header'    => __('ID'),
            'index'     => 'formula_id',
        ]);
        
        $this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);
        $this->addColumn('subtitle', ['header' => __('Sub Title'), 'index' => 'subtitle']);
        $this->addColumn('sort_order', ['header' => __('Sort Order'), 'index' => 'sort_order']);
        $this->addColumn('link', ['header' => __('Link'), 'index' => 'link']);
        
        // $this->addColumn(
        //     'published_at',
        //     [
        //         'header' => __('Published On'),
        //         'index' => 'published_at',
        //         'type' => 'date',
        //         'header_css_class' => 'col-date',
        //         'column_css_class' => 'col-date'
        //     ]
        // );
        
        // $this->addColumn(
        //     'created_at',
        //     [
        //         'header' => __('Created'),
        //         'index' => 'created_at',
        //         'type' => 'datetime',
        //         'header_css_class' => 'col-date',
        //         'column_css_class' => 'col-date'
        //     ]
        // );
        
        $this->addColumn(
            'action',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'formula_id'
                    ]
                ],
                'sortable' => false,
                'filter' => false,
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['formula_id' => $row->getId()]);
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
}
