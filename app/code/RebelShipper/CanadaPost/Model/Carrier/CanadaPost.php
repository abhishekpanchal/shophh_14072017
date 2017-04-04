<?php

namespace RebelShipper\CanadaPost\Model\Carrier;

use Magento\Framework\Module\Dir;
use Magento\Framework\Xml\Security;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Tracking\Result as TrackingResult;

/*
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Framework\Module\Dir\Reader;
use Magento\Store\Model\Information;
use Magento\Directory\Model\Region;
use Psr\Log\LoggerInterface;
*/

/**
 * .03 Fixed issue with soap faults causing 500 error codes
 * .02 Fixed issue with dimensional keys
 * @category   RebelShipper
 * @package    RebelShipper_CanadaPost
 * @author     iam@rebelshipper.com
 * @website    http://www.rebelshipper.com
 */
//class CanadaPost extends AbstractCarrierOnline implements \Magento\Shipping\Model\Carrier\CarrierInterface
class CanadaPost extends AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'rscanadapost';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateFactory;
    
    protected $_storeInfo;
    protected $_region;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;
    
    /**
     * Base path of module to find WDSL path
     * @var string
     */
    protected $_basepath='';
    protected $_devUrl = 'http://devwebservices.canadapost.com';
    protected $_liveUrl = 'http://webservices.canadapost.com';

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Store\Model\Information $storeInformation,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $logger->pushHandler( new \Monolog\Handler\StreamHandler( BP . '/var/log/RebelShipper_CanadaPost.log'));
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);

