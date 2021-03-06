<?php

namespace Unirgy\Dropship\Model\Pdf;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Psr\Log\LoggerInterface;
use \Unirgy\Dropship\Helper\Data as HelperData;
use \Unirgy\Dropship\Model\Source;

class Statement extends AbstractPdf
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Source
     */
    protected $_src;

    protected $inlineTranslation;

    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        HelperData $helper,
        LoggerInterface $logger,
        ScopeConfigInterface $configScopeConfigInterface,
        Source $source,
        array $data = []
    )
    {
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $configScopeConfigInterface;
        $this->_src = $source;

        parent::__construct($helper, $logger, $data);
    }

    protected $_curPageNum;
    protected $_pageFooter = array();
    protected $_globalTotals = array();
    protected $_globalTotalsAmount = array();
    protected $_totalsPageNum = 0;

    public function before()
    {
        $this->inlineTranslation->suspend();

        $pdf = new \Zend_Pdf();
        $this->setPdf($pdf);

        return $this;
    }

    public function isInPayoutAmount($amountType, $inPayoutOption, $vId=null)
    {
    	if (is_null($vId)) {
    		$vendor = $this->getStatement()->getVendor();
    	} else {
    		$vendor = $this->_hlp->getVendor($vId);
    	}
    	$inPayoutOption = $inPayoutOption == 'include' ? array('', 'include') : array($inPayoutOption);
    	$hideTax = in_array($vendor->getStatementTaxInPayout(), $inPayoutOption);
    	$hideShipping = in_array($vendor->getStatementShippingInPayout(), $inPayoutOption);
        $hideDiscount = in_array($vendor->getStatementDiscountInPayout(), $inPayoutOption);
    	$hideBoth = $hideTax && $hideShipping && $hideDiscount;
    	switch ($amountType) {
    		case 'all':
    			return $hideBoth;
    		case 'shipping':
    			return $hideShipping;
            case 'discount':
                return $hideDiscount;
    		case 'tax':
    			return $hideTax;
    	}
    }
    
    public function addStatement($statement)
    {
        $hlp = $this->_hlp;
        $this->setStatement($statement);

        $ordersData = \Zend_Json::decode($statement->getOrdersData());
        
        // first front page header
        $this->_curPageNum = 0;
        $this->addPage()->insertPageHeader(array('first'=>true, 'data'=>$ordersData));

        if (!empty($ordersData['orders'])) {
            // iterate through orders
            foreach ($ordersData['orders'] as $order) {
                $this->insertOrder($order);
            }
        } else {
            $this->text(__('No orders found for this period.'), 'down')
                ->moveRel(0, .5);
        }

        $this->insertTotals($ordersData['totals']);
        
        $this->insertAdjustmentsPage(/*array('first'=>true, 'data'=>$ordersData)*/);

        if ($this->_hlp->isStatementRefundsEnabled()) {
            $this->insertRefundsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
        }

        if ($hlp->isUdpayoutActive()) {
        	$this->insertPayoutsPage(/*array('first'=>true, 'data'=>$ordersData)*/);
        }

        $this->setAlign('left')->font('normal', 10);
        foreach ($this->_pageFooter as $k=>&$p) {
            if (!empty($p['done'])) {
                continue;
            }
            $p['done'] = true;
            $str = __('%1 for %2 - Page %3 of %4',
                $statement->getVendor()->getVendorName(),
                $statement->getStatementPeriod(),
                $p['page_num'],
                $this->_curPageNum
            );
            $this->setPage($this->getPdf()->pages[$k])->move(.5, 10.6)->text($str);
        }
        unset($p);
        #$this->font('normal', 10)->setAlign('right')->addPageNumbers(8.25, .25);

        if (($vId = $statement->getVendor()->getId())) {
            $totals = $ordersData['totals'];
            $totals['vendor_name'] = $statement->getVendor()->getVendorName();
            $this->_globalTotals[$vId] = $totals;
            $this->_globalTotalsAmount[$vId] = isset($ordersData['totals_amount']) ? $ordersData['totals_amount'] : $ordersData['totals'];
        }
        
        return $this;
    }

    public function after()
    {
        $this->inlineTranslation->resume();
        return $this;
    }

    public function logoHeight($store)
    {
        $logoAR = $this->_hlp->getScopeConfig('udropship/admin/letterhead_logo_ratio', $store);
        $logoAR = explode('x', $logoAR, 2);
        $height = 1;
        if (!empty($logoAR[1])) {
            $logoAR[0] = round($logoAR[0], 2);
            $logoAR[1] = round((float)$logoAR[1], 2);
            if ($logoAR[0]>0 && $logoAR[1]>0) {
                $height = round(2*$logoAR[1]/$logoAR[0], 2);
            }
        }
        return $height;
    }
    
    protected function _insertPageHeader($params=array())
    {
        $hlp = $this->_hlp;
        $store = null;

        // letterhead info
        $this->move(.5, .5)
            ->font('normal', 10)
            ->text($this->_hlp->getScopeConfig('udropship/admin/letterhead_info', $store));
        // letterhead logo
        $image = $this->_hlp->getScopeConfig('udropship/admin/letterhead_logo', $store);
        if ($image) {
            $image = $this->_hlp->getScopeConfig('system/filesystem/media', $store) . '/udropship/' . $image;
            $this->move(6, .5)->image($image, 2, $this->logoHeight($store));
        }
        $this->move(.5, 2);
        // only for first page
        if (!empty($params['first'])) {
            $statement = $this->getStatement();
            $vendor = $statement->getVendor();
            // vendor info
            $this->font('normal', 12)
                ->text($vendor->getBillingInfo());
            // statement info
            $stInfoHeight = $this->getTextHeight()*2;
            if ($hlp->isUdpoActive()) {
                $stInfoHeight += $this->getTextHeight();
            }
            $stTotalHeight = $this->getTextHeight();
            if ($hlp->isUdpayoutActive()) {
                $stTotalHeight += $this->getTextHeight()*2;
            }
            $this->setAlign('right')
                ->move(6, 2)
                    ->text(__("Statement #"), 'down')
                    ->text(__("Statement Date"), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text(__("PO Type"), 'down');
            }
            $this->move(7.9, 2)
                    ->text($statement->getStatementId(), 'down')
                    ->text($this->_hlp->formatDate($statement->getCreatedAt(), \IntlDateFormatter::MEDIUM), 'down');
            if ($hlp->isUdpoActive()) {
                $this->text($this->_src->setPath('statement_po_type')->getOptionLabel($statement->getPoType()), 'down');
            }
            $stTotalRectMargin = $this->getTextHeight()*.4;
            $stTotalRectPad = $this->getTextHeight()*.3;
            $stTotalRectY = 2+$stInfoHeight+$stTotalRectMargin;
            $stTotalTxtY = 2+$stInfoHeight+$stTotalRectMargin+$stTotalRectPad;
            $stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
            // statement total
            $this->move(4.5, $stTotalRectY)
                ->rectangle(3.5, $stTotalHeightOut, .8, .8)
                ->font('bold')
                ->move(6, $stTotalTxtY);
            if (!$this->_hlp->isStatementAsInvoice()) {
                $this->text(__("Total Payment"), 'down');
                if ($hlp->isUdpayoutActive()) {
                    $this->text(__("Total Paid"), 'down')
                        ->text(__("Total Due"), 'down');
                }
            } else {
                $this->text(__("Total Invoice"), 'down');
            }
            if (!$this->_hlp->isStatementAsInvoice()) {
                $this->move(7.9, $stTotalTxtY)
                        ->text($params['data']['totals']['total_payout'], 'down');
                if ($hlp->isUdpayoutActive()) {
                    $this->text($params['data']['totals']['total_paid'], 'down')
                        ->text($params['data']['totals']['total_due'], 'down');
                }
            } else {
                $this->move(7.9, $stTotalTxtY)
                    ->text($params['data']['totals']['total_invoice'], 'down');
            }
            $this->move(.5, $stTotalRectY+$stTotalHeightOut+$stTotalRectMargin);
        }

        return $this;
    }

    public function insertPageHeader($params=array())
    {
        $hlp = $this->_hlp;
        $store = null;

        $this->_insertPageHeader($params);
        // grid titles
        $this->insertGridHeader();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }
    
    public function insertGridHeader()
    {
    	$hideTax = $this->getStatement()->getVendor()->getStatementTaxInPayout() == 'exclude_hide';
        $hideDiscount = $this->getStatement()->getVendor()->getStatementDiscountInPayout() == 'exclude_hide';
    	$hideShipping = $this->getStatement()->getVendor()->getStatementShippingInPayout() == 'exclude_hide';
    	$hideBoth = $hideTax && $hideShipping && $hideDiscount;
        $hlp = $this->_hlp;
        $this->rectangle(7.5, .4, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text(__("Date"));
        if ($this->isInPayoutAmount('all', 'exclude_hide')) {
            $this->moveRel(1.2, 0)->text(__("Order#"))
            	->moveRel(1.6, 0)->text(__("Product"))
                ->moveRel(1.5, 0)->text(__("Comm (%)/Trans"))
                ->moveRel(1.6, 0)->text(__("Net Amount"))
            ->movePop(0, .4);
        } else {
        	$this->moveRel(0.8, 0)->text(__("Order#"))
            	->moveRel(1, 0)->text(__("Product"));
            $isInPayoutLabel = '';
            foreach (array(
                'shipping' => __("Shipping"),
                'tax' => __("Tax"),
                'discount' => __("Discount")
                ) as $iipKey=>$iipLabel
            ) {
                if (!$this->isInPayoutAmount($iipKey, 'exclude_hide')) {
                    $isInPayoutLabel .= $iipLabel.'/';
                }
            }
            $isInPayoutLabel = substr($isInPayoutLabel, 0, -1);
            $this->moveRel(1, 0)->text($isInPayoutLabel);
            $this->moveRel(2, 0)->text(__("Comm (%)/Trans"))
                ->moveRel(1.6, 0)->text(__("Net Amount"))
            ->movePop(0, .4)
        	;
        }

        return $this;
    }
    
    public function insertAdjustmentsPageHeader($params=array())
    {
        $hlp = $this->_hlp;
        $store = null;

        $this->_insertPageHeader($params);
        // grid titles
        $this->insertAdjustmentsGridHeader();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }
    
    public function insertAdjustmentsGridHeader()
    {
        $hlp = $this->_hlp;
        $this->movePush()->moveRel(3.5)
            ->font('bold', 16)->setAlign('center')->text(__('Extra Adjustments'))
            ->movePop(0, .5);
        $this->rectangle(7.5, .4, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text(__("Adjustment#"))
                ->moveRel(1.2, 0)->text(__("PO ID"))
                ->moveRel(.8, 0)->text(__("PO Type"))
                ->moveRel(.8, 0)->text(__("Amount"))
                ->moveRel(.8, 0)->text(__("Username"))
                ->moveRel(.8, 0)->text(__("Comment"))
                ->moveRel(2.5, 0)->text(__("Date"))
            ->movePop(0, .4)
        ;

        return $this;
    }
    
    public function insertPayoutsPageHeader($params=array())
    {
        $hlp = $this->_hlp;
        $store = null;

        $this->_insertPageHeader($params);
        // grid titles
        $this->insertPayoutsGridHeader();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }
    
    public function insertPayoutsGridHeader()
    {
        $hlp = $this->_hlp;
        $this->movePush()->moveRel(3.5)
            ->font('bold', 16)->setAlign('center')->text('Payouts')
            ->movePop(0, .5);
        $this->rectangle(7.5, .4, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 12)
                ->setAlign('left')
                ->text(__("ID"))
                ->moveRel(.8, 0)->text(__("Type"))
                ->moveRel(.8, 0)->text(__("Method"))
                ->moveRel(.8, 0)->text(__("Status"))
                ->moveRel(.6, 0)->text(__("# of Orders"))
                ->moveRel(1, 0)->text(__("Payout"))
                ->moveRel(.8, 0)->text(__("Paid"))
                ->moveRel(.8, 0)->text(__("Due"))
                ->moveRel(.8, 0)->text(__("Date"))
            ->movePop(0, .4)
        ;

        return $this;
    }

    public function insertRefundsPageHeader($params=array())
    {
        $hlp = $this->_hlp;
        $store = null;

        $this->_insertPageHeader($params);
        // grid titles
        $this->insertRefundsGridHeader();

        $this->_curPageNum++;
        $this->_pageFooter[] = array('page_num'=>$this->_curPageNum);

        return $this;
    }

    public function insertRefundsGridHeader()
    {
        $hideTax = $this->getStatement()->getVendor()->getStatementTaxInPayout() == 'exclude_hide';
        $hideDiscount = $this->getStatement()->getVendor()->getStatementDiscountInPayout() == 'exclude_hide';
        $hideShipping = $this->getStatement()->getVendor()->getStatementShippingInPayout() == 'exclude_hide';
        $hideBoth = $hideTax && $hideShipping && $hideDiscount;
        $hlp = $this->_hlp;

        $this->movePush()->moveRel(3.5)
            ->font('bold', 16)->setAlign('center')->text(__('Refunds'))
            ->movePop(0, .5);

        $this->rectangle(7.5, .4, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
            ->font('bold', 12)
            ->setAlign('left')
            ->text(__("Date"));
        if ($this->isInPayoutAmount('all', 'exclude_hide')) {
            $this->moveRel(1.2, 0)->text(__("Order#"))
                ->moveRel(1.6, 0)->text(__("Product"))
                ->moveRel(1.5, 0)->text(__("Comm (%)"))
                ->moveRel(1.6, 0)->text(__("Total Refund"))
                ->movePop(0, .4);
        } else {
            $this->moveRel(0.8, 0)->text(__("Order#"))
                ->moveRel(1, 0)->text(__("Product"));
            $isInPayoutLabel = '';
            foreach (array(
                         'shipping' => __("Shipping"),
                         'tax' => __("Tax"),
                         'discount' => __("Discount")
                     ) as $iipKey=>$iipLabel
            ) {
                if (!$this->isInPayoutAmount($iipKey, 'exclude_hide')) {
                    $isInPayoutLabel .= $iipLabel.'/';
                }
            }
            $isInPayoutLabel = substr($isInPayoutLabel, 0, -1);
            $this->moveRel(1, 0)->text($isInPayoutLabel);
            $this->moveRel(2, 0)->text(__("Comm (%)"))
                ->moveRel(1.6, 0)->text(__("Total Refund"))
                ->movePop(0, .4)
            ;
        }

        return $this;
    }

    public function insertRefundsPage($params=array())
    {
        $hlp = $this->_hlp;

        $this->_totalsPageNum = 0;

        if (!$this->getStatement()->getRefunds()) return $this;

        $this->addPage()->insertRefundsPageHeader($params);

        foreach ($this->getStatement()->getRefunds() as $refund) {

            $this->checkPageOverflow(.5, 'insertRefundsPageHeader')
                ->setMaxHeight(0)
                ->font('normal', 10)
                ->movePush()
                ->setAlign('left')
                ->text($this->_hlp->formatDate($refund['date'], \IntlDateFormatter::SHORT));
            if ($this->isInPayoutAmount('all', 'exclude_hide')) {
                $this->moveRel(1.2, 0)->text($refund['order_increment_id'])
                    ->moveRel(1.6, 0)->text('-'.$refund['subtotal'])
                    ->moveRel(1.5, 0)->text("{$refund['com_amount']} ({$refund['com_percent']}%)")
                    ->setAlign('right')
                    ->moveRel(3, 0)->text('-'.@$refund['total_refund']);
            } else {
                $this->moveRel(.8, 0)->text($refund['id'])
                    ->moveRel(1, 0)->text('-'.$refund['subtotal']);

                $isInPayoutLabel = '';
                foreach (array(
                             'shipping' => '-'.$refund['shipping'],
                             'tax' => '-'.$refund['tax'],
                             'discount' => @$refund['discount']
                         ) as $iipKey=>$iipLabel
                ) {
                    if (!$this->isInPayoutAmount($iipKey, 'exclude_hide')) {
                        $isInPayoutLabel .= $iipLabel.' / ';
                    }
                }
                $isInPayoutLabel = substr($isInPayoutLabel, 0, -3);
                $this->moveRel(1, 0)->text($isInPayoutLabel);

                $this->moveRel(2, 0)->text("{$refund['com_amount']} ({$refund['com_percent']}%)")
                    ->setAlign('right')
                    ->moveRel(2.5, 0)->text('-'.@$refund['total_refund']);

            }
            $this->movePop(0, $this->getMaxHeight()+5, 'point')
                ->moveRel(-.1, 0)
                ->line(7.5, 0, .7)
                ->moveRel(.1, .1)
            ;


        }

        return $this;
    }

    public function insertOrder($order)
    {

        foreach (array('trans_fee','com_percent','com_amount') as $_k) {
            $order[$_k] = strpos($order[$_k], '-') === 0
                ? substr($order[$_k], 1)
                : '-'.$order[$_k];
        }

        $this->checkPageOverflow()
            ->setMaxHeight(0)
            ->font('normal', 10)
            ->movePush()
                ->setAlign('left')
                    ->text($this->_hlp->formatDate($order['date'], \IntlDateFormatter::SHORT));
		if ($this->isInPayoutAmount('all', 'exclude_hide')) {
			$this->moveRel(1.2, 0)->text($order['id'])
                ->moveRel(1.6, 0)->text($order['subtotal'])
                ->moveRel(1.5, 0)->text("{$order['com_amount']} ({$order['com_percent']}%) / {$order['trans_fee']}")
            	->setAlign('right');
            if (!$this->_hlp->isStatementAsInvoice()) {
                $this->moveRel(3, 0)->text($order['total_payout']);
            } else {
                $this->moveRel(3, 0)->text($order['total_invoice']);
            }
		} else {
            $this->moveRel(.8, 0)->text($order['id'])
                ->moveRel(1, 0)->text($order['subtotal']);

            $isInPayoutLabel = '';
            foreach (array(
                'shipping' => $order['shipping'],
                'tax' => $order['tax'],
                'discount' => '-'.@$order['discount']
                ) as $iipKey=>$iipLabel
            ) {
                if (!$this->isInPayoutAmount($iipKey, 'exclude_hide')) {
                    $isInPayoutLabel .= $iipLabel.' / ';
                }
            }
            $isInPayoutLabel = substr($isInPayoutLabel, 0, -3);
            $this->moveRel(1, 0)->text($isInPayoutLabel);

			$this->moveRel(2, 0)->text("{$order['com_amount']} ({$order['com_percent']}%) / {$order['trans_fee']}")
                ->setAlign('right');
            if (!$this->_hlp->isStatementAsInvoice()) {
                $this->moveRel(2.5, 0)->text($order['total_payout']);
            } else {
                $this->moveRel(2.5, 0)->text($order['total_invoice']);
            }

		}
		$this->movePop(0, $this->getMaxHeight()+5, 'point')
            ->moveRel(-.1, 0)
            ->line(7.5, 0, .7)
            ->moveRel(.1, .1)
        	;

        if (!empty($order['adjustments'])) {
            foreach ($order['adjustments'] as $adj) {
                $this->checkPageOverflow()
                    ->setMaxHeight(0)
                    ->font('normal', 10)
                    ->movePush()
                        ->setAlign('left')
                            ->text($this->_hlp->formatDate($order['date'], \IntlDateFormatter::SHORT))
                            ->moveRel(.8, 0)->text($order['id'])
                            ->moveRel(1, 0)->text($adj['comment'], null, 70)
                        ->setAlign('right')
                            ->moveRel(5.5, 0)->price($adj['amount'])
                    ->movePop(0, $this->getMaxHeight()+5, 'point')
                    ->moveRel(-.1, 0)
                    ->line(7.5, 0, .7)
                    ->moveRel(.1, .1)
                ;
            }
        }

        return $this;
    }

    public function insertTotals($totals)
    {
        $hlp = $this->_hlp;

        foreach (array('trans_fee','com_amount') as $_k) {
            $totals[$_k] = strpos($totals[$_k], '-') === 0
                ? substr($totals[$_k], 1)
                : '-'.$totals[$_k];
        }

        $this->checkPageOverflow(1.5)
            ->moveRel(-.1, 0)
            ->rectangle(7.5, .05, .8, .8)
            ->moveRel(5.7, .2)
            ->movePush()
                ->setAlign('right')
                ->font('bold', 12)
                    ->text(__("Total Product Revenue"), 'down')
                ->font('normal');
                if ($this->isInPayoutAmount('tax', 'include')) {
                    $this->text(__("Total Tax"), 'down');
                } elseif ($this->isInPayoutAmount('tax', 'exclude_show')) {
                    $this->text(__("Total Tax (non-payable)"), 'down');
                }
                
                if ($this->isInPayoutAmount('shipping', 'include')) {
                    $this->text(__("Total Shipping"), 'down');
                } elseif ($this->isInPayoutAmount('shipping', 'exclude_show')) {
                	$this->text(__("Total Shipping (non-payable)"), 'down');
                }

                if ($this->isInPayoutAmount('discount', 'include')) {
                    $this->text(__("Total Discount"), 'down');
                } elseif ($this->isInPayoutAmount('discount', 'exclude_show')) {
                    $this->text(__("Total Discount (non-payable)"), 'down');
                }

                    //->text(__("Total Handling"), 'down')
                $this->text(__("Total Commission"), 'down')
                    ->text(__("Total Transaction Fees"), 'down')
                    ->text(__("Total Adjustments"), 'down');
            if ($this->_hlp->isStatementRefundsEnabled()) {
                $this->text(__("Total Refunds"), 'down');
            }

            $this->movePop(1.7, 0)
            ->font('bold', 12)
                ->text($totals['subtotal'], 'down')
            ->font('normal');
            if (!$this->isInPayoutAmount('tax', 'exclude_hide')) {
                $this->text($totals['tax'], 'down');
            }
            if (!$this->isInPayoutAmount('shipping', 'exclude_hide')) {
                $this->text($totals['shipping'], 'down');
            }
            if (!$this->isInPayoutAmount('discount', 'exclude_hide')) {
                $this->text('-'.@$totals['discount'], 'down');
            }
                //->text($totals['handling'], 'down')
            $this->text($totals['com_amount'], 'down')
                ->text($totals['trans_fee'], 'down')
                ->text($totals['adj_amount'], 'down');
            if ($this->_hlp->isStatementRefundsEnabled()) {
                $this->text('-'.@$totals['total_refund'], 'down');
            }
            $this->movePush()
                ->moveRel(-3.5, .1);
        $stTotalHeight = $this->getTextHeight();
        if ($hlp->isUdpayoutActive()) {
            $stTotalHeight += $this->getTextHeight()*2;
        }
        $this->font('bold', 14);
        $stTotalRectPad = $this->getTextHeight()*.4;
        $stTotalHeightOut = $stTotalHeight+$stTotalRectPad*2;
        $this->rectangle(3.6, $stTotalHeightOut, .8, .8)
            ->movePop(-1.7, .15);

        if (!$this->_hlp->isStatementAsInvoice()) {
            $this->text(__("Total Payment"))
                ->moveRel(1.7, 0)->text($totals['total_payout'], 'down')
            ;
            if ($hlp->isUdpayoutActive()) {
                $this->moveRel(-1.7)->text(__("Total Paid"))->moveRel(1.7, 0)->text($totals['total_paid'], 'down');
                $this->moveRel(-1.7)->text(__("Total Due"))->moveRel(1.7, 0)->text($totals['total_due'], 'down');
            }
        } else {
            $this->text(__("Total Invoice"))
                ->moveRel(1.7, 0)->text($totals['total_invoice'], 'down')
            ;
        }

        return $this;
    }

    public function insertTotalsPageHeader()
    {
        $hlp = $this->_hlp;
        $store = null;

        $this
            ->move(.5, .5)
            ->font('normal', 10)
            ->text($this->_hlp->getScopeConfig('udropship/admin/letterhead_info', $store));

        // letterhead logo
        $image = $this->_hlp->getScopeConfig('udropship/admin/letterhead_logo', $store);
        if ($image) {
            $image = $this->_hlp->getScopeConfig('system/filesystem/media', $store) . '/udropship/' . $image;
            $this->move(6, .5)->image($image, 2, $this->logoHeight($store));
        }

        $this->move(.5, 10.6)->text(__("Page %1", ++$this->_totalsPageNum));

        $hideAll = $hideShipping = $hideTax = $hideDiscount = true;
        foreach ($this->_globalTotals as $vId=>$line) {
        	$hideAll = $hideAll && $this->isInPayoutAmount('all', 'exclude_hide', $vId);
        	$hideTax = $hideTax && $this->isInPayoutAmount('tax', 'exclude_hide', $vId); 
        	$hideShipping = $hideShipping && $this->isInPayoutAmount('shipping', 'exclude_hide', $vId);
            $hideDiscount = $hideDiscount && $this->isInPayoutAmount('discount', 'exclude_hide', $vId);
        }
        $showAll = !$hideTax && !$hideShipping && !$hideDiscount;
        $hideCnt = 0;
        foreach (array($hideTax, $hideShipping, $hideDiscount) as $hideIdx) {
            $hideCnt += (int)$hideIdx;
        }

        $isRefundsEnabled = $this->_hlp->isStatementRefundsEnabled();
        $adjLabel = __("Adjustments");
        if ($isRefundsEnabled) {
            $adjLabel = __("Adjust");
        }

        $this->move(4.25, 1.5)
            ->font('bold', 16)->setAlign('center')->text(__('Statement Totals'))
            ->move(.5, 2)
            ->rectangle(7.5, .3, .8, .8)
            ->moveRel(.1, .1)
            ->movePush()
                ->font('bold', 8)
                ->setAlign('left')
                    ->text(__("Vendor"))
                ->setAlign('right');
            if ($hlp->isUdpayoutActive()) {

                if ($hideCnt==3) {
                    $this->moveRel(2.3, 0);
                    $moveRel = 1.1;
                } elseif ($hideCnt==2) {
                    $this->moveRel(2.1, 0);
                    $moveRel = .9;
                } elseif ($hideCnt==1) {
                    $this->moveRel(1.9, 0);
                    $moveRel = .7;
                } else {
                    $this->moveRel(1.7, 0);
                    $moveRel = .5;
                }

                $this->text(__("Product"));

                if (!$hideTax) {
                    $this->moveRel($moveRel, 0);
                    $this->text(__("Tax"));
                }
                if (!$hideShipping) {
                    $this->moveRel($moveRel, 0);
                    $this->text(__("Shipping"));
                }
                if (!$hideDiscount) {
                    $this->moveRel($moveRel, 0);
                    $this->text(__("Discount"));
                }
                $moveLbls = array(.7, .95, .8, .8);
                if ($isRefundsEnabled) {
                    $moveLbls = array(.5, .8, .7, .7);
                }
                    //->moveRel(.6, 0)->text(__("Handling"))
                $this->moveRel(.6, 0)->text(__("Comm"))
                    ->moveRel(.5, 0)->text(__("Trans"))
                    ->moveRel($moveLbls[0], 0)->text($adjLabel);
                if ($isRefundsEnabled) {
                    $this->moveRel(.5, 0)->text(__("Refund"));
                }
                    $this->moveRel($moveLbls[1], 0)->text(__("Payment"))
                    ->moveRel($moveLbls[2], 0)->text(__("Paid"))
                    ->moveRel($moveLbls[3], 0)->text(__("Due"));
            } else {

                if ($hideCnt==3) {
                    $this->moveRel(3, 0);
                    $moveRel = 1.1;
                } elseif ($hideCnt==2) {
                    $this->moveRel(2.6, 0);
                    $moveRel = .9;
                } elseif ($hideCnt==1) {
                    $this->moveRel(2.3, 0);
                    $moveRel = .7;
                } else {
                    $this->moveRel(2, 0);
                    $moveRel = .5;
                }

                $this->text(__("Product"));

                if (!$hideTax) {
                    $this->moveRel($moveRel, 0);
                    $this->text(__("Tax"));
                }
                if (!$hideShipping) {
                    $this->moveRel($moveRel, 0);
                    $this->text(__("Shipping"));
                }
                if (!$hideDiscount) {
                    $this->moveRel($moveRel, 0);
                    $this->text(__("Discount"));
                }

                    //->moveRel(.7, 0)->text(__("Handling"))
                $this->moveRel(.7, 0)->text(__("Commission"))
                    ->moveRel(.6, 0)->text(__("Trans.Fee"))
                    ->moveRel(.6, 0)->text($adjLabel);
                if ($isRefundsEnabled) {
                    $this->moveRel(.6, 0)->text(__('Refund'));
                    $this->moveRel(.8, 0);
                } else {
                    $this->moveRel(1.2, 0);
                }
                $this->text(__("Payment"));
            }
        $this->movePop(0, .3);
    }

    protected function _textWithAlphaOverlay($text, $overlay, $alpha)
    {
    	$this->text($text);
    	$tWidth = $this->getTextWidth($text);
    	$curFS = $this->getFontSize();
    	$this->getPage()->setAlpha(.7);
    	$this->fontSize($curFS*.7);
    	$this->movePush();
    	$curAlign = $this->getAlign();
    	if ($this->getAlign()=='left') {
    		$this->moveRel($tWidth*.5, 0, 'point');
    	} elseif ($this->getAlign()=='right') {
    		$this->moveRel(-$tWidth*.5, 0, 'point');
    	}
    	$this->moveRel(0, -$curFS*.7, 'point');
    	$this->setAlign('center');
		$this->text($overlay);
		$this->movePop();
		$this->getPage()->setAlpha(1);
		$this->setAlign($curAlign);
		$this->fontSize($curFS);
        return $this;
    }
    
    public function insertTotalsPage()
    {
        $hlp = $this->_hlp;

        $this->_totalsPageNum = 0;

        $this->addPage()->insertTotalsPageHeader();

        $totals = array('subtotal'=>0, 'tax'=>0, 'shipping'=>0, 'discount' => 0, 'handling'=>0, 'com_amount'=>0, 'trans_fee'=>0, 'adj_amount'=>0, 'total_payout'=>0, 'total_paid'=>0, 'total_due'=>0, 'total_refund'=>0);
        if (!$this->_hlp->isStatementAsInvoice()) {
            $totals = array_merge($totals, array('total_payout'=>0, 'total_paid'=>0, 'total_due'=>0, 'total_refund'=>0));
        } else {
            $totals = array_merge($totals, array('total_invoice'=>0, 'total_refund'=>0));
        }

        $isRefundsEnabled = $this->_hlp->isStatementRefundsEnabled();

        $hideAll = $hideShipping = $hideTax = $hideDiscount = true;
        foreach ($this->_globalTotals as $vId=>$line) {
        	$hideAll = $hideAll && $this->isInPayoutAmount('all', 'exclude_hide', $vId);
        	$hideTax = $hideTax && $this->isInPayoutAmount('tax', 'exclude_hide', $vId); 
        	$hideShipping = $hideShipping && $this->isInPayoutAmount('shipping', 'exclude_hide', $vId);
            $hideDiscount = $hideDiscount && $this->isInPayoutAmount('discount', 'exclude_hide', $vId);
        }
        $showAll = !$hideTax && !$hideShipping && !$hideDiscount;
        $hideCnt = 0;
        foreach (array($hideTax, $hideShipping, $hideDiscount) as $hideIdx) {
            $hideCnt += (int)$hideIdx;
        }
        foreach ($this->_globalTotals as $vId=>$line) {
        	$line['tax'] = $this->isInPayoutAmount('tax', 'exclude_hide', $vId) ? '' : $line['tax'];
        	$line['shipping'] = $this->isInPayoutAmount('shipping', 'exclude_hide', $vId) ? '' : $line['shipping'];
            $line['discount'] = $this->isInPayoutAmount('discount', 'exclude_hide', $vId) ? '' : @$line['discount'];
            $this->checkPageoverflow(.5, 'insertTotalsPageHeader')
                ->setMaxHeight(0)
                ->font('normal', 9)
                ->movePush()
                    ->setAlign('left')
                        ->text($line['vendor_name'], null, 30)
                    ->setAlign('right');
                if ($hlp->isUdpayoutActive() && !$this->_hlp->isStatementAsInvoice()) {
                	if ($hideCnt==3) {
                    	$this->moveRel(2.3, 0);
                        $moveRel = 1.1;
                    } elseif ($hideCnt==2) {
                        $this->moveRel(2.1, 0);
                        $moveRel = .9;
                    } elseif ($hideCnt==1) {
                        $this->moveRel(1.9, 0);
                        $moveRel = .7;
                	} else {
                		$this->moveRel(1.7, 0);
                        $moveRel = .5;
                	}
                   	$this->text($line['subtotal']);

                    if (!$hideTax) {
                        $this->moveRel($moveRel, 0);
                        if ($this->isInPayoutAmount('tax', 'exclude_show', $vId)) {
                            $this->_textWithAlphaOverlay($line['tax'], 'non-payable', .3);
                        } else {
                            $this->text($line['tax']);
                        }
                    }
                    if (!$hideShipping) {
                        $this->moveRel($moveRel, 0);
                        if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
                            $this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
                        } else {
                            $this->text($line['shipping']);
                        }
                    }
                    if (!$hideDiscount) {
                        $this->moveRel($moveRel, 0);
                        if ($this->isInPayoutAmount('discount', 'exclude_show', $vId)) {
                            $this->_textWithAlphaOverlay('-'.@$line['discount'], 'non-payable', .3);
                        } else {
                            $this->text('-'.@$line['discount']);
                        }
                    }

                    foreach (array('trans_fee','com_amount') as $_k) {
                        $line[$_k] = strpos($line[$_k], '-') === 0
                            ? substr($line[$_k], 1)
                            : '-'.$line[$_k];
                    }
                    $moveLbls = array(.7, .95, .8, .8);
                    if ($isRefundsEnabled) {
                        $moveLbls = array(.5, .8, .7, .7);
                    }
                        //->moveRel(.6, 0)->text($line['handling'])
                    $this->moveRel(.6, 0)->text($line['com_amount'])
                        ->moveRel(.5, 0)->text($line['trans_fee'])
                        ->moveRel($moveLbls[0], 0)->text($line['adj_amount']);
                    if ($isRefundsEnabled) {
                        $this->moveRel(.5, 0)->text('-'.@$line['total_refund']);
                    }
                    $this->moveRel($moveLbls[1], 0)->text($line['total_payout'])
                        ->moveRel($moveLbls[2], 0)->text($line['total_paid'])
                        ->moveRel($moveLbls[3], 0)->text($line['total_due']);
                } else {
                    if ($hideCnt==3) {
                        $this->moveRel(3, 0);
                        $moveRel = 1.1;
                    } elseif ($hideCnt==2) {
                        $this->moveRel(2.6, 0);
                        $moveRel = .9;
                    } elseif ($hideCnt==1) {
                        $this->moveRel(2.3, 0);
                        $moveRel = .7;
                    } else {
                        $this->moveRel(2, 0);
                        $moveRel = .5;
                    }
                    $this->text($line['subtotal']);
                    if (!$hideTax) {
                        $this->moveRel($moveRel, 0);
                        if ($this->isInPayoutAmount('tax', 'exclude_show', $vId)) {
                            $this->_textWithAlphaOverlay($line['tax'], 'non-payable', .3);
                        } else {
                            $this->text($line['tax']);
                        }
                    }
                    if (!$hideShipping) {
                        $this->moveRel($moveRel, 0);
                        if ($this->isInPayoutAmount('shipping', 'exclude_show', $vId)) {
                            $this->_textWithAlphaOverlay($line['shipping'], 'non-payable', .3);
                        } else {
                            $this->text($line['shipping']);
                        }
                    }
                    if (!$hideDiscount) {
                        $this->moveRel($moveRel, 0);
                        if ($this->isInPayoutAmount('discount', 'exclude_show', $vId)) {
                            $this->_textWithAlphaOverlay('-'.@$line['discount'], 'non-payable', .3);
                        } else {
                            $this->text('-'.@$line['discount']);
                        }
                    }

                    foreach (array('trans_fee','com_amount') as $_k) {
                        $line[$_k] = strpos($line[$_k], '-') === 0
                            ? substr($line[$_k], 1)
                            : '-'.$line[$_k];
                    }

                        //->moveRel(.7, 0)->text($line['handling'])
                    $this->moveRel(.7, 0)->text($line['com_amount'])
                        ->moveRel(.6, 0)->text($line['trans_fee'])
                        ->moveRel(.6, 0)->text($line['adj_amount']);
                    if ($isRefundsEnabled) {
                        $this->moveRel(.6, 0)->text('-'.@$line['total_refund']);
                        $this->moveRel(.8, 0);
                    } else {
                        $this->moveRel(1.2, 0);
                    }
                    if (!$this->_hlp->isStatementAsInvoice()) {
                        $this->text($line['total_payout']);
                    } else {
                        $this->text($line['total_invoice']);
                    }
                }
            $this->movePop(0, $this->getMaxHeight(), 'point')
                ->moveRel(-.1, 0)
                ->line(7.5, 0, .7)
                ->moveRel(.1, .1)
            ;
            foreach ($totals as $k=>&$v) {
        		if ($k == 'shipping' && $this->isInPayoutAmount('shipping', 'include', $vId)
        			|| $k == 'tax' && $this->isInPayoutAmount('tax', 'include', $vId)
                    || $k == 'discount' && $this->isInPayoutAmount('discount', 'include', $vId)
        			|| !in_array($k, array('tax','shipping','discount'))
        		) {
                	$v += @$this->_globalTotalsAmount[$vId][$k];
        		}
            }
            unset($v);
        }

        $this->checkPageOverflow(.5, 'insertTotalsPageHeader')
            ->moveRel(-.1, 0)
            ->rectangle(7.5, .05, .8, .8)
            ->moveRel(.1, .1)
            ->font('bold', 9)
                ->setAlign('left')
                    ->text(__('Grand Totals'))
                ->setAlign('right');
            if ($hlp->isUdpayoutActive() && !$this->_hlp->isStatementAsInvoice()) {
                if ($hideCnt==3) {
                    $this->moveRel(2.3, 0);
                    $moveRel = 1.1;
                } elseif ($hideCnt==2) {
                    $this->moveRel(2.1, 0);
                    $moveRel = .9;
                } elseif ($hideCnt==1) {
                    $this->moveRel(1.9, 0);
                    $moveRel = .7;
                } else {
                    $this->moveRel(1.7, 0);
                    $moveRel = .5;
                }
                $this->price($totals['subtotal']);

                if (!$hideTax) {
                    $this->moveRel($moveRel, 0);
                    $this->price($totals['tax']);
                }
                if (!$hideShipping) {
                    $this->moveRel($moveRel, 0);
                    $this->price($totals['shipping']);
                }
                if (!$hideDiscount) {
                    $this->moveRel($moveRel, 0);
                    $this->price('-'.@$totals['discount']);
                }

                foreach (array('trans_fee','com_amount') as $_k) {
                    $totals[$_k] = strpos($totals[$_k], '-') === 0
                        ? substr($totals[$_k], 1)
                        : '-'.$totals[$_k];
                }
                $moveLbls = array(.7, .95, .8, .8);
                if ($isRefundsEnabled) {
                    $moveLbls = array(.5, .8, .7, .7);
                }
                    //->moveRel(.6, 0)->price($totals['handling'])
                $this->moveRel(.6, 0)->price($totals['com_amount'])
                    ->moveRel(.5, 0)->price($totals['trans_fee'])
                    ->moveRel($moveLbls[0], 0)->price($totals['adj_amount']);
                if ($isRefundsEnabled) {
                    $this->moveRel(.5, 0)->price('-'.@$totals['total_refund']);
                }
                $this
                    ->moveRel($moveLbls[1], 0)->price($totals['total_payout'])
                    ->moveRel($moveLbls[2], 0)->price($totals['total_paid'])
                    ->moveRel($moveLbls[3], 0)->price($totals['total_due']);
            } else {
                    if ($hideCnt==3) {
                        $this->moveRel(3, 0);
                        $moveRel = 1.1;
                    } elseif ($hideCnt==2) {
                        $this->moveRel(2.6, 0);
                        $moveRel = .9;
                    } elseif ($hideCnt==1) {
                        $this->moveRel(2.3, 0);
                        $moveRel = .7;
                    } else {
                        $this->moveRel(2, 0);
                        $moveRel = .5;
                    }
                    $this->price($totals['subtotal']);

                    if (!$hideTax) {
                        $this->moveRel($moveRel, 0);
                        $this->price($totals['tax']);
                    }
                    if (!$hideShipping) {
                        $this->moveRel($moveRel, 0);
                        $this->price($totals['shipping']);
                    }
                    if (!$hideDiscount) {
                        $this->moveRel($moveRel, 0);
                        $this->price('-'.@$totals['discount']);
                    }


                foreach (array('trans_fee','com_amount') as $_k) {
                    $totals[$_k] = strpos($totals[$_k], '-') === 0
                        ? substr($totals[$_k], 1)
                        : '-'.$totals[$_k];
                }
                    //->moveRel(.7, 0)->price($totals['handling'])
                $this->moveRel(.7, 0)->price($totals['com_amount'])
                    ->moveRel(.6, 0)->price($totals['trans_fee'])
                    ->moveRel(.6, 0)->price($totals['adj_amount']);
                if ($isRefundsEnabled) {
                    $this->moveRel(.6, 0)->text('-'.@$totals['total_refund']);
                    $this->moveRel(.8, 0);
                } else {
                    $this->moveRel(1.2, 0);
                }
                if (!$this->_hlp->isStatementAsInvoice()) {
                    $this->price($totals['total_payout']);
                } else {
                    $this->price($totals['total_invoice']);
                }
            }

        return $this;
    }
    
    public function insertAdjustmentsPage($params=array())
    {
        $hlp = $this->_hlp;

        $this->_totalsPageNum = 0;
        
        if (!$this->getStatement()->getExtraAdjustments()) return $this;

        $this->addPage()->insertAdjustmentsPageHeader($params);

        foreach ($this->getStatement()->getExtraAdjustments() as $line) {
            foreach (array('adjustment_id','po_id','amount','comment','username','po_type','created_at') as $_k) {
                if (!isset($line[$_k])) $line[$_k] = '';
            }
            $this->checkPageoverflow(.5, 'insertAdjustmentsPageHeader')
                ->setMaxHeight(0)
                ->font('normal', 9)
                ->movePush()
                    ->setAlign('left')
                    ->text($line['adjustment_id'])
                    ->moveRel(1.2, 0)->text($line['po_id'])
                    ->moveRel(.7, 0)->text($line['po_type'])
                    ->setAlign('right')
                    ->moveRel(1.6, 0)->price($line['amount'])
                    ->setAlign('left')
                    ->moveRel(0.1, 0)->text($line['username'])
                    ->moveRel(.8, 0)->text($line['comment'], null, 50)
                    ->moveRel(2.5, 0)->text($this->_hlp->formatDate($line['created_at'], \IntlDateFormatter::SHORT));
            $this->movePop(0, $this->getMaxHeight(), 'point')
                ->moveRel(-.1, 0)
                ->line(7.5, 0, .7)
                ->moveRel(.1, .1)
            ;
            unset($v);
        }

        return $this;
    }
    
    public function insertPayoutsPage($params=array())
    {
        $hlp = $this->_hlp;

        $this->_totalsPageNum = 0;
        
        if (!$this->getStatement()->getPayouts()) return $this;

        $this->addPage()->insertPayoutsPageHeader($params);

        foreach ($this->getStatement()->getPayouts() as $line) {
            foreach (array('payout_id','payout_type','payout_method','payout_status','total_orders','total_payout','total_paid','total_due') as $_k) {
                if (!isset($line[$_k])) $line[$_k] = '';
            }
            $this->checkPageoverflow(.5, 'insertPayoutsPageHeader')
                ->setMaxHeight(0)
                ->font('normal', 9)
                ->movePush()
                    ->setAlign('left')
                    ->text($line['payout_id'])
                    ->moveRel(.8, 0)->text($line['payout_type'])
                    ->moveRel(.8, 0)->text($line['payout_method'])
                    ->moveRel(.8, 0)->text($line['payout_status'])
                    ->moveRel(.6, 0)->text($line['total_orders'])
                    ->setAlign('right')
                    ->moveRel(1.5, 0)->price($line['total_payout'])
                    ->moveRel(.8, 0)->price($line['total_paid'])
                    ->moveRel(.8, 0)->price($line['total_due'])
                    ->moveRel(.8, 0)->text($this->_hlp->formatDate($line['created_at'], \IntlDateFormatter::SHORT));
            $this->movePop(0, $this->getMaxHeight(), 'point')
                ->moveRel(-.1, 0)
                ->line(7.5, 0, .7)
                ->moveRel(.1, .1)
            ;
            unset($v);
        }

        return $this;
    }

}
