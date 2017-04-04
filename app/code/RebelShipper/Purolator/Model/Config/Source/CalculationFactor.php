<?php  
namespace RebelShipper\Purolator\Model\Config\Source;
/**
 * @category   RebelShipper
 * @package    RebelShipper_Purolator
 * @author     iam@rebelshipper.com
 * @website    http://www.rebelshipper.com
 */
class CalculationFactor {
    
    public function toOptionArray() {
    $options = array(
    array(
               'value' => "15",
               'label' => "Express"
            ),
    array(
               'value' => "10",
               'label' => "Ground"
            ),
         );
        return $options;
    }

  
}