/*
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
*/
            $this->_xmlSecurity=$xmlSecurity;
            $this->_xmlElFactory=$xmlElFactory;
            $this->_rateFactory=$rateFactory;
            $this->_rateMethodFactory=$rateMethodFactory;
            $this->_trackFactory=$trackFactory;
            $this->_trackErrorFactory=$trackErrorFactory;
            $this->_trackStatusFactory=$trackStatusFactory;
            $this->_regionFactory=$regionFactory;
            $this->_countryFactory=$countryFactory;
            $this->_currencyFactory=$currencyFactory;
            $this->_directoryData=$directoryData;
            $this->_stockRegistry=$stockRegistry;
            
        $this->_storeInfo = $storeInformation;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_productLoader = $_productloader;
        $this->_currency = $currency;
        $this->productRepository = $productRepository;

        $this->_basepath=$configReader->getModuleDir("etc","RebelShipper_CanadaPost");
        
    }

    /**
     * Check if city option required
     *
     * @return boolean
     */
    public function isCityRequired()
    {
        return false;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTracking($trackings) {
      if (!is_array($trackings)) {
        $trackings=array($trackings);
      }
      $result = $this->_trackFactory->create();
      foreach($trackings as $trackingnum) {
        $tracking = $this->_trackStatusFactory->create();
        $tracking->setCarrier($this->_code);
        $tracking->setCarrierTitle($this->getConfigData('title'));
        $tracking->setUrl("http://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=".$trackingnum);
        $result->append($tracking);
      } // end for
      return $tracking;
    } // and tracking


    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */

    public function collectRates(RateRequest $request) {
      
      if (!$this->isActive() ||  $request->getDestPostcode()==".") {
          return false;
      }


      try {
      $this->_debug("Fetching config");
      $config =$this->_getConfig($request);

      $this->_debug("Checking cart contents");
      $config->runningPrice=0;
      $config->totalItems=0;
      $config->extraDays = 0;

      $cartWrapper = new CartWrapper("2",$request,$this);
      $cartWrapper->iterate($this,"itemCallback",$config);
      
      usort($config->shipments,array($this,"shipmentsSort"));    
      if ($config->properties->max_items_to_quote>0 &&
          $config->totalItems>$config->properties->max_items_to_quote) {
        $this->_debug("Too many items to quote ".$config->totalItems); 
        // Do not return an error, just an empty quote
        return $config->result;      
      }
      if (!$config->isError && count($config->shipments)>0) {      
        $this->shipItems($config);
      }

      

if ($config->isError) {
          // Present error information
          //$error = Mage::getModel('shipping/rate_result_error');
          //$error->setCarrier($this->_code);
          //$error->setCarrierTitle($this->getConfigData('title'));
          //$error->setErrorMessage($this->getConfigData('specificerrmsg') . " - ".$config->errorText);
          //$config->result->append($error);
          
          $this->createError($config, $config->errorText);

        } else {
            // Set items in magento
            foreach($config->quotes as $method=>$quote) {
            
              $rate = $this->_rateMethodFactory->create();
              $rate->setCarrierTitle($this->getConfigData('title'));
              $rate->setCarrier($this->_code);
              $methodTitle = $quote["name"] . $quote["date"];
              $rate->setMethod($method);
              $totalPrice = $quote["price"] ;
              $totalPrice = $this->getFinalPriceWithHandlingFee($totalPrice);
              // $totalPrice = $this->getMethodPrice($totalPrice / $this->_numBoxes, $method) + $config->properties->handling_fixed_fee;
              if ($config->properties->minimum_shipping_cost>0 &&
                  $totalPrice<$config->properties->minimum_shipping_cost) {
                   $totalPrice= $config->properties->minimum_shipping_cost;
              }
              $rate->setMethodTitle($methodTitle);
              if ($config->properties->json_description) {
                $rate->setMethodDescription(
                  json_encode($quote["communication"]));
              }
              
/*
Provide currency conversion based on store
// the price
$amt=1000;
// Base Currency ('INR')
$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
// Current Currency ('USD')
$currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

// Allowed currencies
$allowedCurrencies = Mage::getModel('directory/currency')->getConfigAllowCurrencies();
$rates = Mage::getModel('directory/currency')->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
// the price converted
$amt= $amt/$rates[$currentCurrencyCode];


*/

              // $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
              $baseCurrencyCode = $this->_storeManagerInterface->getStore()->getCurrentCurrency()->getCode();
              if ($baseCurrencyCode!="CAD") {
                // We need to convert to the base currency code so that the amount is properly calculated
                $rates = $this->_currency->getCurrencyRates($baseCurrencyCode, ["CAD"]);
                $totalPrice = $totalPrice / $rates["CAD"];
              }
              
              $rate->setPrice($totalPrice);
              $config->result->append($rate);
            }
        }

      } catch(Exception $exception) {
        // Log the error
        $config->responseError="CanadaPost server reported an error on this quote, this information has been logged. ";
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($this->getConfigData('specificerrmsg') . " - ".$config->responseError);
        $config->result->append($error);
        $this->carriererror("Error reported by canadapost " .$exception->getMessage());
      }

      return $config->result;
    }
    
    
    public function fitInBox($currentBox,&$boxes, &$tryingToPack, &$packer, &$config) {
      $startBox = $currentBox;
      $maxBox=count($boxes);
      $dimensions = $tryingToPack->dimensions;
      
      $this->shipLog($config, "Trying. ",$startBox); 
      while (!$packer->add_item($tryingToPack)) {                                  
        // Pick next size larger box
        $currentBox++;

        if ($currentBox==$maxBox) {
          return -1;
        }
        $packer->removeBoxes();
        $packer->add_box($boxes[$currentBox]);
        while(!$packer->fitItems()) {
          // If items dont fit any more, then change box again
          $currentBox++;
          if ($currentBox==$maxBox) {
            // Reset packer to original container
            $packer->removeBoxes();
            $packer->add_box($boxes[$startBox]);
            $packer->fitItems();
            
            return -1;
          }
          $packer->removeBoxes();
          $packer->add_box($boxes[$currentBox]);
        }
		$this->shipLog($config, "Fits. ",$boxes[$currentBox]); 
      }
      return $currentBox;
    }
    public function shipItems(&$config) {
      // Shippable items are presorted so we can start with the box calculations
      $packer = new Deviant_Packer();
      $boxes = $config->boxes;
      $this->shipLog($config, "Boxes ",$boxes); 
      
      $shipments = $config->shipments;
      $maxBox = count($boxes);
      $usedBoxes = array();
      
      $firstRun=true;
      $moreShipments = true;
      while($moreShipments) {
        $packer->reset();
        $currentBox=0;
        $packer->add_box($boxes[$currentBox]);
        
        $x=count($shipments)-1;
        $tryingToPack = &$shipments[$x];
        $tryingToPackQty = $tryingToPack->qty;
        
        while($x>-1 && $tryingToPack->qty==0) {
          $x--;
          $tryingToPack = &$shipments[$x];
          $tryingToPackQty = $tryingToPack->qty;
        }
        
        while($x>-1) {
          $upped = false;
          while($tryingToPackQty>0) {
            $this->shipLog($config, "fitInBox ? ",$currentBox); 
            $boxToFit = $this->fitInBox($currentBox, $boxes, $tryingToPack, $packer,$config);
            if ($boxToFit>-1) {
              $this->shipLog($config, "Item fits in this box. ",$boxToFit); 
              $currentBox = $boxToFit;
              $tryingToPackQty--;
            } else {
              $this->shipLog($config, "Item cannot fit in this box. ",$boxes[$currentBox]); 
              break;
            }
          }
          
          
          if ($boxToFit<0) {
            // Item to large to fit in box, check to see if 
            // item is the only item being packed, if so this needs to be shipped by itself
            if (count($packer->outer_boxes[0]->items)==0) {
                
            
              $usedBoxes[] = $currentBoxedItem=(object)array(
                  "dimensions"=>$shipments[$x]->dimensions, 
                  "contents"=>array((object)array(
                    "dimensions" => array($tryingToPack->dimensions[0],$tryingToPack->dimensions[1],$tryingToPack->dimensions[2]),
                    "weight"=>$tryingToPack->weight,
                    "qty"=>1,
                    "itemid" => $tryingToPack->itemid,
                    "itemname"=>$tryingToPack->itemname,
                    "itemPrice"=>$tryingToPack->itemPrice,
                    "weight"=>$tryingToPack->weight,
                    "totalVolume"=>($tryingToPack->dimensions[0]*$tryingToPack->dimensions[1]*$tryingToPack->dimensions[2]),
                    )),
                  "weight"=>$tryingToPack->weight,
                  "qty"=>$tryingToPack->qty,
                  "itemPrice"=>$tryingToPack->itemPrice,
                  "communication"=>(object)array("request"=>false,"response"=>false),
                  "boxPrice"=>0,
                  );
              $tryingToPack->qty=0;
              $x--;
              if ($x>-1) {
                $tryingToPack = &$shipments[$x];
                $tryingToPackQty = $tryingToPack->qty;            
              }
              $this->shipLog($config, "No box to fit item, shipping item as is. ", $currentBoxedItem); 
              continue;
            }
            
          } else {
            $currentBox=$boxToFit;
            
          }
          
          $shipments[$x]->qty = $tryingToPackQty;
          // Go to next largest item
          $x--;          
          if ($x>-1) {
            $tryingToPack = &$shipments[$x];
            $tryingToPackQty = $tryingToPack->qty;            
          }
        } // end of items to ship
        
        // Check to see if current box can be made smaller
        if ($boxToFit>-1) {
          // Items fit in current box. see if we can reduce the container size.
          while($boxToFit>0) {
            $boxToFit--;
            $packer->removeBoxes();
            $packer->add_box($boxes[$boxToFit]);
            if (!$packer->fitItems()) {
              $packer->removeBoxes();
              $packer->add_box($boxes[$boxToFit+1]);
              $packer->fitItems();
              
              break;
            }
          }
        }
        
        
        // Determine if more items need to be shipped
        $moreShipments = false;
        foreach($shipments as $item) {
          if ($item->qty>0) {
            $moreShipments = true;
            break;
          }
        }
        
        // Create a new shipment box
        if (count($packer->outer_boxes[0]->items)>0) {
          $usedBoxes[] = $currentBoxedItem=(object)
            array("dimensions"=>$packer->outer_boxes[0]->dimensions,
                  "weight"=>$packer->outer_boxes[0]->currentWeight + $packer->outer_boxes[0]->boxWeight,
                  "maxWeight"=>$packer->outer_boxes[0]->maxWeight,
                  "itemPrice"=>0,
                  "boxPrice"=>$packer->outer_boxes[0]->boxPrice,
                  "communication"=>(object)array("request"=>false,"response"=>false),
              "contents"=>$packer->key_inner_boxes,"qty"=>1);
          $this->shipLog($config, "Box added. ", $currentBoxedItem); 
              
          // Check to see if same items are shippable
          if ($moreShipments) {
          
            // Make a clone of the current boxed item so we can determine if the counts exist yet in the shipment
            $makeAnother = true;
            while($makeAnother) {
              $itemToChange=array();
              foreach($shipments as &$item) {
                if (!isset($packer->key_inner_boxes[$item->itemid]) ||
                    $packer->key_inner_boxes[$item->itemid]->qty > $item->qty) {
                  $makeAnother = false;
                  break;                
                } else {
                  $itemToChange[$item->itemid]=$item;
                }
              }
              unset($item);
              if ($makeAnother) {
                foreach($itemToChange as &$item) {
                  $item->qty -= $packer->key_inner_boxes[$item->itemid]->qty;
                  
                }
                unset($item);
                $currentBoxedItem->qty++;
                $this->shipLog($config, "Duplicated box contents. ", $currentBoxedItem); 
              }
            }
          }
          foreach($currentBoxedItem->contents as &$item) {
            $currentBoxedItem->itemPrice += $item->itemPrice * $item->qty;
          }
          unset($item);
          $currentBoxedItem->itemPrice*=$currentBoxedItem->qty;
        } 
        
                  
        
        
        $moreShipments = false;
        foreach($shipments as &$item) {
          if ($item->qty>0) {
            $moreShipments = true;
            break;
          }
        }
        unset($item);
        $firstRun=false;
      }
      
      // Everything was shipped, check 
      $this->shipLog($config, "Shipping boxes", $usedBoxes);
      
      
      
    
    $mailedBy = $config->properties->ccpnumber;
    
    $originPostalCode = $config->origin->postalCode;
    $destinationCountryCode = $config->dest->countryCode;
    if ($config->request->getDestCountryId()) {
        $destinationCountryCode = $config->request->getDestCountryId();
    }
    else {
        $destinationCountryCode = self::USA_COUNTRY_ID;
    }
    
    $postCode=strtoupper($config->request->getDestPostcode());
    // Test postal code
//    ^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$
	$ZIPREG=array(
		"US"=>"^\d{5}([\-]?\d{4})?$",
		"UK"=>"^(GIR|[A-Z]\d[A-Z\d]??|[A-Z]{2}\d[A-Z\d]??)[ ]??(\d[A-Z]{2})$",
		"DE"=>"\b((?:0[1-46-9]\d{3})|(?:[1-357-9]\d{4})|(?:[4][0-24-9]\d{3})|(?:[6][013-9]\d{3}))\b",
		"CA"=>"^([ABCEGHJKLMNPRSTVXY]\d[ABCEGHJKLMNPRSTVWXYZ])\ {0,1}(\d[ABCEGHJKLMNPRSTVWXYZ]\d)$",
		"FR"=>"^(F-)?((2[A|B])|[0-9]{2})[0-9]{3}$",
		"IT"=>"^(V-|I-)?[0-9]{5}$",
		"AU"=>"^(0[289][0-9]{2})|([1345689][0-9]{3})|(2[0-8][0-9]{2})|(290[0-9])|(291[0-4])|(7[0-4][0-9]{2})|(7[8-9][0-9]{2})$",
		"NL"=>"^[1-9][0-9]{3}\s?([a-zA-Z]{2})?$",
		"ES"=>"^([1-9]{2}|[0-9][1-9]|[1-9][0-9])[0-9]{3}$",
		"DK"=>"^([D-d][K-k])?( |-)?[1-9]{1}[0-9]{3}$",
		"SE"=>"^(s-|S-){0,1}[0-9]{3}\s?[0-9]{2}$",
		"BE"=>"^[1-9]{1}[0-9]{3}$",
		"IN"=>"^\d{6}$"
	);    
    
    if (false && isset($ZIPREG[$destinationCountryCode])) {
		if (preg_match_all("/".$ZIPREG[$destinationCountryCode]."/i", $postCode)!==1) {
			$config->isError=true;
			$config->errorText="  Invalid zip code ($destinationCountryCode, $postCode)";
			return false;        
		}
    }
    switch($destinationCountryCode) {
      case "CA":
        $destination=array("domestic"=>array("postal-code"=>str_replace(" ","",str_replace("-","",$postCode))));
      break;
      case "US":
        $destination=array("united-states"=>array("zip-code"=>$postCode));
      break;
      default:
        $destination=array("international"=>array("country-code"=>$destinationCountryCode));
      break;
    }
    
    $locale = $config->language;
    
    $firsttime = true;
    // Two arrays, see reason below
    $serviceList=array();
    $quoteList=array();
    // echo "c".count($likeBoxItems).",".count($config->shippable->boxes);
    $this->_numBoxes=0;
    foreach($usedBoxes as $boxKey=>$box) {
    
      if ($config->properties->contract_id) {
        $call = array(
            'get-rates-request' => array(
                'locale'            => $locale,
                'mailing-scenario'          => array(
                    'customer-number'           => $mailedBy,
                    'contract-id'=>$config->properties->contract_id,
                    "expected-mailing-date"=>date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+$config->properties->days_for_handling + $config->extraDays, date("Y"))),
                    //"options"=>$options,
                    'parcel-characteristics'    => array(
                        'weight'                    => round($box->weight,3),
                      'dimensions'=>array(
                        'length'                    => round($box->dimensions[0],1),
                        'width'                    => round($box->dimensions[1],1),
                        'height'                    => round($box->dimensions[2],1)),
                    ),
                    //"services"=>array(array("service-code"=>"DOM.RP")),
                    'services' =>$config->services,
                    'origin-postal-code'        => $originPostalCode,
                    'destination'           => $destination
                )
            ));
        
      }
      else {
      $call = array(
          'get-rates-request' => array(
              'locale'            => $locale,
              'mailing-scenario'          => array(
                  'customer-number'           => $mailedBy,
                  "expected-mailing-date"=>date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")+$config->properties->days_for_handling + $config->extraDays, date("Y"))),
                  //"options"=>$options,
                  'parcel-characteristics'    => array(
                      'weight'                    => round($box->weight,3),
                      'dimensions'=>array(
                        'length'                    => round($box->dimensions[0],1),
                        'width'                    => round($box->dimensions[1],1),
                        'height'                    => round($box->dimensions[2],1)),
                  ),
                  //"services"=>array(array("service-code"=>"DOM.RP")),
                  'services' =>$config->services,
                  'origin-postal-code'        => $originPostalCode,
                  'destination'           => $destination
              )
          ));
      }
      if ($config->properties->shipping_insurance && $box->itemPrice>0) {
        $call['get-rates-request']
             ['mailing-scenario']
             ["options"]=array("option"=>array(array("option-code"=>"COV","option-amount"=>$box->itemPrice,),
                                               //array("option-code"=>"DC","option-amount"=>$box->itemPrice,)
                                               ));
      }
       
      $this->shipLog($config, "Request", $call);
      if ($box->qty<1) {
        $this->shipLogError($config, "Request error ", $box);
        throw new \Exception("Invalid box request");
      }
      $this->_numBoxes+=$box->qty;
try {

      $result = $config->client->__soapCall('GetRates', $call, NULL, NULL);
} catch (SoapFault $exception) {
        //echo $exception;
        die("here");
}
if (is_soap_fault($result)) {
  $this->createError($config, $result->faultstring);
  return;
}
//      $result = $config->client->GetRates($call);
      
      //print_r(array($call,$result));die();
      $this->shipLog($config, "Response", $result);
      
        if ( isset($result->{'price-quotes'}) ) {
          if ($firsttime) {
            $firsttime=false;
            foreach ( $result->{'price-quotes'}->{'price-quote'} as $priceQuote ) { 
              // Skip services 
              // if (!in_array($priceQuote->{'service-code'},$config->services["service-code"])) continue;
              
              $amount = $priceQuote->{'price-details'}->{'due'};
              $tax = 0.0;
              if (isset($priceQuote->{'price-details'}->{'taxes'})) {
                foreach( $priceQuote->{'price-details'}->{'taxes'} as $taxe) {
                  $tax += floatval($taxe->{"_"});
                }
              }
              
              $amount= $amount - $tax + $box->boxPrice;
              $quoteDate = "";
              if (isset($priceQuote->{'service-standard'}->{'expected-delivery-date'})) {
                $actualDate = $priceQuote->{'service-standard'}->{'expected-delivery-date'};
                // Parse the quoted date as 
                if ($config->properties->useAlternativeEtd && date_create_from_format ( "Y-m-j" , $actualDate )!==FALSE) {
                    $totalDays = (date_create_from_format ( "Y-m-j" , $actualDate )->getTimestamp()- time()) / 86400;
                    $startDate = new \DateTime(); //date_create_from_format ( "Y-m-j" , $actualDate );
                    
                    $newTotalDays = 0;
                    for ($x=0;$x<$totalDays;$x++) {
                      $startDate->add(new \DateInterval("P1D"));
                      if ($startDate->format("w")>0 && $startDate->format("w")<6) {
                        $newTotalDays ++;
                      } 
                    }
                    
                    $days = ceil($totalDays - 2* ceil($totalDays)/7);
                    // now we need to count through those dates
                     
                    $quoteDate = " ($newTotalDays " . translate($config,"Business Days") .")" ;
                    $info = array("quote"=>$quoteDate,"total"=>$totalDays,"bdays"=>$newTotalDays, "Actual"=> $actualDate );
                    $this->shipLog($config, "Quote date ", $info);
                    
                 } else {
                    $quoteDate = " (ETD " . $actualDate .")";
                 }
                 
                
              }
              $quoteList[$priceQuote->{'service-code'}]=array("price"=>$amount*$box->qty,
                                                            "name"=>$priceQuote->{'service-name'},
                                                            "date"=>$quoteDate,
                                                            "quote"=>$priceQuote,
                                                            "communication"=>array(array("request"=>$call,"response"=>$result,"box"=>$box)),
                                                          ); 
            }
          }
          else {
            // As we iterate through matching quotes we want to 
            // Ensure we remove any quotes that may not be applicable for all items
            foreach ( $result->{'price-quotes'}->{'price-quote'} as $priceQuote ) { 
              $amount = $priceQuote->{'price-details'}->{'due'};
              $tax = 0.0;
              if (isset($priceQuote->{'price-details'}->{'taxes'})) {
                foreach( $priceQuote->{'price-details'}->{'taxes'} as $taxe) {
                  $tax += floatval($taxe->{"_"});
                }
              }
              $amount= $amount - $tax + $box->boxPrice;
            
              if (isset($quoteList[$priceQuote->{'service-code'}])) {
                $serviceList[$priceQuote->{'service-code'}]=$quoteList[$priceQuote->{'service-code'}];
                $serviceList[$priceQuote->{'service-code'}]["communication"][]=array("request"=>$call,"response"=>$result,"box"=>$box);
                
                $serviceList[$priceQuote->{'service-code'}]["price"]=$serviceList[$priceQuote->{'service-code'}]["price"]+$amount*$box->qty;
              }
            }
            $quoteList = $serviceList;
            $serviceList = array();
          }
        } else {
          $errors="";
            foreach ( $result->{'messages'}->{'message'} as $message ) {
                $errors .= 'Error Code: ' . $message->code . "<br />";
                $errors .= 'Error Msg: ' . $message->description . "<br /><br />";
            }
            $this->createError($config, $errors);
            return;
        }
    }
    
    // Return shipments if any
    $config->quotes=$quoteList;
  }
  
  private function createError(&$config, $errrors) {
      $error = $this->_rateErrorFactory->create();
      $error->setCarrier($this->_code);
      $error->setCarrierTitle($this->getConfigData('title'));
      $error->setErrorMessage($this->getConfigData('specificerrmsg') . ($config->properties->supress_error_description ? "":$errrors ));
      $this->shipLog($config, "Errors " .$errrors );        
      $config->result->append($error);
  }
    
    
    /**
     * Sorts the shippable items by largest to smallest
     **/
    public function shipmentsSort($itema, $itemb) {
      $result = $itema->dimensions[0]-$itemb->dimensions[0];
      if ($result==0) {
        $result = $itema->dimensions[1]-$itemb->dimensions[1];
      }
      if ($result==0) {
        $result = $itema->dimensions[2]-$itemb->dimensions[2];
      }
      return $result;
    }    
    
    
    

    public function itemCallback($productData, $config) {
        rsort($productData->dimensions);
        $config->totalItems+=$productData->qty;
        if ($productData->weight>0) {
          $config->shipments[]=$productData; 
        }
    }
    
    
    
    
    public function loadMyProduct($sku)
    {
        return $this->productRepository->get($sku);
    }

    public function shipLog(&$config, $message, &$item=false) {
      $this->_debug(array("message"=>$message,"data"=>$item));
    }
    // Loggers
    public function shipLogError(&$config, $message, &$item=false) {
      $this->_logger->error(array("message"=>$message,"data"=>$item));
    }
    
    
    protected function _getConfig(RateRequest $request) {
      $config=(object)array("isError"=>false,"errorText","rootPath"=>$this->_basepath,"calculate_dimension_weight"=>1);
      $config->properties=(object) array(
      "max_package_weight"=>$this->getConfigData("max_package_weight"),
      "developmentusername"=>$this->getConfigData("developmentusername"),
      "developmentpassword"=>$this->getConfigData("developmentpassword"),
      "productionusername"=>$this->getConfigData("productionusername"),
      "productionpassword"=>$this->getConfigData("productionpassword"),
      "ccpnumber"=>$this->getConfigData("ccpnumber"),
      "productionmode"=>$this->getConfigData("productionmode")==1,
      "log_mode"=>$this->getConfigData("log_mode")==1,
      "json_description"=>$this->getConfigData("json_description")==1,
      "supress_error_description"=>$this->getConfigData("supress_error_description")==1,
      
      "shippingcontainers"=>($containers =$this->getConfigData("shippingcontainers")),
      "dimensionalGroupData"=>$this->getConfigData("dimensional_group_data"),     // The dimensional group data
      "useAlternativeEtd"=>$this->getConfigData("use_alternate_etd"),     
      "widthattribute"=>$this->getConfigData("widthattribute"),
      "heightattribute"=>$this->getConfigData("heightattribute"),
      "lengthattribute"=>$this->getConfigData("lengthattribute"),
      "defaultheight"=>$this->getConfigData("defaultheight"),
      "defaultlength"=>$this->getConfigData("defaultlength"),
      "defaultwidth"=>$this->getConfigData("defaultwidth"),
      "convert_size"=>$this->getConfigData("convert_size"),
      "convert_weight"=>$this->getConfigData("convert_weight"),
      "days_for_handling"=>$this->getConfigData("days_for_handling"),
      "allowed_methods"=>$this->getConfigData("allowed_methods"),
      "handling_fixed_fee"=>$this->getConfigData("handling_fixed_fee"),
      "max_items_to_quote"=>$this->getConfigData("max_items_to_quote"),
      "minimum_shipping_cost"=>$this->getConfigData("minimum_shipping_cost"),
      "shipping_insurance"=>$this->getConfigData("shipping_insurance")==1,
      "json_description"=>$this->getConfigData("json_description")==1,
      "ship_as_bundled"=>$this->getConfigData("ship_as_bundled")==1,
      "contract_id"=>$this->getConfigData("contract_id"),
      "locale_choose" => $this->getConfigData("locale_choose"),
    );

    $config->translation=
      array(
        "Business Days"=>array("fr"=>"jours ouvrables")
      );
              
      
      
      /** @var \Magento\Framework\ObjectManagerInterface $om */
      $om = \Magento\Framework\App\ObjectManager::getInstance();
      /** @var \Magento\Framework\Locale\Resolver $resolver */
      $resolver = $om->get('Magento\Framework\Locale\Resolver');
      $storeLocale = $resolver->getLocale();
      $config->language = "en";
      if ($config->properties->locale_choose=="fr") {
        $config->language = "fr";
      }
      else if ($config->properties->locale_choose=="choose") {
        if ($storeLocale == "fr_CA" || $storeLocale == "fr_FR") {
          $config->language = "fr";
        }
      }
      
      
        if ($request->getOrigCountry()) {
            $origCountry = $request->getOrigCountry();
        } else {
            $origCountry = $this->_scopeConfig->getValue(
                \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $request->getStoreId()
            );
        }
        $origCountry = $this->_countryFactory->create()->load($origCountry)->getData('iso2_code');
        if ($request->getOrigPostcode()) {
            $origPostal=$request->getOrigPostcode();
        } else {
            $origPostal =
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                );
        }
      
        if ($request->getOrigRegion()) {
            $origRegionID=$request->getOrigRegion();
        } else {
            $origRegionID =
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_REGION_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                );
        }
        $origRegion = $this->_regionFactory->create()->load($origRegionID)->getCode();
        $origRegionName = $this->_regionFactory->create()->load($origRegionID)->getName();
      // $storeInfo = $this->_storeInfo->getStoreInformationObject($this->_storeManagerInterface->getStore());
        if ($request->getCity()) {
            $origCity=$request->getCity();
        } else {
            $origCity =
                $this->_scopeConfig->getValue(
                    \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_CITY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $request->getStoreId()
                );
        }
      
      
       $config->origin=(object) array("postalCode"=>str_replace(' ', '', $origPostal),
          "countryCode" => $origCountry,
          "regionID" => $origRegion,
          "city" => $origCity,
          "region" => $origRegion) ; //$this->_region->loadByName("",$config->originRegionID)->getCode();

          
       $destCity = $request->getDestCity();
       if ($request->getDestCountryId()) {
           $destCountry = $request->getDestCountryId();
       }
       else {
           $destCountry = self::USA_COUNTRY_ID;
       }
       
       $destRegion = $request->getDestRegionCode();
       if ($request->getDestPostcode()) {
           $destPostalCode = $request->getDestPostcode();
       }
       else {
         $destPostalCode='';
       }
       $config->dest=(object) array("postalCode"=>str_replace(' ', '', $destPostalCode),
          "countryCode" => $destCountry,
          "regionID" => $destRegion,
          "city" => $destCity,
          "region" => $destRegion) ;

    // Convert allowed methods
    $config->services=array("service-code"=>explode(",",$config->properties->allowed_methods));
    $config->quotes=array();
    $config->boxes=array();
    
    // Decode shipping containers
    $boxes = explode(",",$containers);
    foreach($boxes as $box) {
      $box = trim($box);
      if ($box=="") continue;
      $result=array();
      
      $test = preg_match_all('|([0-9.]+)kg([0-9.]+)x([0-9.]+)x([0-9.]+)x([0-9.]+)kg\$([0-9.]+)|', $box, $result, PREG_PATTERN_ORDER);
      if ($test &&
          count($result[0])==1 &&
          count($result[1])==1 &&
          count($result[2])==1 &&
          count($result[3])==1 &&
          count($result[4])==1 &&
          count($result[5])==1) {
            
         $config->boxes[]=$containerInfo=(object)array("maxWeight"=>$result[1][0],
                              "boxWeight"=>$result[5][0],
                              "boxPrice"=>$result[6][0],
                              "dimensions"=>array($result[2][0],$result[3][0],$result[4][0]));
         rsort($containerInfo->dimensions);
              
      } else {
        $config->isError=true;
        $config->errorText="Invalid container $box";
        return false;        
      }
    }
    $config->dimensionalData = false;
    $config->dimensionalKeys = false;
    if ($config->properties->dimensionalGroupData!="") {
        $dimensionalGroupData = json_decode($config->properties->dimensionalGroupData);
        // Extract the keys from the data
        if ($dimensionalGroupData===NULL) {
            $message = "Failed to decode dimensional group data - ";
            switch (json_last_error()) {
                    case JSON_ERROR_NONE:
                        $message .= ' - No errors';
                    break;
                    case JSON_ERROR_DEPTH:
                        $message .= ' - Maximum stack depth exceeded';
                    break;
                    case JSON_ERROR_STATE_MISMATCH:
                        $message .= ' - Underflow or the modes mismatch';
                    break;
                    case JSON_ERROR_CTRL_CHAR:
                        $message .= ' - Unexpected control character found';
                    break;
                    case JSON_ERROR_SYNTAX:
                        $message .= ' - Syntax error, malformed JSON:'. $config->properties->dimensionalGroupData;
                    break;
                    case JSON_ERROR_UTF8:
                        $message .= ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                    break;
                    default:
                        $message .= ' - Unknown error';
                    break;
                }
            throw new Exception($message);    
    
        }
        $dimensionalGroupData = (array)$dimensionalGroupData;
        foreach($dimensionalGroupData as $key=>$value) {
            $dimensionalGroupData[$key]=(array)$value;
        }
        // Now extract the keys
        $config->dimensionalKeys = array_keys($dimensionalGroupData);
        $config->dimensionalData = $dimensionalGroupData;
    }      // end decode shipping
    
    
    // Setup soap client service
    if ($config->properties->productionmode) {
      $hostName = 'soa-gw.canadapost.ca';
      $config->properties->username=$this->getConfigData("productionusername");
      $config->properties->password=$this->getConfigData("productionpassword");
    }
    else {
      $hostName = 'ct.soa-gw.canadapost.ca';
      $config->properties->username=$this->getConfigData("developmentusername");
      $config->properties->password=$this->getConfigData("developmentpassword");
    }
    
      
    $username = $config->properties->username;
    $password = $config->properties->password;
    $customerNumber = $config->properties->ccpnumber;
    

      $this->_debug(array("Config"=>$config));
    
      
    $location = 'https://' . $hostName . '/rs/soap/rating/v2';
    $opts = array('ssl' =>
        array(
            'verify_peer'=> $config->properties->productionmode,
            'cafile' => $config->rootPath . '/third-party/cert/cacert.pem',
            //'CN_match' => $hostName,
            'allow_self-signed' => true,

            'trace'               => true,
        )
    );
    

    $ctx = stream_context_create($opts);    
    $config->client = new \SoapClient($config->rootPath."/wsdl/rating.wsdl",
      array('location' => $location, 
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS, 
            'stream_context' => $ctx,
            'trace' => 1,
            'exceptions' => false,
            'cache_wsdl' => WSDL_CACHE_NONE,
            ));
    $validateheaders[] = new \SoapHeader ( 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd', 
                                        'Security', 
                                        array (
                                          "UsernameToken"=>array("Username"=>$username,
                                                                  "Password"=>$password
                                                                ) 
                                              )
                                      ); 

    $WSSENS = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
    $usernameToken = new \stdClass(); 
    $usernameToken->Username = new \SoapVar($config->properties->username, XSD_STRING, null, null, "Username", $WSSENS);
    $usernameToken->Password = new \SoapVar($config->properties->password, XSD_STRING, null, null, "Password", $WSSENS);
    $content = new \stdClass(); 
    $content->UsernameToken = new \SoapVar($usernameToken, SOAP_ENC_OBJECT, null, null, "UsernameToken", $WSSENS);
    $header = new \SOAPHeader($WSSENS, 'Security', $content);
    $config->client->__setSoapHeaders($header); 
      
      
      // Store other items in the configuration
      $config->request = $request;
      
      // Create the shipment request object
      /** @var \Magento\Shipping\Model\Rate\Result $result */
      $result = $this->_rateFactory->create();
      $config->result = $result;

      return $config;
    }

    /**
     * Returns array of key-value pairs of all available methods
     * @return array
     */
    public function getAllowedMethods()
    {
        return array(
        
            'standard'  =>  'Standard delivery',
            'express'   =>  'Express delivery',
"CanadaPostExpress9AM"=>"Express 9AM",
"CanadaPostExpress10:30AM"=>"Express 10:30AM",
"CanadaPostExpress"=>"Express",
"CanadaPostExpressPackU.S."=>"Express U.S. Pack ",
"CanadaPostExpressU.S.Pack9AM"=>"Express U.S. Pack 9AM",
"CanadaPostExpressU.S.Pack10:30AM"=>"Express U.S. Pack 10:30AM",
"CanadaPostExpressEvening"=>"Express Evening",
"CanadaPostExpressEnvelope9AM"=>"Express Envelope 9AM",
"CanadaPostExpressEnvelope10:30AM"=>"Express Envelope 10:30AM",
"CanadaPostExpressEnvelope"=>"Express Envelope",
"CanadaPostExpressEnvelopeEvening"=>"Express Envelope Evening",
"CanadaPostExpressPack9AM"=>"Express Pack 9AM",
"CanadaPostExpressPack10:30AM"=>"Express Pack 10:30AM",
"CanadaPostExpressPack"=>"Express Pack",
"CanadaPostExpressPackEvening"=>"Express Pack Evening",
"CanadaPostExpressBox9AM"=>"Express Box 9AM",
"CanadaPostExpressBox10:30AM"=>"Express Box 10:30AM",
"CanadaPostExpressBox"=>"Express Box",
"CanadaPostExpressBoxEvening"=>"Express Box Evening",
"CanadaPostGround"=>"Ground",
"CanadaPostGround9AM"=>"Ground 9AM",
"CanadaPostGround10:30AM"=>"Ground 10:30AM",
"CanadaPostGroundEvening"=>"Ground Evening",
"CanadaPostExpressU.S."=>"Express U.S.",
"CanadaPostExpressU.S.9AM"=>"Express U.S. 9AM",
"CanadaPostExpressU.S.10:30AM"=>"Express U.S. 10:30AM",
"CanadaPostExpressU.S.12:00"=>"Express U.S. 12:00",
"CanadaPostExpressEnvelopeU.S."=>"Express Envelope U.S.",
"CanadaPostExpressU.S.Envelope9AM"=>"Express U.S. Envelope 9AM",
"CanadaPostExpressU.S.Envelope10:30AM"=>"Express U.S. Envelope 10:30AM",
"CanadaPostExpressU.S.Envelope12:00"=>"Express U.S. Envelope 12:00",
"CanadaPostExpressU.S.Pack12:00"=>"Express U.S. Pack 12:00",
"CanadaPostExpressBoxU.S."=>"Express U.S. Box",
"CanadaPostExpressU.S.Box9AM"=>"Express U.S. Box 9AM",
"CanadaPostExpressU.S.Box10:30AM"=>"Express U.S. Box 10:30AM",
"CanadaPostExpressU.S.Box12:00"=>"Express U.S. Box 12:00",
"CanadaPostGroundU.S."=>"Ground U.S.",
"CanadaPostExpressInternational"=>"Express International",
"CanadaPostExpressInternational9AM"=>"Express International 9AM",
"CanadaPostExpressInternational10:30AM"=>"Express International 10:30AM",
"CanadaPostExpressInternational12:00"=>"Express International 12:00",
"CanadaPostExpressEnvelopeInternational"=>"Express Envelope International",
"CanadaPostExpressInternationalEnvelope9AM"=>"Express International Envelope 9AM",
"CanadaPostExpressInternationalEnvelope10:30AM"=>"Express International Envelope 10:30AM",
"CanadaPostExpressInternationalEnvelope12:00"=>"Express International Envelope 12:00",
"CanadaPostExpressPackInternational"=>"Express International Pack",
"CanadaPostExpressInternationalPack9AM"=>"Express International Pack 9AM",
"CanadaPostExpressInternationalPack10:30AM"=>"Express International Pack 10:30AM",
"CanadaPostExpressInternationalPack12:00"=>"Express International Pack 12:00",
"CanadaPostExpressBoxInternational"=>"Express International Box",
"CanadaPostExpressInternationalBox9AM"=>"Express International Box 9AM",
"CanadaPostExpressInternationalBox10:30AM"=>"Express International Box 10:30AM",
"CanadaPostExpressInternationalBox12:00"=>"Express International Box 12:00",
"CanadaPostGroundDistribution"=>"Ground Distribution",


        );
    }
    
    function preptranslate(&$config) {
      $config->translation=
      array(
    "Item too heavy to ship using this service"=>array("fr"=>"Trop lourd pour expédier en utilisant ce service"),
        "days"=>array("fr"=>"jours"),
        "Business Days"=>array("fr"=>"jours ouvrables"),
        "Business Day"=>array("fr"=>"jour ouvrable"),
        "FREE"=>array("fr"=>"GRATUIT"),
        
    "CanadaPostExpress9AM"=>array("en"=>"Express 9AM","fr"=>"Express 9 h"),
    "CanadaPostExpress10:30AM"=>array("en"=>"Express 10:30AM","fr"=>"Express 10 h 30"),
    "CanadaPostExpress"=>array("en"=>"Express","fr"=>"Express"),
    "CanadaPostExpressPackU.S."=>array("en"=>"Express U.S. Pack ","fr"=>"Express Soirée"),
    "CanadaPostExpressU.S.Pack9AM"=>array("en"=>"Express U.S. Pack 9AM","fr"=>"Express Pack 9 h vers les É.-U."),
    "CanadaPostExpressU.S.Pack10:30AM"=>array("en"=>"Express U.S. Pack 10:30AM","fr"=>"Express Pack 10 h 30 vers les É.-U."),
    "CanadaPostExpressEvening"=>array("en"=>"Express Evening","fr"=>"Express Soirée"),
    "CanadaPostExpressEnvelope9AM"=>array("en"=>"Express Envelope 9AM","fr"=>"Express Enveloppe 9 h"),
    "CanadaPostExpressEnvelope10:30AM"=>array("en"=>"Express Envelope 10:30AM","fr"=>"Express Enveloppe 10 h 30"),
    "CanadaPostExpressEnvelope"=>array("en"=>"Express Envelope","fr"=>"Express Enveloppe"),
    "CanadaPostExpressEnvelopeEvening"=>array("en"=>"Express Envelope Evening","fr"=>"Express Enveloppe Soirée"),
    "CanadaPostExpressPack9AM"=>array("en"=>"Express Pack 9AM","fr"=>"Express Pack 9 h"),
    "CanadaPostExpressPack10:30AM"=>array("en"=>"Express Pack 10:30AM","fr"=>"Express Pack 10 h 30"),
    "CanadaPostExpressPack"=>array("en"=>"Express Pack","fr"=>"Express Pack"),
    "CanadaPostExpressPackEvening"=>array("en"=>"Express Pack Evening","fr"=>"Express Pack Soirée"),
    "CanadaPostExpressBox9AM"=>array("en"=>"Express Box 9AM","fr"=>"Express Boîte 9 h"),
    "CanadaPostExpressBox10:30AM"=>array("en"=>"Express Box 10:30AM","fr"=>"Express Boîte 10 h 30"),
    "CanadaPostExpressBox"=>array("en"=>"Express Box","fr"=>"Express Boîte"),
    "CanadaPostExpressBoxEvening"=>array("en"=>"Express Box Evening","fr"=>"Express Boîte Soirée"),
    "CanadaPostGround"=>array("en"=>"Ground","fr"=>"Routier"),
    "CanadaPostGround9AM"=>array("en"=>"Ground 9AM","fr"=>"Routier 9 h"),
    "CanadaPostGround10:30AM"=>array("en"=>"Ground 10:30AM","fr"=>"Routier 10 h 30"),
    "CanadaPostGroundEvening"=>array("en"=>"Ground Evening","fr"=>"Routier"),
    "CanadaPostExpressU.S."=>array("en"=>"Express U.S.","fr"=>"Routier Soirée"),
    "CanadaPostExpressU.S.9AM"=>array("en"=>"Express U.S. 9AM","fr"=>"Express 9 h vers les É.-U."),
    "CanadaPostExpressU.S.10:30AM"=>array("en"=>"Express U.S. 10:30AM","fr"=>"Express 10 h 30 vers les É.-U."),
    "CanadaPostExpressU.S.12:00"=>array("en"=>"Express U.S. 12:00","fr"=>"Express Midi vers les É.-U."),
    "CanadaPostExpressEnvelopeU.S."=>array("en"=>"Express Envelope U.S.","fr"=>"Express Enveloppe vers les É.-U."),
    "CanadaPostExpressU.S.Envelope9AM"=>array("en"=>"Express U.S. Envelope 9AM","fr"=>"Express Enveloppe 9 h vers les É.-U."),
    "CanadaPostExpressU.S.Envelope10:30AM"=>array("en"=>"Express U.S. Envelope 10:30AM","fr"=>"Express Enveloppe 10 h 30 vers les É.-U."),
    "CanadaPostExpressU.S.Envelope12:00"=>array("en"=>"Express U.S. Envelope 12:00","fr"=>"Express Enveloppe Midi vers les É.-U."),
    "CanadaPostExpressU.S.Pack12:00"=>array("en"=>"Express U.S. Pack 12:00","fr"=>"Express Pack Midi vers les É.-U."),
    "CanadaPostExpressBoxU.S."=>array("en"=>"Express U.S. Box","fr"=>"Express Boîte vers les É.-U."),
    "CanadaPostExpressU.S.Box9AM"=>array("en"=>"Express U.S. Box 9AM","fr"=>"Express Boîte 9 h vers les É.-U."),
    "CanadaPostExpressU.S.Box10:30AM"=>array("en"=>"Express U.S. Box 10:30AM","fr"=>"Express Boîte 10 h 30 vers les É.-U."),
    "CanadaPostExpressU.S.Box12:00"=>array("en"=>"Express U.S. Box 12:00","fr"=>"Express Boîte Midi vers les É.-U."),
    "CanadaPostGroundU.S."=>array("en"=>"Ground U.S.","fr"=>"Routier vers les É.-U."),
    "CanadaPostExpressInternational"=>array("en"=>"Express International","fr"=>"Express International"),
    "CanadaPostExpressInternational9AM"=>array("en"=>"Express International 9AM","fr"=>"Express 9 h International"),
    "CanadaPostExpressInternational10:30AM"=>array("en"=>"Express International 10:30AM","fr"=>"Express 10 h 30 International"),
    "CanadaPostExpressInternational12:00"=>array("en"=>"Express International 12:00","fr"=>"Express Midi International"),
    "CanadaPostExpressEnvelopeInternational"=>array("en"=>"Express Envelope International","fr"=>"Express Enveloppe International"),
    "CanadaPostExpressInternationalEnvelope9AM"=>array("en"=>"Express International Envelope 9AM","fr"=>"Express Enveloppe 9 h International"),
    "CanadaPostExpressInternationalEnvelope10:30AM"=>array("en"=>"Express International Envelope 10:30AM","fr"=>"Express Enveloppe 10 h 30 International"),
    "CanadaPostExpressInternationalEnvelope12:00"=>array("en"=>"Express International Envelope 12:00","fr"=>"Express Enveloppe Midi International"),
    "CanadaPostExpressPackInternational"=>array("en"=>"Express International Pack","fr"=>"Express Pack International"),
    "CanadaPostExpressInternationalPack9AM"=>array("en"=>"Express International Pack 9AM","fr"=>"Express Pack 9 h International"),
    "CanadaPostExpressInternationalPack10:30AM"=>array("en"=>"Express International Pack 10:30AM","fr"=>"Express Pack 10 h 30 International"),
    "CanadaPostExpressInternationalPack12:00"=>array("en"=>"Express International Pack 12:00","fr"=>"Express Pack Midi International"),
    "CanadaPostExpressBoxInternational"=>array("en"=>"Express International Box","fr"=>"Express Boîte International"),
    "CanadaPostExpressInternationalBox9AM"=>array("en"=>"Express International Box 9AM","fr"=>"Express Boîte International 9 h"),
    "CanadaPostExpressInternationalBox10:30AM"=>array("en"=>"Express International Box 10:30AM","fr"=>"Express Boîte International 10 h 30"),
    "CanadaPostExpressInternationalBox12:00"=>array("en"=>"Express International Box 12:00","fr"=>"Express Boîte International Midi"),
    "CanadaPostGroundDistribution"=>array("en"=>"Ground Distribution","fr"=>"Routier - Distribution"),

    "LowestPriceFreeShipping"=>array("en"=>"Free Shipping","fr"=>"Livraison Gratuite"),
        
          );
    }
    
    function translate(&$config,$message) {
      $message=trim($message);
      if (isset($config->translation[$message]) && isset($config->translation[$message][$config->language])) {
        return $config->translation[$message][$config->language];
      }
      
      if (isset($config->translation[$message]) && isset($config->translation[$message]["en"])) {
        return $config->translation[$message]["en"];
      }
      
      return $message;
    }
    
    private function datediff($d1,$d2) {
      return ($d1-$d2)/(60*60*24);
    }
    
     private function foreachArray(&$arrayOrItem) {
       return (is_array($arrayOrItem) ? $arrayOrItem: array($arrayOrItem));
     }
    function arrayToObject($array) {
        if(!is_array($array)) {
            return $array;
        }
        
        $object = new \stdClass();
        if (is_array($array) && count($array) > 0) {
          foreach ($array as $name=>$value) {
            $name = trim($name);
            if (!empty($name)) {
                $object->$name = $this->arrayToObject($value);
            }
          }
          return $object;
        }
        else {
          return FALSE;
        }
    }    
    function carrierdebug(...$value) {
      $this->_debug($value);
    }
    function carriererror(...$value) {
      $this->_logger->error($value);
    }

}


