<?php
namespace Paymentnetwork\Pnsofortueberweisung\Controller\Payment;

use Paymentnetwork\Pnsofortueberweisung\Controller\PaymentAbstract;

class Redirect extends PaymentAbstract
{
    public function execute() 
    {
        $this->_redirectAction();
    }
}