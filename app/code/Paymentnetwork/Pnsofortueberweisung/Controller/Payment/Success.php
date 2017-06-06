<?php
namespace Paymentnetwork\Pnsofortueberweisung\Controller\Payment;

use Paymentnetwork\Pnsofortueberweisung\Controller\PaymentAbstract;

class Success extends PaymentAbstract
{
    public function execute() 
    {                
        $this->_successAction();
    }
}