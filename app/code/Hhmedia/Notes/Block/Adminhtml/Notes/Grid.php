<?php
namespace Hhmedia\Notes\Block\Adminhtml\Notes;

/**
 * Adminhtml Notes grid
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Hhmedia\Notes\Model\ResourceModel\Notes\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Hhmedia\Notes\Model\Notes
     */
    protected $_notes;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Hhmedia\Notes\Model\Notes $notesPage
     * @param \Hhmedia\Notes\Model\ResourceModel\Notes\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\PageLayout\Config\Builder $pageLayoutBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Hhmedia\Notes\Model\Notes $notes,
        \Hhmedia\Notes\Model\ResourceModel\Notes\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_notes = $notes;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('notesGrid');
        $this->setDefaultSort('notes_id');
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
        /* @var $collection \Hhmedia\Notes\Model\ResourceModel\Notes\Collection */
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
        $this->addColumn('notes_id', [
            'header'    => __('ID'),
            'index'     => 'notes_id',
        ]);
        
        $this->addColumn('title', ['header' => __('Title'), 'index' => 'title']);
        
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index' => 'created_at',
                'type' => 'datetime',
                'header_css_class' => 'col-date',
                'column_css_class' => 'col-date'
            ]
        );
        
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
                        'field' => 'notes_id'
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
        return $this->getUrl('*/*/edit', ['notes_id' => $row->getId()]);
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
