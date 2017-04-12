<?php  
namespace RebelShipper\CanadaPost\Model\Config\Source;
/**
 * @category   RebelShipper
 * @package    RebelShipper_CanadaPost
 * @author     iam@rebelshipper.com
 * @website    http://www.rebelshipper.com
 */
class DimensionConversion {
    
    public function toOptionArray() {
    $options = array(
    array(
               'value' => "0.393700787",
               'label' => "Product Dimensions In CM"
            ),
    array(
               'value' => "1",
               'label' => "Product Dimensions In Inches"
            ),
         );
        return $options;
    }

  
}
