<?php

namespace Unirgy\DropshipBatch\Block\Adminhtml\Dist\Grid;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\Source;

class Status
    extends AbstractRenderer
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Source
     */
    protected $_modelSource;

    public function __construct(Context $context, 
        HelperData $helperData, 
        Source $modelSource, 
        array $data = [])
    {
        $this->_helperData = $helperData;
        $this->_modelSource = $modelSource;

        parent::__construct($context, $data);
    }

    /**
     * Renders grid column
     *
     * @param   DataObject $row
     * @return  string
     */
    public function render(DataObject $row)
    {
        $hlp = $this->_helperData;

        $key = $this->getColumn()->getIndex();
        $value = $row->getData($key);

        switch ($key) {
        case 'dist_status':
            $classes = [
                'pending'    => 'notice',
                'processing' => 'major',
                'exporting'  => 'major',
                'importing'  => 'major',
                'success'    => 'notice',
                'empty'      => 'minor',
                'error'      => 'critical',
                'canceled'   => 'minor'
            ];
            $labels = $this->_modelSource->setPath('dist_status')->toOptionHash();
            break;

        case 'batch_status':
            $classes = [
                'pending'    => 'notice',
                'scheduled'  => 'notice',
                'missed'     => 'minor',
                'processing' => 'major',
                'exporting'  => 'major',
                'importing'  => 'major',
                'empty'      => 'minor',
                'success'    => 'notice',
                'partial'    => 'minor',
                'error'      => 'critical',
                'canceled'   => 'minor',
            ];
            $labels = $this->_modelSource->setPath('batch_status')->toOptionHash();
            break;

        default:
            return $value;
        }

        return '<span class="grid-severity-'.$classes[$value].'" '.(!empty($styles[$value])?' style="'.$styles[$value].'"':'').'><span style="white-space:nowrap">'
            .$labels[$value]
            .'</span></span>';
    }
}