/**
 *
 */
class Deviant_Packer {

    public $outer_boxes;
    private $usable_boxes;
    public $key_inner_boxes;
    private $inner_boxes;
    
    

    public function Deviant_Packer() {

        $this->reset();
        
        return;

    }
    public function reset() {
        $this -> outer_boxes = array();
        $this -> inner_boxes = array();
        $this -> usable_boxes = array();
        $this->key_inner_boxes=array();
    }
    public function removeBoxes() {
        $this -> usable_boxes = array();
        $this -> outer_boxes = array();
    }
    private function resetBoxes() {
      $this -> usable_boxes = array();
      $box = &$this->outer_boxes[0];
      // basically create the usable box
      $this->add_outer_box($box->dimensions[0],$box->dimensions[1],$box->dimensions[2], $box);
    }
    
    public function fitItems($debug=false) {
      $this->resetBoxes();
      $fit = true;

      for($x=0;$x<count($this->inner_boxes);$x++) {
        $newbox = &$this->inner_boxes[$x];
        if (!$this->fits($newbox, $newbox->method)) {
          $fit=false;
          break;
        }
      }
      
      
      
      return $fit;
    }
    public function add_box($box) {


        $this -> outer_boxes[] = $obox = (object) array(

            "dimensions" => $box->dimensions,
            "packed" => false,
            "maxWeight"=>$box->maxWeight,
            "boxWeight"=>$box->boxWeight,
            "currentWeight"=>0,
            "totalVolume"=>($box->dimensions[0]*$box->dimensions[1]*$box->dimensions[2]),
            "currentVolume"=>0,
            "boxPrice"=>$box->boxPrice,
            "items"=>array()

        );
        $this->add_outer_box($box->dimensions[0],$box->dimensions[1],$box->dimensions[2], $obox);

        return true;

    }
    public function add_item(&$item) {
    

            $newBox = (object)array(

                "dimensions" => array($item->dimensions[0],$item->dimensions[1],$item->dimensions[2]),
                "itemid" => $item->itemid,
                "itemPrice"=>$item->itemPrice,
                "itemname"=>$item->itemname,
                "weight"=>$item->weight,
                "totalVolume"=>($item->dimensions[0]*$item->dimensions[1]*$item->dimensions[2]),
                "qty"=>1,
                "method"=>"a",
                

            );
            $method="a";
            
              while(true) {
              if ($this->fits($newBox, $method)) {
                $this->inner_boxes[]=$newBox;
                if (!isset($this->key_inner_boxes[$item->itemid])) {
                  $this->key_inner_boxes[$item->itemid]=$newBox;
                } else {
                  $this->key_inner_boxes[$item->itemid]->qty++;
                }
                return true;
              }
              
              // Reset the boxes and try a different layout to fit new item
              $this->resetBoxes();
              $fit = true;
              if ($method=="b") {
                return false;
              }
              $method = "b";
              for($x=0;$x<count($this->inner_boxes);$x++) {
                $inewbox = &$this->inner_boxes[$x];
                $inewbox->method=$method;
                if (!$this->fits($inewbox, $inewbox->method)) {
                  return false;;
                }        
              }
              
            }


        return false;

    }

