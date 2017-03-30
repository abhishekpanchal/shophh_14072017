<?php
 
namespace Hhmedia\Tags\Model\Config\Source;
 
//use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
//use Magento\Framework\DB\Ddl\Table;
 
class Options extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    
    //protected $optionFactory;
 
    public function getAllOptions()
    {
        $obj = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $obj->create('Hhmedia\Tags\Helper\Data');
        $tags = $helper->getCollection();
        foreach($tags as $tag){
            $label = $tag->getTitle();
            $value = $tag->getTagsId();
            $option[] = array('label'=>$label,'value'=>$value);
        }
        $this->_options = $option;
    
        return $this->_options;
    }
 
    /*public function getOptionText($value)
    {
        foreach ($this->getAllOptions() as $option) {
            if ($option['value'] == $value) {
                return $option['label'];
            }
        }
        return false;
    }
 
    public function getFlatColumns()
    {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        return [
            $attributeCode => [
                'unsigned' => false,
                'default' => null,
                'extra' => null,
                'type' => Table::TYPE_INTEGER,
                'nullable' => true,
                'comment' => 'Custom Attribute Options  ' . $attributeCode . ' column',
            ],
        ];
    }*/
}