<?php
namespace Paymentnetwork\Pnsofortueberweisung\Controller\Payment;

use Paymentnetwork\Pnsofortueberweisung\Controller\PaymentAbstract;

class Notification extends PaymentAbstract
{
    public function execute() 
    {
        $this->_notificationAction();
    }
}