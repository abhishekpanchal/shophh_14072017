<?php
namespace Hhmedia\Editor\Block\Adminhtml\Editor\Edit\Tab;

/**
 * Cms page edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    protected $bioWysiwyg;
    protected $quoteWysiwyg;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $bioWysiwyg, 
        \Magento\Cms\Model\Wysiwyg\Config $quoteWysiwyg, 
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->bioWysiwyg = $bioWysiwyg;
        $this->quoteWysiwyg = $quoteWysiwyg;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magento\Cms\Model\Page */
        $model = $this->_coreRegistry->registry('editor');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Hhmedia_Editor::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('editor_main_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Editor Information')]);

        if ($model->getId()) {
            $fieldset->addField('editor_id', 'hidden', ['name' => 'editor_id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Editor Name'),
                'title' => __('Editor Name'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'job_title',
            'text',
            [
                'name' => 'job_title',
                'label' => __('Job Title'),
                'title' => __('Job Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'status', 
            'select', 
            [
                'label'    => __('Status'),
                'title'    => __('Status'),
                'name'     => 'status',
                'required' => true,
                'options'  => $model->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'bio',
            'editor',
            [
                'name' => 'bio',
                'label' => __('Bio'),
                'title' => __('Bio'),
                 'config'    => $this->bioWysiwyg->getConfig(),
                'wysiwyg'   => true
            ]
        );

        $fieldset->addField(
            'quote',
            'editor',
            [
                'name' => 'quote',
                'label' => __('Quote'),
                'title' => __('Quote'),
                 'config'    => $this->quoteWysiwyg->getConfig(),
                'wysiwyg'   => true
            ]
        );

        $fieldset->addField(
            'subtitle',
            'text',
            [
                'name' => 'subtitle',
                'label' => __('Subtitle'),
                'title' => __('Subtitle'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'guest', 
            'select', 
            [
                'label'    => __('Guest Editor'),
                'title'    => __('Guest Editor'),
                'name'     => 'guest',
                'required' => true,
                'options'  => $model->getAvailableGuest(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'past', 
            'select', 
            [
                'label'    => __('Past Editor'),
                'title'    => __('Past Editor'),
                'name'     => 'past',
                'required' => true,
                'options'  => $model->getAvailablePast(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'display_home', 
            'select', 
            [
                'label'    => __('Display on Home'),
                'title'    => __('Display on Home'),
                'name'     => 'display_home',
                'required' => true,
                'options'  => $model->getDisplayHome(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'signature',
            'image',
            [
                'name' => 'signature',
                'label' => __('Signature'),
                'title' => __('Signature'),
                'required'  => false,
                'disabled' => $isElementDisabled
            ]
        );
        
        $field = $fieldset->addField(
            'color',
            'text',
            [
                'name' => 'color',
                'label' => __('Color'),
                'title' => __('Color')
            ]
        );
        $renderer = $this->getLayout()->createBlock('Hhmedia\Editor\Block\Adminhtml\Color');
        $field->setRenderer($renderer);
        /*$dateFormat = $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT);
        $fieldset->addField('published_at', 'date', [
            'name'     => 'published_at',
            'date_format' => $dateFormat,
            'image'    => $this->getViewFileUrl('images/grid-cal.gif'),
            'value' => $model->getPublishedAt(),
            'label'    => __('Publishing Date'),
            'title'    => __('Publishing Date'),
            'required' => true
        ]);*/
        
        $this->_eventManager->dispatch('adminhtml_editor_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Editor Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Editor Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    protected function _getAdditionalElementTypes()
    {
        return ['signature' => 'Hhmedia\Editor\Block\Adminhtml\Form\Element\Signature'];
    }

}
