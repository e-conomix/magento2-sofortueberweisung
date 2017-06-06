<?php
namespace Paymentnetwork\Pnsofortueberweisung\Controller\Payment;

use Paymentnetwork\Pnsofortueberweisung\Controller\PaymentAbstract;

class Abort extends PaymentAbstract
{
    public function execute() 
    {
        $this->_abortAction();
    }
}