    public function add_outer_box($l,$w,$h, &$referenceBox) {

        if ($l > 0 && $w > 0 && $h > 0) {
          $dimensions=array($l,$w,$h);
          rsort($dimensions);

            $this -> usable_boxes[] = (object) array(

                "dimensions" => $dimensions,
                "totalVolume"=>($l*$w*$h),
                "referenceBox"=>$referenceBox
            );

        }

        return true;

    }
    

    public function fits($newBox, $method, $debug=false) {

        /* first we do a simple volume check, this can save a lot of calculations */

        if (!$this -> fits_volume($newBox)) {

            return false;

        }

        $this -> sort_outer_boxes(); // smallest first

        $found_fitting_box = false;

        foreach ($this->usable_boxes as $outer_box_id => $outer_box) {
          $sourceBox=$outer_box->referenceBox;

            if ($this->fits_inside($newBox, $outer_box)) {
                /* matches! */
                $sourceBox->items[]=$newBox;
                $sourceBox->currentWeight+=$newBox->weight;
                $sourceBox->currentVolume+=$newBox->totalVolume;
                unset($this->usable_boxes[$outer_box_id]);
                $this -> find_subboxes($newBox, $outer_box, $sourceBox,$method);
                $found_fitting_box = true;

                break;

            
            }

        }

        if (!$found_fitting_box) {

            return false;

        }

        return true;

    }

