<?php

/**
 * 1.0.0 2017-03-27 Some patches, updated version number
 * 0.1.0 2017-03-13 Initial release
 */
namespace RebelShipper\Purolator\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
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

/**
 * @category   RebelShipper
 * @package    RebelShipper_Purolator
 * @author     iam@rebelshipper.com
 * @website    http://www.rebelshipper.com
 */
class Purolator extends AbstractCarrier implements CarrierInterface
{
    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'rspurolator';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;
    
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
    protected $_devUrl = 'http://devwebservices.purolator.com';
    protected $_liveUrl = 'http://webservices.purolator.com';

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        Reader $reader,
        Information $storeInfo,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_storeInfo = $storeInfo;
        $this->_storeManagerInterface = $storeManagerInterface;
        $this->_regionFactory = $regionFactory;
        $this->_countryFactory = $countryFactory;
        $this->_productLoader = $_productloader;
        $this->productRepository = $productRepository;
        $logger->pushHandler( new \Monolog\Handler\StreamHandler( BP . '/var/log/RebelShipper_Purolator.log'));
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        //$om = \Magento\Framework\App\ObjectManager::getInstance();
        //$reader = $om->get('Magento\Framework\Module\Dir\Reader');
        $this->_basepath=$reader->getModuleDir("etc","RebelShipper_Purolator");
        
    }

    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */

    public function collectRates(RateRequest $request) {
      if (!$this->isActive()) {
          return false;
      }

      $this->_debug("Fetching config");
      $config =$this->_getConfig($request);
      
      $this->_debug("Checking config");
      if (!$this->testConfig($config)) {
        $this->_debug("Config failed test");
        // Return an error response
        return $config->result;
      }
      
      // Check and attempt to correct the address
      $this->_debug("Checking address");
      if (!$this->testAddress($config)) {
        $this->_debug("Address failed test");
        // Return an error response
        return $config->result;
      }
      
      $this->_debug("Preparing soap estimate calls");
      $this->_prepareSoapEstimate($config);
      
      try {

      $this->_debug("Checking cart contents");
      $cartWrapper = new CartWrapper("2",$request,$this);
      $cartWrapper->iterate($this,"itemCallback",$config);
      
      $this->_debug("Making request");
      $valid = $this->_makeRequest($config);
      if ($valid) {
        $this->_debug("Request valid, found shipping, adding free shipping method if configured");
        $this->_buildShippingResult($config);
      } 
         else {
             $error = $this->_rateErrorFactory->create();
             $error->setCarrier($this->_code);
             $error->setCarrierTitle($this->getConfigData('title'));
             $error->setErrorMessage($this->getConfigData('specificerrmsg') . " - ".$config->responseError);
             $config->result->append($error);

         }
      

      } catch(Exception $exception) {
        // Log the error
        $config->responseError="Purolator server reported an error on this quote, this information has been logged. ";
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($this->getConfigData('specificerrmsg') . " - ".$config->responseError);
        $config->result->append($error);
        $this->carriererror("Error reported by purolator " .$exception->getMessage());
      }

      return $config->result;
    }
    
    private function testConfig($config) {
      if (!$config->dest->postalCode || !$config->origin->postalCode) {
        // Return an error response
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($this->getConfigData('specificerrmsg') . " - Missing postal codes for quote");
        $config->result->append($error);
        return false;
        
      }
      return true;
    }
    private function testAddress($config) {
    
        $config->validateclient = new \SoapClient( $config->wsdlpath."/ServiceAvailabilityService.wsdl", 
                                        $config->headinfo=array   (
                                                'trace'         =>  true,
                                                'location'  =>  $config->url."/PWS/V1/ServiceAvailability/ServiceAvailabilityService.asmx",
                                                'uri'               =>  "http://purolator.com/pws/datatypes/v1",
                                              'login'         => $config->api_access_key,
                                              'password'  =>  $config->api_password,
                                              )
                                      );
        $validateheaders[] = new \SoapHeader ( 'http://purolator.com/pws/datatypes/v1', 
                                            'RequestContext', 
                                            array (
                                                    'Version'           =>  '1.3',
                                                    'Language'          =>  'en',
                                                    'GroupID'           =>  'xxx',
                                                    'RequestReference'  =>  'Magento Estimate',
													'UserToken'=>"e9bb452f-41a4-4877-949d-78081ae1a300",
                                                  )
                                          ); 
              //Apply the SOAP Header to your client                  
        $config->validateclient->__setSoapHeaders($validateheaders);
        $config->freightclient = new \SoapClient( $config->wsdlpath."/ServiceAvailabilityService.wsdl", 
                                        $config->freightheadinfo=array   (
                                                'trace'         =>  true,
                                                'location'  =>  $config->url."/PWS/V1/ServiceAvailability/ServiceAvailabilityService.asmx",
                                                'uri'               =>  "http://purolator.com/pws/datatypes/v1",
                                              'login'         => $config->api_access_key,
                                              'password'  =>  $config->api_password,
                                              )
                                      );
                                      
        $freightValidateheaders = new \SoapHeader ( 'http://purolator.com/pws/datatypes/v1', 
                                            'RequestContext', 
                                            array (
                                                    'Version'           =>  '1.3',
                                                    'Language'          =>  'en',
                                                    'GroupID'           =>  'xxx',
                                                    'RequestReference'  =>  'Magento Estimate',
                                                    'UserToken'=>"e9bb452f-41a4-4877-949d-78081ae1a300",
                                                  ));
        $config->freightclient->__setSoapHeaders($freightValidateheaders);
              
        $config->validateclientrequest=$this->arrayToObject(
          array(
            "Addresses"=>array("ShortAddress"=>array("PostalCode"=>$config->dest->postalCode,
                                                      "Country"=>$config->dest->countryCode,
                                                      ))
                                                      
            )
          );
             
        // If destination city is not populated this is a quick quote
        // but we need to manually populate it regardless
        // Also perform this function if address validation is off
        try {
        if ($config->dest->city=="" || $config->address_validation==0) {

        $response = $config->validateclient->ValidateCityPostalCodeZip($config->validateclientrequest);

        if (!$response) {
          $config->responseError="Purolator server did not respond. ";
          $error = $this->_rateErrorFactory->create();
          $error->setCarrier($this->_code);
          $error->setCarrierTitle($this->getConfigData('title'));
          $error->setErrorMessage($this->getConfigData('specificerrmsg') . " - ".$config->responseError);
          $config->result->append($error);
          $this->shipLogError($config,"Purolator server did not respond  validate city postal code");
          return false;
        }
        if ($response->SuggestedAddresses->SuggestedAddress) {
            $config->dest->city = $response->SuggestedAddresses->SuggestedAddress->Address->City;
            
            // Check the province JIC
            if ($response->SuggestedAddresses->SuggestedAddress->Address->Province!=$config->request->getDestRegionCode()) {
              if ($config->address_validation_warning==1) {
                  $error = $this->_rateErrorFactory->create();
                  $error->setCarrier($this->_code);
                  $error->setCarrierTitle($this->getConfigData('title'));
                  $error->setErrorMessage("Warning: Selected State / Province code does not match postal code ".
                      $config->destRegion."," . " auto changed to " . 
                      $response->SuggestedAddresses->SuggestedAddress->Address->Province );
                  $config->result->append($error);
              }    
              $config->dest->region=$response->SuggestedAddresses->SuggestedAddress->Address->Province;
              $config->request->setDestRegionCode($config->dest->region);
              $config->request->setDestCity($config->dest->city);
            }
          }
        }
      } catch(\Exception $exception) {
        
        // Log the error
        $config->responseError="Purolator server reported an error on this quote, this information has been logged. ";
        $error = $this->_rateErrorFactory->create();
        $error->setCarrier($this->_code);
        $error->setCarrierTitle($this->getConfigData('title'));
        $error->setErrorMessage($this->getConfigData('specificerrmsg') . " - ".$config->responseError);
        $config->result->append($error);
        $this->_logger->critical($exception);

        $this->_logger->addError("Error reported by purolator " .$exception->getMessage());
        $this->_logger->addError("Request " .$config->validateclient->__getLastRequest());
        $this->_debug("Headinfo",$config->headinfo);
        
        print_r(array("URL"=>$config->url."/PWS/V1/ServiceAvailability/ServiceAvailabilityService.asmx",
                                        'login'         => $config->api_access_key,
                                        'password'  =>  $config->api_password));
        
        print_r($exception->getMessage());
        return false;
      }    
    
      return true;
    }
    protected function _prepareSoapEstimate($config) {


             /** Purpose : Creates a SOAP Client in Non-WSDL mode with the appropriate authentication and
               *           header information
             **/
             //Set the parameters for the Non-WSDL mode SOAP communication with your Development/Production credentials
             $config->estimateclient = new \SoapClient( $config->wsdlpath."/EstimatingService.wsdl",
                                       $config->estimateclientHead=array   (
                                               'trace'         =>  true,
                                               // Development (dev)
                                               'location'  =>  $config->url."/PWS/V1/Estimating/EstimatingService.asmx",
                                               // Production
                                               // 'location'   =>  "https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx",
                                               'uri'               =>  "http://purolator.com/pws/datatypes/v1",
                                               'login'         => $config->api_access_key,
                                               'password'  =>  $config->api_password,
                                             )
                                     );
             //Define the SOAP Envelope Headers
             $headers[] = new \SoapHeader ( 'http://purolator.com/pws/datatypes/v1',
                                           'RequestContext',
                                           array (
                                                   'Version'           =>  '1.4',
                                                   'Language'          =>  $config->language,
                                                   'GroupID'           =>  'xxx',
                                                   'RequestReference'  =>  'Rating Example',
   												  'UserToken'=>"e9bb452f-41a4-4877-949d-78081ae1a300",
                                                 )
                                         );

             $this->_debug(array("Estimate Request head "=> $config->estimateclientHead));

             //Apply the SOAP Header to your client
             $config->estimateclient->__setSoapHeaders($headers);
             $config->fullestimateclientrequest=$this->arrayToObject(
               array(
                 "Shipment"=>array(// "M9W 7J2" ETOBICOKE
                   "SenderInformation"=>array("Address"=>array("PostalCode"=>$config->origin->postalCode,
                                                               // Unneeded
                                                               //"Name"=>,
                                                               //"StreetNumber"=>,
                                                               //"StreetName"=>,
                                                               "City"=>$config->origin->city,
                                                               "Province"=>$config->origin->region,
                                                               "Country"=>$config->origin->countryCode,
                                                               )),
                   "ReceiverInformation"=>array("Address"=>array(//"PostalCode"=>$config->destPostalCode,
                                                                 "PostalCode"=>$config->dest->postalCode,
                                                               //"Name"=>"Foo Bar",
                                                               //"StreetNumber"=>"1234",
                                                               //"StreetName"=>"Main Street",
                                                               "City"=>$config->dest->city,
                                                               "Province"=>$config->dest->region,
                                                               "Country"=>$config->dest->countryCode,
                                                               //"PhoneNumber"=>array("CountryCode"=>"1","AreaCode"=>"905","Phone"=>"5555555"),
                                                                 )),
                   "PackageInformation"=>array("TotalWeight"=>array("Value"=>0,"WeightUnit"=>"lb"),
                                              "TotalPieces"=>0,
                                              "PiecesInformation"=>array("Piece"=>array()),
                                              "ServiceID"=>($config->dest->countryCode=="CA" ?
                                                "PurolatorExpress":($config->dest->countryCode=="US" ?
                                                "PurolatorExpressU.S.":"PurolatorExpressInternational")),

                                              ),
                   "PaymentInformation"=>array("PaymentType"=>"Sender",
                                               "BillingAccountNumber" => $config->billing_acct,
                                               "RegisteredAccountNumber"=>$config->registered_acct),
                   "PickupInformation"=>array("PickupType"=>"DropOff"),


               ),
               "ShowAlternativeServicesIndicator"=>"true")
               );
             $config->fullestimateclientitem=array("Weight"=>array("Value"=>0,"WeightUnit"=>"lb"),
                                                   "Length"=>array("Value"=>0,"DimensionUnit"=>"in"),
                                                   "Width"=>array("Value"=>0,"DimensionUnit"=>"in"),
                                                   "Height"=>array("Value"=>0,"DimensionUnit"=>"in"),
                                                   //"Options"
                                                  );
             //print_r($config->fullestimateclientrequest);
             $config->fullestimateclientrequest->Shipment->PackageInformation->PiecesInformation->Piece=array();
             $options = array();
             $configOptionsInformation = "";
             switch ($config->dest->countryCode) {
               case "CA":
                 $configOptionsInformation=$this->getConfigData("options_information_canada");
                 break;
               case "US":
                 $configOptionsInformation=$this->getConfigData("options_information_us");
                 break;
               default:
                 $configOptionsInformation=$this->getConfigData("options_information_international");
                 break;
             }

             // Remove whitespace from options
             $c=str_replace(array(" ","\t","\n","\r","\0","\x0b"),"",$configOptionsInformation);
             if ($c!="") {
               foreach(explode(",",$c) as $option) {
                 $detail    = explode("=",$option);
                 $options[] = (object)array("ID"=>$detail[0],"Value"=>$detail[1]);
               }
               //print_r($options);die();
               $config->fullestimateclientrequest->Shipment->PackageInformation->OptionsInformation=(object)array("Options"=>(object)array("OptionIDValuePair"=>array()));
               $config->fullestimateclientrequest->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair=$options;
             }

    }
    protected function _makeRequest($config) {
       // Full estimate request
       $config->shippable->prepareRequest();
       // Round up total weight to at least 1 lb
       $config->fullestimateclientrequest->Shipment->
             PackageInformation->TotalWeight->Value = max(1,$config->fullestimateclientrequest->Shipment->
             PackageInformation->TotalWeight->Value);
        // Round up individual weights to at least 1 lb
       foreach($config->fullestimateclientrequest->Shipment->
               PackageInformation->PiecesInformation->Piece  as $piece) {
             // If piece is less then 1lb round up
             $piece->Weight->Value = max($piece->Weight->Value,1);

       }
       $this->carrierdebug("Full Request **",$config->fullestimateclientrequest);
       $config->fullestimateclientresponse=$response = $config->estimateclient->GetFullEstimate($config->fullestimateclientrequest);

       /*
     echo "testing ....";
     var_dump($config->headinfo);
     echo "====== REQUEST HEADERS =====" . PHP_EOL;
     var_dump( $config->estimateclient->__getLastRequestHeaders());
     echo "========= REQUEST ==========" . PHP_EOL;
     var_dump( $config->estimateclient->__getLastRequest());
     echo "========= RESPONSE =========" . PHP_EOL;
     var_dump($response);
     echo "========= end =========" . PHP_EOL;
     */
         

       return $this->checkResponse($config,$response,"");
    }
     private function checkResponse(&$config, &$response, $methodprefix="") {
       $found=false;
       $this->carrierdebug("Response ",$response);
       $shipping = $config->shipping;
       $firstresponse = !isset($shipping->methodkey[$methodprefix]);
       $shipping->methodkey[$methodprefix]=$methodprefix;
       $methods = $shipping->methods;
       if ($response) {
       //print_r($response);die();
         if (!$response->ShipmentEstimates) {
           $error = "";
           foreach($this->foreachArray($response->ResponseInformation->Errors->Error) as $err) {
               $error .=$err->Description;
           }
           $this->carrierdebug("Error in response",$error );
           $config->responseError=$error;
         }
         else {

           // Estimates available
           foreach($this->foreachArray($response->ShipmentEstimates->ShipmentEstimate) as $estimate) {

             $taxes = 0;
             foreach($estimate->Taxes->Tax as $tax) {
               $taxes += $tax->Amount;
             }
             $estimate->RealTransitDays=$this->datediff(strtotime($estimate->ExpectedDeliveryDate),
                                                       strtotime($estimate->ShipmentDate));
             $estimate->PreTaxPrice=$estimate->TotalPrice - $taxes;
             if (stristr($config->allowed_methods,",".$estimate->ServiceID.",")!==FALSE) {
               $methodName = $methodprefix.$estimate->ServiceID;
                 if ($firstresponse) {
                   if (stristr($config->allowed_methods,",".$estimate->ServiceID.",")!==FALSE) {
                     $found = true;
                     $methods[$methodName]=$estimate;
                     // We need to remove taxes from the total
                   }
                 }
                 else if (isset($methods[$methodName])) {
                   // Was in the old methods, update quote with new amounts.
                   $oldestimate = $methods[$methodName];
                   $oldestimate->RealTransitDays=max($oldestimate->RealTransitDays, $estimate->RealTransitDays);
                   $oldestimate->PreTaxPrice+=$estimate->PreTaxPrice;
                   $methods[$methodName]=$oldestimate;
                   $found = true;
                 }
             }
           }

         }
       }
       else {
         $this->carriererror($config->responseError="Unable to contact purolator");
         return false;
       }
       if (!$found) {
         $this->carriererror("Unable to generate quote for item(s)");
         return false;
       }


       $config->shipping->methods=$methods;
       return true;
     }
     private function _buildShippingResult($config) {
             // Determine cheapest shipping method, if neccesary
             $freeshipmethod=$config->free_method;
             $foundGroupShippingMethod = false;
             $key = $config->dest->countryCode . "-". $config->dest->region;
             $totalValue = $config->request->getPackageValueWithDiscount();
             /*
             $customerGroup = Mage::getSingleton('customer/session')->getCustomerGroupId();
             if ($customerGroup && array_key_exists($key,$free_group_shipping)) {
               foreach ($free_group_shipping[$key] as $value) {
                 if (in_array($customerGroup,$value["group_list"]) && $totalValue>$minprice) {
                   // Found a match, we can not fetch the shipping methods and determine which is free
                   $lowestPrice=9999999999;
                   foreach ($config->shipping->methods as $method=>$estimate) {
                       if ($estimate->PreTaxPrice<$lowestPrice) {
                         $freeshipmethod=$method;
                         $lowestPrice=$estimate->PreTaxPrice;
                         $foundGroupShippingMethod=$method;
                       }
                   }

                 }
               }
                 // $free_group_shipping[$key][]=array("group_list"=>$customerGroups,"min_price"=$minprice);
             }
             */

             if ($foundGroupShippingMethod === false && $config->free_shipping_with_minimum_order_amount>0 &&
                   $config->request->getPackageValueWithDiscount()>=$config->free_shipping_with_minimum_order_amount &&
                   $config->free_method=="LowestPriceFreeShipping") {
                $lowestPrice=9999999999;
                foreach ($config->shipping->methods as $method=>$estimate) {
                   if ($estimate->PreTaxPrice<$lowestPrice) {
                     $freeshipmethod=$method;
                     $lowestPrice=$estimate->PreTaxPrice;
                   }
                }
             }

             // Got through all methods to create shipping options
             foreach ($config->shipping->methods as $method=>$estimate) {
                 $this->carrierdebug("Composed total estimate",$estimate);
                 
                 $rate = $this->_rateMethodFactory->create();;
                 $rate->setCarrier($this->_code);
                 $rate->setCarrierTitle($this->getConfigData('title'));

                 $rate->setMethod($method);
                 $rate->setCost($estimate->PreTaxPrice);
                 if ($config->json_description) {
                   $rate->setMethodDescription( json_encode(array("request"=>$config->fullestimateclientrequest,"response"=>$estimate)));
                 }

                 $totalPrice = max($estimate->PreTaxPrice*(1+$config->percent_markup/100)+$config->fixed_handling,
                                   $config->minimum_shipping_cost);


                 $methodTitle = $this->translate($config,$method) . " ("
                   . ($estimate->RealTransitDays + $config->days_for_handling) .
                     " ".$this->translate($config,"Business Day".(($estimate->RealTransitDays + $config->days_for_handling)>1?"s":"")).")";

                 // Add in any special magento pricing
                 
                 $totalPrice = $this->getFinalPriceWithHandlingFee($totalPrice);
                 
                 if ($foundGroupShippingMethod==$method) {
                   $this->carrierdebug("Free Shipping Detected, ",$config->runningPrice);
                   $totalPrice=0;
                   $methodTitle = $this->translate($config,"FREE")." " . $methodTitle;
                 } else if ($config->free_shipping_with_minimum_order_amount>0 &&
                   $config->runningPrice>$config->free_shipping_with_minimum_order_amount &&
                   $freeshipmethod==$method) {
                   $this->carrierdebug("Free Shipping Detected, ",$config->runningPrice);
                   $totalPrice=0;
                   $methodTitle = $this->translate($config,"FREE")." " . $methodTitle;
                 }
                 $rate->setMethodTitle($methodTitle);
                 $rate->setPrice($totalPrice);
                 $config->result->append($rate);
             } // end for

     }

    public function itemCallback($productData, $config) {
      if (!$config->shippable->addPiece($productData)) {
        return false;
      }
    }
    
    public function loadMyProduct($sku)
    {
        return $this->productRepository->get($sku);
    }
    protected function _getConfig(RateRequest $request) {
      $config=(object)array();
      $config->locale_choose = $this->getConfigData("locale_choose");
      $config->api_access_key = $this->getConfigData("api_access_key");
      $config->api_password = $this->getConfigData("api_password");
      $config->billing_acct = $this->getConfigData("billing_acct");
      $config->registered_acct = $this->getConfigData("registered_acct");
      $config->allowed_methods = ",".$this->getConfigData("allowed_methods").",";
      $config->free_method = $this->getConfigData("free_method");
      $config->free_shipping_with_minimum_order_amount = $this->getConfigData("free_shipping_with_minimum_order_amount");
      $config->minimum_shipping_cost = $this->getConfigData("minimum_shipping_cost");
      $config->fixed_handling = $this->getConfigData("fixed_handling");
      $config->percent_markup = $this->getConfigData("percent_markup");
      $config->days_for_handling = $this->getConfigData("days_for_handling");
      $config->convert_weight = $this->getConfigData("convert_weight");
      $config->virtual_box_mode = $this->getConfigData("virtual_box_mode");
      $config->max_package_weight = $this->getConfigData("max_package_weight")*$config->convert_weight;
      $config->calculate_dimension_weight = $this->getConfigData("calculate_dimension_weight");
      $config->dimensional_multiplier = $this->getConfigData("dimensional_multiplier");
      $config->address_validation = $this->getConfigData("address_validation");
      $config->sallowspecific = $this->getConfigData("sallowspecific");
      $config->specificerrmsg = $this->getConfigData("specificerrmsg");
      $config->developer_mode = $this->getConfigData("developer_mode");
      $config->log_mode = $this->getConfigData("log_mode");
      $config->originpostalcode = $this->getConfigData("originpostalcode");
      $config->widthattribute = $this->getConfigData("widthattribute");
      $config->defaultwidth = $this->getConfigData("defaultwidth");
      $config->lengthattribute = $this->getConfigData("lengthattribute");
      $config->defaultlength = $this->getConfigData("defaultlength");
      $config->heightattribute = $this->getConfigData("heightattribute");
      $config->defaultheight = $this->getConfigData("defaultheight");
      $config->convert_size = $this->getConfigData("convert_size");
      $config->dutiable = $this->getConfigData("dutiable");
      $config->handling_type = $this->getConfigData("handling_type");
      $config->handling_action = $this->getConfigData("handling_action");
      $config->handling_fee = $this->getConfigData("handling_fee");
      $config->address_validation_warning = $this->getConfigData("address_validation_warning");
      $config->store_response = $this->getConfigData("address_validation_warning");
      $config->json_description    = ($this->getConfigData('json_description')==1);

      
      
      $config->url= ($config->developer_mode==1 ? $this->_devUrl : $this->_liveUrl);
      $config->installPath = $this->_basepath  . DIRECTORY_SEPARATOR . 'wsdl' . DIRECTORY_SEPARATOR ;
      $config->wsdlpath=$config->installPath.($config->developer_mode==1 ? "Development" : "Production");
      
      /** @var \Magento\Framework\ObjectManagerInterface $om */
      $om = \Magento\Framework\App\ObjectManager::getInstance();
      /** @var \Magento\Framework\Locale\Resolver $resolver */
      $resolver = $om->get('Magento\Framework\Locale\Resolver');
      $storeLocale = $resolver->getLocale();
      $config->language = "en";
      if ($config->locale_choose=="fr") {
        $config->language = "fr";
      }
      else if ($config->locale_choose=="choose") {
        
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
      
      
       $config->origin=(object) array("postalCode"=>$origPostal,
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
       $config->dest=(object) array("postalCode"=>$destPostalCode,
          "countryCode" => $destCountry,
          "regionID" => $destRegion,
          "city" => $destCity,
          "region" => $destRegion) ;

      $config->runningPrice = 0;
      $this->_debug(array("Config"=>$config));
      
      // Store other items in the configuration
      $config->request = $request;
      $config->shipping=(object)array("methodkey"=>array(),"methods"=>array());
      $config->responseError="Shipment error, please contact us";
      $config->shippable = new Purolator_Shippable();
      $config->shippable->config = $config;
      $config->shippable->carrier = $this;
      $this->preptranslate($config);
      
      // Create the shipment request object
      /** @var \Magento\Shipping\Model\Rate\Result $result */
      $result = $this->_rateResultFactory->create();
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
"PurolatorExpress9AM"=>"Express 9AM",
"PurolatorExpress10:30AM"=>"Express 10:30AM",
"PurolatorExpress"=>"Express",
"PurolatorExpressPackU.S."=>"Express U.S. Pack ",
"PurolatorExpressU.S.Pack9AM"=>"Express U.S. Pack 9AM",
"PurolatorExpressU.S.Pack10:30AM"=>"Express U.S. Pack 10:30AM",
"PurolatorExpressEvening"=>"Express Evening",
"PurolatorExpressEnvelope9AM"=>"Express Envelope 9AM",
"PurolatorExpressEnvelope10:30AM"=>"Express Envelope 10:30AM",
"PurolatorExpressEnvelope"=>"Express Envelope",
"PurolatorExpressEnvelopeEvening"=>"Express Envelope Evening",
"PurolatorExpressPack9AM"=>"Express Pack 9AM",
"PurolatorExpressPack10:30AM"=>"Express Pack 10:30AM",
"PurolatorExpressPack"=>"Express Pack",
"PurolatorExpressPackEvening"=>"Express Pack Evening",
"PurolatorExpressBox9AM"=>"Express Box 9AM",
"PurolatorExpressBox10:30AM"=>"Express Box 10:30AM",
"PurolatorExpressBox"=>"Express Box",
"PurolatorExpressBoxEvening"=>"Express Box Evening",
"PurolatorGround"=>"Ground",
"PurolatorGround9AM"=>"Ground 9AM",
"PurolatorGround10:30AM"=>"Ground 10:30AM",
"PurolatorGroundEvening"=>"Ground Evening",
"PurolatorExpressU.S."=>"Express U.S.",
"PurolatorExpressU.S.9AM"=>"Express U.S. 9AM",
"PurolatorExpressU.S.10:30AM"=>"Express U.S. 10:30AM",
"PurolatorExpressU.S.12:00"=>"Express U.S. 12:00",
"PurolatorExpressEnvelopeU.S."=>"Express Envelope U.S.",
"PurolatorExpressU.S.Envelope9AM"=>"Express U.S. Envelope 9AM",
"PurolatorExpressU.S.Envelope10:30AM"=>"Express U.S. Envelope 10:30AM",
"PurolatorExpressU.S.Envelope12:00"=>"Express U.S. Envelope 12:00",
"PurolatorExpressU.S.Pack12:00"=>"Express U.S. Pack 12:00",
"PurolatorExpressBoxU.S."=>"Express U.S. Box",
"PurolatorExpressU.S.Box9AM"=>"Express U.S. Box 9AM",
"PurolatorExpressU.S.Box10:30AM"=>"Express U.S. Box 10:30AM",
"PurolatorExpressU.S.Box12:00"=>"Express U.S. Box 12:00",
"PurolatorGroundU.S."=>"Ground U.S.",
"PurolatorExpressInternational"=>"Express International",
"PurolatorExpressInternational9AM"=>"Express International 9AM",
"PurolatorExpressInternational10:30AM"=>"Express International 10:30AM",
"PurolatorExpressInternational12:00"=>"Express International 12:00",
"PurolatorExpressEnvelopeInternational"=>"Express Envelope International",
"PurolatorExpressInternationalEnvelope9AM"=>"Express International Envelope 9AM",
"PurolatorExpressInternationalEnvelope10:30AM"=>"Express International Envelope 10:30AM",
"PurolatorExpressInternationalEnvelope12:00"=>"Express International Envelope 12:00",
"PurolatorExpressPackInternational"=>"Express International Pack",
"PurolatorExpressInternationalPack9AM"=>"Express International Pack 9AM",
"PurolatorExpressInternationalPack10:30AM"=>"Express International Pack 10:30AM",
"PurolatorExpressInternationalPack12:00"=>"Express International Pack 12:00",
"PurolatorExpressBoxInternational"=>"Express International Box",
"PurolatorExpressInternationalBox9AM"=>"Express International Box 9AM",
"PurolatorExpressInternationalBox10:30AM"=>"Express International Box 10:30AM",
"PurolatorExpressInternationalBox12:00"=>"Express International Box 12:00",
"PurolatorGroundDistribution"=>"Ground Distribution",


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
        
    "PurolatorExpress9AM"=>array("en"=>"Express 9AM","fr"=>"Express 9 h"),
    "PurolatorExpress10:30AM"=>array("en"=>"Express 10:30AM","fr"=>"Express 10 h 30"),
    "PurolatorExpress"=>array("en"=>"Express","fr"=>"Express"),
    "PurolatorExpressPackU.S."=>array("en"=>"Express U.S. Pack ","fr"=>"Express Soirée"),
    "PurolatorExpressU.S.Pack9AM"=>array("en"=>"Express U.S. Pack 9AM","fr"=>"Express Pack 9 h vers les É.-U."),
    "PurolatorExpressU.S.Pack10:30AM"=>array("en"=>"Express U.S. Pack 10:30AM","fr"=>"Express Pack 10 h 30 vers les É.-U."),
    "PurolatorExpressEvening"=>array("en"=>"Express Evening","fr"=>"Express Soirée"),
    "PurolatorExpressEnvelope9AM"=>array("en"=>"Express Envelope 9AM","fr"=>"Express Enveloppe 9 h"),
    "PurolatorExpressEnvelope10:30AM"=>array("en"=>"Express Envelope 10:30AM","fr"=>"Express Enveloppe 10 h 30"),
    "PurolatorExpressEnvelope"=>array("en"=>"Express Envelope","fr"=>"Express Enveloppe"),
    "PurolatorExpressEnvelopeEvening"=>array("en"=>"Express Envelope Evening","fr"=>"Express Enveloppe Soirée"),
    "PurolatorExpressPack9AM"=>array("en"=>"Express Pack 9AM","fr"=>"Express Pack 9 h"),
    "PurolatorExpressPack10:30AM"=>array("en"=>"Express Pack 10:30AM","fr"=>"Express Pack 10 h 30"),
    "PurolatorExpressPack"=>array("en"=>"Express Pack","fr"=>"Express Pack"),
    "PurolatorExpressPackEvening"=>array("en"=>"Express Pack Evening","fr"=>"Express Pack Soirée"),
    "PurolatorExpressBox9AM"=>array("en"=>"Express Box 9AM","fr"=>"Express Boîte 9 h"),
    "PurolatorExpressBox10:30AM"=>array("en"=>"Express Box 10:30AM","fr"=>"Express Boîte 10 h 30"),
    "PurolatorExpressBox"=>array("en"=>"Express Box","fr"=>"Express Boîte"),
    "PurolatorExpressBoxEvening"=>array("en"=>"Express Box Evening","fr"=>"Express Boîte Soirée"),
    "PurolatorGround"=>array("en"=>"Ground","fr"=>"Routier"),
    "PurolatorGround9AM"=>array("en"=>"Ground 9AM","fr"=>"Routier 9 h"),
    "PurolatorGround10:30AM"=>array("en"=>"Ground 10:30AM","fr"=>"Routier 10 h 30"),
    "PurolatorGroundEvening"=>array("en"=>"Ground Evening","fr"=>"Routier"),
    "PurolatorExpressU.S."=>array("en"=>"Express U.S.","fr"=>"Routier Soirée"),
    "PurolatorExpressU.S.9AM"=>array("en"=>"Express U.S. 9AM","fr"=>"Express 9 h vers les É.-U."),
    "PurolatorExpressU.S.10:30AM"=>array("en"=>"Express U.S. 10:30AM","fr"=>"Express 10 h 30 vers les É.-U."),
    "PurolatorExpressU.S.12:00"=>array("en"=>"Express U.S. 12:00","fr"=>"Express Midi vers les É.-U."),
    "PurolatorExpressEnvelopeU.S."=>array("en"=>"Express Envelope U.S.","fr"=>"Express Enveloppe vers les É.-U."),
    "PurolatorExpressU.S.Envelope9AM"=>array("en"=>"Express U.S. Envelope 9AM","fr"=>"Express Enveloppe 9 h vers les É.-U."),
    "PurolatorExpressU.S.Envelope10:30AM"=>array("en"=>"Express U.S. Envelope 10:30AM","fr"=>"Express Enveloppe 10 h 30 vers les É.-U."),
    "PurolatorExpressU.S.Envelope12:00"=>array("en"=>"Express U.S. Envelope 12:00","fr"=>"Express Enveloppe Midi vers les É.-U."),
    "PurolatorExpressU.S.Pack12:00"=>array("en"=>"Express U.S. Pack 12:00","fr"=>"Express Pack Midi vers les É.-U."),
    "PurolatorExpressBoxU.S."=>array("en"=>"Express U.S. Box","fr"=>"Express Boîte vers les É.-U."),
    "PurolatorExpressU.S.Box9AM"=>array("en"=>"Express U.S. Box 9AM","fr"=>"Express Boîte 9 h vers les É.-U."),
    "PurolatorExpressU.S.Box10:30AM"=>array("en"=>"Express U.S. Box 10:30AM","fr"=>"Express Boîte 10 h 30 vers les É.-U."),
    "PurolatorExpressU.S.Box12:00"=>array("en"=>"Express U.S. Box 12:00","fr"=>"Express Boîte Midi vers les É.-U."),
    "PurolatorGroundU.S."=>array("en"=>"Ground U.S.","fr"=>"Routier vers les É.-U."),
    "PurolatorExpressInternational"=>array("en"=>"Express International","fr"=>"Express International"),
    "PurolatorExpressInternational9AM"=>array("en"=>"Express International 9AM","fr"=>"Express 9 h International"),
    "PurolatorExpressInternational10:30AM"=>array("en"=>"Express International 10:30AM","fr"=>"Express 10 h 30 International"),
    "PurolatorExpressInternational12:00"=>array("en"=>"Express International 12:00","fr"=>"Express Midi International"),
    "PurolatorExpressEnvelopeInternational"=>array("en"=>"Express Envelope International","fr"=>"Express Enveloppe International"),
    "PurolatorExpressInternationalEnvelope9AM"=>array("en"=>"Express International Envelope 9AM","fr"=>"Express Enveloppe 9 h International"),
    "PurolatorExpressInternationalEnvelope10:30AM"=>array("en"=>"Express International Envelope 10:30AM","fr"=>"Express Enveloppe 10 h 30 International"),
    "PurolatorExpressInternationalEnvelope12:00"=>array("en"=>"Express International Envelope 12:00","fr"=>"Express Enveloppe Midi International"),
    "PurolatorExpressPackInternational"=>array("en"=>"Express International Pack","fr"=>"Express Pack International"),
    "PurolatorExpressInternationalPack9AM"=>array("en"=>"Express International Pack 9AM","fr"=>"Express Pack 9 h International"),
    "PurolatorExpressInternationalPack10:30AM"=>array("en"=>"Express International Pack 10:30AM","fr"=>"Express Pack 10 h 30 International"),
    "PurolatorExpressInternationalPack12:00"=>array("en"=>"Express International Pack 12:00","fr"=>"Express Pack Midi International"),
    "PurolatorExpressBoxInternational"=>array("en"=>"Express International Box","fr"=>"Express Boîte International"),
    "PurolatorExpressInternationalBox9AM"=>array("en"=>"Express International Box 9AM","fr"=>"Express Boîte International 9 h"),
    "PurolatorExpressInternationalBox10:30AM"=>array("en"=>"Express International Box 10:30AM","fr"=>"Express Boîte International 10 h 30"),
    "PurolatorExpressInternationalBox12:00"=>array("en"=>"Express International Box 12:00","fr"=>"Express Boîte International Midi"),
    "PurolatorGroundDistribution"=>array("en"=>"Ground Distribution","fr"=>"Routier - Distribution"),

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
      $this->_debug(json_encode($value));
    }
    function carriererror(...$value) {
      $this->_logger->error(json_encode($value));
    }

}


class Purolator_Shippable_Box {
      public $runningWeight=0;
      public $runningSize=0;
      public $config=false;
      public $carrier=false;
      public $items = array();

      public function addPiece(&$piece, &$productData) {
        if ($this->runningWeight+$piece->Weight->Value>$this->config->max_package_weight) {
            $this->config->responseError="Item weight to large";
            return false;
        }
        if ($this->config->calculate_dimension_weight==1) {
          // Use the formula to calculate the
          $newweight= (($productData->length*
                              $productData->width*
                              $productData->height)/1728)*$this->config->dimensional_multiplier;
          $this->carrier->carrierdebug("Calculated weight,$newweight was, ".$piece->Weight->Value .",Product ".$productData->Product);
          
          if ($piece->Weight->Value<$newweight) {
            $piece->Weight->Value=$newweight;
          }                    
        }
        
        $this->runningWeight+=$piece->Weight->Value;
        
        $this->items[]=$piece;
        return true;
      }
      
    }
    class Purolator_Shippable {
      public $config=false;
      public $carrier=false;
      protected $boxes = array();
      public function addPiece(&$productData) {
        for($x=$productData->qty;$x>0;$x--) {      
          $piece = $this->carrier->arrayToObject($this->config->fullestimateclientitem);
          $piece->Weight->Value=$productData->Weight;
          $this->config->fullestimateclientrequest->Shipment->
            PackageInformation->TotalWeight->Value+=$productData->Weight;
            
          if ($this->config->virtual_box_mode==1) {
            // find a box for the piece
            if (!$this->findBox($piece,$productData)) {
                // $productData->Product
                  $this->config->responseError="Item too large to ship. ";
                  $error = Mage::getModel('shipping/rate_result_error');
                  $error->setCarrier($this->carrier->_code);
                  $error->setCarrierTitle($this->carrier->getConfigData('title'));
                  $error->setErrorMessage($this->carrier->getConfigData('specificerrmsg') . " - ".$this->config->responseError);
                  $this->config->result->append($error);
                $this->carrier->carriererror(array("Item too big for quote", $productData));
                
                return false;
            }

          }
          else {
                if ($this->config->calculate_dimension_weight==1) {
                  $piece->Length->Value=$productData->length;
                  $piece->Width->Value=$productData->width;
                  $piece->Height->Value=$productData->height;
                }
                $this->config->fullestimateclientrequest->Shipment->
                  PackageInformation->PiecesInformation->Piece[]=$piece;
                $this->config->fullestimateclientrequest->Shipment->
                  PackageInformation->TotalPieces++;
          }
        }  
      
        return true;
      }
      public function findBox(&$piece, &$productData) {
        $found = false;
        foreach($this->boxes as $box) {
          if ($box->addPiece($piece,$productData)) {
            $found = true;
            break;
          }
        }
        if (!$found) {
          $box = new Purolator_Shippable_Box();
          $box->config = $this->config;
          $box->carrier = $this->carrier;
          if (!$box->addPiece($piece, $productData)) {
            $this->carrier->carriererror(array("Item too big for quote", $productData));
            return false;
          }
          $this->boxes[]=$box;
        }
        return true;
      }
      public function prepareRequest() {
        if ($this->config->virtual_box_mode==1) {
          foreach($this->boxes as $box) {

            $piece = $this->carrier->arrayToObject($this->config->fullestimateclientitem);
            $piece->Weight->Value=$box->runningWeight;
            
            $this->config->fullestimateclientrequest->Shipment->
              PackageInformation->PiecesInformation->Piece[]=$piece;
            $this->config->fullestimateclientrequest->Shipment->
              PackageInformation->TotalPieces++;
            
          }
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
                $config->widthattribute,
                $config->lengthattribute,
                $config->heightattribute])
              ->addIdFilter($productID);
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
        
        if ($this->_request->getAllItems()) {
              foreach ($this->_request->getAllItems() as $item) {
                  if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                      continue;
                  }
                  if ($item->getHasChildren() && $item->isShipSeparately()) {
                      foreach ($item->getChildren() as $child) {
                          if (!$child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                              // $callback(CartItemWrapper("2",$child),$config)
                              $product = $this->WrapItem($child,$config);
                              if ($product!==false) {
                                call_user_func_array(array($source, $callback),array($product,$config));
                              }

                          }
                      }
                  } elseif (!$item->getFreeShipping()) {
                      //$callback(CartItemWrapper("2",$child),$config)
                      $product = $this->WrapItem($item,$config);
                      if ($product!==false) {
                        call_user_func_array(array($source, $callback),array($product,$config));
                      }
                  }
              }
          }
        }
      
      }
      public function WrapItem($invoiceItem, $config) {
         $productId = $invoiceItem->getProductId();
         $productQuery = $this->productQuery;
         $product = $productQuery($invoiceItem,$config);
         $weight = ($invoiceItem->getWeight()*$config->convert_weight);
         $productData=(object)array("Product"=>$productId,"Weight"=>$weight);
        if ($product) {
          if ($config->calculate_dimension_weight==1) {
            $productData->length = $product->getData($config->lengthattribute)*$config->convert_size;
            if ($productData->length=="" && $config->defaultlength>0) {
              $productData->length = $config->defaultlength*$config->convert_size;
            }
            
            $productData->width = $product->getData($config->widthattribute)*$config->convert_size;
            if ($productData->width=="" && $config->defaultwidth>0) {
              $productData->width = $config->defaultwidth*$config->convert_size;
            }
            
            $productData->height = $product->getData($config->heightattribute)*$config->convert_size;
            if ($productData->height=="" && $config->defaultheight>0) {
              $productData->height = $config->defaultheight*$config->convert_size;
            }
          } else {
            $productData->length=0;
            $productData->width=0;
            $productData->height=0;
          }
          
          
          $productData->qty =  $invoiceItem->getQty();
          $productData->price = $invoiceItem->getPrice();
          $productData->totalprice = $invoiceItem->getPrice()*$productData->qty;
          
          $config->runningPrice+= $invoiceItem->getPrice()*$productData->qty;
          
          $this->carrier->debug(array("Product data"=>$productData));
         }
        else {
          // Product not found
          $this->carrier->carriererror(array("Product not found :"=> $product));
          return false;
        }
        return $productData;
      }
}



