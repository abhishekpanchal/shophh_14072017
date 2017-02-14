<?php
namespace Bg\Freshdesk\Block\Adminhtml\Createticket\Edit;
 
use \Magento\Backend\Block\Widget\Form\Generic;
use Bg\Freshdesk\Helper\Data as HelperData;
class Form extends Generic
{

protected $_systemStore;
protected $_helper;
 
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
        array $data = []
    ) {
	//$this->_helper=$helperdata;
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }
 
   
    protected function _construct()
    {
        parent::_construct();
        $this->setId('department_form');
        $this->setTitle(__('Ticket Information'));
    }


 
protected function _prepareForm()
    {
        
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
 
        $form->setHtmlIdPrefix('pmam_');

$fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Ticket Information'), 'class' => 'fieldset-wide']
        );

$elements = [];

$allfields = $this->getAvailableFields();

foreach($allfields as $k=>$v){

$textoption=array();
$options='';
$name = $v['name'];

switch($v['type']){

	case 'custom_checkbox':
	$type="checkbox";
	break;
	case 'custom_paragraph':
	$type="textarea";
	break;

	case 'custom_date':
	$type="date";
	$textoption['class'] = 'validate-date';
	
	$textoption['singleClick'] = true;
	$textoption['date_format'] = 'yyyy-MM-dd';
	$textoption['time'] = false;
	
	break;

	default:
	$type='text';

}
if(array_key_exists('choices',$v)){
	$type='select';
	$options=$v['choices'];
}

if($name=="description"){
	//$type="textarea";
	$type="editor";
	$textoption['wysiwyg']=true;
}

$textoption['name']=$name;
$textoption['label']=$v['label_for_customers'];
$textoption['title']=$v['description'];

if($v['required_for_customers']==1){
	$textoption['required']=true;
}

if($name=="status"){
	$textoption['required']=true;
}

if($name=="priority"){
	$textoption['required']=true;
	$textoption['value']=1;
}



if(array_key_exists('choices',$v)){

	if($name=='status'){
		//$chopt=array(''=>'--');
		foreach($v['choices'] as $fk=>$fv){
			$chopt[$fk]=$fv[0];
		}
		$textoption['options']=$chopt;
	}
	elseif($name=='ticket_type'){
		$chopt2=array(''=>'--');
		foreach($v['choices'] as $fv2){
		$chopt2[$fv2]=$fv2;
		}
		$textoption['options']=$chopt2;
	}
	else{

		if($v['type']=='nested_field'){

		}else{
			
			$chopt3=array(''=>'--');
			if($v['default']==1){
				foreach($v['choices'] as $fk=>$fv){
					$chopt3[$fv]=$fk;
				}
			}else{
				foreach($v['choices'] as $fk=>$fv){
					$chopt3[$fk]=$fv;
				}
			}
				
			$textoption['options']=$chopt3;
		}
	}

}

if($name=="requester"){
$textoption['class'] = 'validate-email';
}

if($v['type']=='nested_field'){

}else{


$fieldset->addField(
            $name,
            $type,
	    $textoption
        );

}

}
 
        
 
        $form->setUseContainer(true);
        $this->setForm($form);
 
        return parent::_prepareForm();
    }

public function getAvailableFields()
    {
       return $this->_coreRegistry->registry('fdfields');
    }

}