    public function fits_volume(&$newBox) {

        foreach ($this -> usable_boxes as $outer) {
            if ($outer->totalVolume>$newBox->totalVolume &&
               $outer->referenceBox->maxWeight-$outer->referenceBox->currentWeight>$newBox->weight) {
              return true;
            }
        }

        return false;
    }

    private function find_subboxes(&$inner_box, &$outer_box, &$referenceBox, $method) {

        $inner_dimensions = $inner_box->dimensions;
        $outer_dimensions = $outer_box->dimensions;
        
        switch($method) {
          case "a":
              // New code
              $this -> add_outer_box(
                $outer_dimensions[0]-$inner_dimensions[0], 
                $outer_dimensions[1],
                $outer_dimensions[2], $referenceBox);          
                
              $this -> add_outer_box(
                $outer_dimensions[1]-$inner_dimensions[1], 
                $inner_dimensions[0],
                $outer_dimensions[2], $referenceBox);
                
              $this -> add_outer_box(
                $outer_dimensions[2]-$inner_dimensions[2], 
                $inner_dimensions[1],
                $inner_dimensions[0], $referenceBox);
                break;
          case "b":
              // New code
              $this -> add_outer_box(
                $outer_dimensions[0], 
                $outer_dimensions[1]-$inner_dimensions[1],
                $outer_dimensions[2], $referenceBox);          
                
              $this -> add_outer_box(
                $outer_dimensions[0]-$inner_dimensions[0], 
                $inner_dimensions[1],
                $outer_dimensions[2], $referenceBox);
                
              $this -> add_outer_box(
                $outer_dimensions[2]-$inner_dimensions[2], 
                $inner_dimensions[1],
                $outer_dimensions[0] - $inner_dimensions[0], $referenceBox);
                break;
                
        }
          
          
/*
        sort($outer_dimensions);

        $pairs = array();

        foreach ($inner_dimensions as $inner_id => $inner_value) {

            foreach ($outer_dimensions as $outer_id => $outer_value) {

                if ($inner_value <= $outer_value) {

                    $unset = $outer_id;

                    $pairs[] = array(

                        "inner" => $inner_value,
                        "outer" => $outer_value,
                        "diff" => $outer_value-$inner_value

                    );

                    break;

                }

            }

            unset($outer_dimensions[$unset]);

        }
        

        do {

            $pairs = $this-> _diffsort($pairs);
print_r($pairs);die();
            $this -> add_outer_box($pairs[0]["diff"], $pairs[1]["outer"], $pairs[2]["outer"],$referenceBox);

            $pairs[0]["diff"] = 0;
            $pairs[0]["outer"] = $pairs[0]["inner"];


        } while($pairs[0]["diff"] > 0 || $pairs[1]["diff"] > 0 || $pairs[2]["diff"] > 0);

        return true;
*/
    }

