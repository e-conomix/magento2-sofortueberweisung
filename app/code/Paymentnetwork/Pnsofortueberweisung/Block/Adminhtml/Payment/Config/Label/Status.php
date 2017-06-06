<?php
namespace Paymentnetwork\Pnsofortueberweisung\Block\Adminhtml\Payment\Config\Label;

class Status extends \Paymentnetwork\Pnsofortueberweisung\Block\Adminhtml\Payment\Config\Label
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $label = sprintf($element->getLabel(), '"' . __("Not update status")) . '"';
        
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><div id="%s">%s</div></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $label
        );
    }
}
