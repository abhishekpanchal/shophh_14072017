<?php  
namespace RebelShipper\CanadaPost\Model\Config\Source;
/**
 * @category   RebelShipper
 * @package    RebelShipper_CanadaPost
 * @author     iam@rebelshipper.com
 * @website    http://www.rebelshipper.com
 */

class WeightConversion {
    
    public function toOptionArray() {
    $options = array(
    array(
               'value' => "2.20462262",
               'label' => "Product Weights In KG"
            ),
    array(
               'value' => "1",
               'label' => "Product Weights In LB"
            ),
         );
        return $options;
    }

  
}