    private function fits_inside($inner_box, $outer_box) {

        if ($inner_box->dimensions[0] <= $outer_box->dimensions[0] &&
            $inner_box->dimensions[1] <= $outer_box->dimensions[1] &&
            $inner_box->dimensions[2] <= $outer_box->dimensions[2] &&
            $inner_box->weight+$outer_box->referenceBox->currentWeight <= $outer_box->referenceBox->maxWeight            
            
        ) {
            /* fits */
            return true;

        } else {
            /* fits not */
            return false;

        }
    }

    public function checkBoxSize($boxa, $boxb) {
      $result = $boxa->dimensions[0]-$boxb->dimensions[0];
      if ($result==0) {
        $result = $boxa->dimensions[1]-$boxb->dimensions[1];
      }
      if ($result==0) {
        $result = $boxa->dimensions[2]-$boxb->dimensions[2];
      }
      if ($result==0) {
        $result = $boxa->referenceBox->maxWeight-$boxa->referenceBox->currentWeight-
                  ($boxb->referenceBox->maxWeight-$boxb->referenceBox->currentWeight);
      }
      return $result;
    }
    private function sort_outer_boxes() {
      usort($this->outer_boxes,array($this, "checkBoxSize"));
      return true;
    }
    function _diffsort($array) {

        /* quick and dirty hack since _sksort() does strange things */

        $tmp_array = array();

        foreach ($array as $item) {

            $tmp_array[(string)$item["diff"]][] = $item;

        }

        krsort($tmp_array, SORT_NUMERIC);

        $array = array();

        foreach ($tmp_array as $a) {

            foreach ($a as $item) {

                $array[] = $item;

            }

        }

        return $array;

    }

}

class F {
        static $_output;
        static $_objects;
        static $_depth;
    public static function dump($var,$depth=5,$highlight=false)
    {
        self::$_output='';
        self::$_objects=array();
        self::$_depth=$depth;
        self::dumpInternal($var,0);
        if($highlight)
        {
            $result=highlight_string("<?php\n".self::$_output,true);
            return preg_replace('/&lt;\\?php<br \\/>/','',$result,1);
        }
        else
            return self::$_output;
    }
    private static function dumpInternal($var,$level)
    {
        switch(gettype($var))
        {
            case 'boolean':
                self::$_output.=$var?'true':'false';
                break;
            case 'integer':
                self::$_output.="$var";
                break;
            case 'double':
                self::$_output.="$var";
                break;
            case 'string':
                self::$_output.="'$var'";
                break;
            case 'resource':
                self::$_output.='{resource}';
                break;
            case 'NULL':
                self::$_output.="null";
                break;
            case 'unknown type':
                self::$_output.='{unknown}';
                break;
            case 'array':
                if(self::$_depth<=$level)
                    self::$_output.='array(...)';
                else if(empty($var))
                    self::$_output.='array()';
                else
                {
                    $keys=array_keys($var);
                    $spaces=str_repeat(' ',$level*4);
                    self::$_output.="array\n".$spaces.'(';
                    foreach($keys as $key)
                    {
                        self::$_output.="\n".$spaces."    [$key] => ";
                        self::$_output.=self::dumpInternal($var[$key],$level+1);
                    }
                    self::$_output.="\n".$spaces.')';
                }
                break;
            case 'object':
                if(($id=array_search($var,self::$_objects,true))!==false)
                    self::$_output.=get_class($var).'#'.($id+1).'(...)';
                else if(self::$_depth<=$level)
                    self::$_output.=get_class($var).'(...)';
                else
                {
                    $id=array_push(self::$_objects,$var);
                    $className=get_class($var);
                    $members=(array)$var;
                    $keys=array_keys($members);
                    $spaces=str_repeat(' ',$level*4);
                    self::$_output.="$className#$id\n".$spaces.'(';
                    foreach($keys as $key)
                    {
                        $keyDisplay=strtr(trim($key),array("\0"=>':'));
                        self::$_output.="\n".$spaces."    [$keyDisplay] => ";
                        self::$_output.=self::dumpInternal($members[$key],$level+1);
                    }
                    self::$_output.="\n".$spaces.')';
                }
                break;
        }
    }
  
}


class CartWrapper {
  protected $_mode='';
  protected $_request='';
     public function __construct($mode,$request, $carrier) {
        $this->_mode = $mode;
        $this->_request = $request;
        $this->carrier = $carrier;
        if ($mode=="2") {
          $this->productQuery = function($invoiceItem,$config) {
            $productID = $invoiceItem->getProductId();
            $productList = $this->carrier->_productLoader->create()->getCollection()
              ->addAttributeToSelect(['name','sku','weight',
                $config->properties->widthattribute,
                $config->properties->lengthattribute,
                $config->properties->heightattribute]);
              if ($config->dimensionalKeys) {
                  foreach($config->dimensionalKeys as $dimensionalKey) {
                      $productList = $productList->addAttributeToSelect($dimensionalKey);
                  }
              }
              $productList = $productList->addIdFilter($productID);
            $product=false;
           foreach($productList as $product) {
             //echo $pl->getData('package_depth');
             break;
           } // end for
           return $product;
          };
          
        }
      }
      public function iterate($source, $callback,$config) {
      if ($this->_mode=="2") {
        $items = $this->getAllItems($this->_request);
        foreach($items as $item) {
          $product = $this->WrapItem($item,$config);
          if ($product!==false) {
            call_user_func_array(array($source, $callback),array($product,$config));
          }
        }
      }
      }
      public function WrapItem($invoiceItem, $config) {
         $productId = $invoiceItem->getProductId();
         $productQuery = $this->productQuery;
         $product = $productQuery($invoiceItem,$config);
         $weight = ($invoiceItem->getWeight()*$config->properties->convert_weight);
         $productData=(object)array("Product"=>$productId,
          "Weight"=>$weight,"weight"=>$invoiceItem->getWeight()*$config->properties->convert_weight,
          "itemid"=>$productId,"itemname"=>$product->getSku(). ' ' .$invoiceItem->getName(),
          "itemPrice"=>($invoiceItem->getParentItem() ? $invoiceItem->getParentItem()->getPrice() : $invoiceItem->getPrice()),
          "qty" => ($invoiceItem->getParentItem() ? $invoiceItem->getParentItem()->getQty() : $invoiceItem->getQty()));

        if ($product) {
          $mappingFound = false;
          if ($config->dimensionalKeys) {
              foreach($config->dimensionalKeys as $dimensionalKey) {
                  if ($product->getData($dimensionalKey) && $product->getData($dimensionalKey)!="") {
                      $dataValue = $product->getData($dimensionalKey);
                      if (isset($config->dimensionalData[$dimensionalKey][$dataValue])) {
                          // We found the mapping
                          $productData->dimensions = explode("x", $config->dimensionalData[$dimensionalKey][$dataValue]);
                          $mappingFound = true;

                      }
                  }
              }
          }
          if (!$mappingFound && $config->calculate_dimension_weight==1) {

            $productData->length = $product->getData($config->properties->lengthattribute)*$config->properties->convert_size;
            if ($productData->length=="" && $config->properties->defaultlength>0) {
              $productData->length = $config->defaultlength*$config->properties->convert_size;
            }
            
            $productData->width = $product->getData($config->properties->widthattribute)*$config->properties->convert_size;
            if ($productData->width=="" && $config->properties->defaultwidth>0) {
              $productData->width = $config->properties->defaultwidth*$config->properties->convert_size;
            }
            
            $productData->height = $product->getData($config->properties->heightattribute)*$config->properties->convert_size;
            if ($productData->height=="" && $config->properties->defaultheight>0) {
              $productData->height = $config->properties->defaultheight*$config->properties->convert_size;
            }
            $productData->dimensions = array($productData->length,$productData->height,$productData->width);
          } else {
            $productData->length=0;
            $productData->width=0;
            $productData->height=0;
            $productData->dimensions = array($productData->length,$productData->height,$productData->width);
          }
          
          
          $productData->qty =  $invoiceItem->getQty();
          $productData->price = $invoiceItem->getPrice();
          $productData->totalprice = $invoiceItem->getPrice()*$productData->qty;
          
          $config->runningPrice+= $invoiceItem->getPrice()*$productData->qty;
          
          $this->carrier->debug(array("Product data"=>$productData));
          // Append product data to estimate call
        }
        else {
          // Product not found
          $this->carrier->carriererror(array("Product not found :"=> $product));
          return false;
        }
        return $productData;
      }
    /**
     * Return items for further shipment rate evaluation. We need to pass children of a bundle instead passing the
     * bundle itself, otherwise we may not get a rate at all (e.g. when total weight of a bundle exceeds max weight
     * despite each item by itself is not)
     *
     * @param RateRequest $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @api
     */
    public function getAllItems(RateRequest $request)
    {
        $items = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                /* @var $item \Magento\Quote\Model\Quote\Item */
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    // Don't process children here - we will process (or already have processed) them below
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $items[] = $child;
                        }
                    }
                } else {
                    // Ship together - count compound item as one solid
                    $items[] = $item;
                }
            }
        }

        return $items;
    }
}
