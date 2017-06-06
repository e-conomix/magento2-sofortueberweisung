<?php

namespace Paymentnetwork\Pnsofortueberweisung\Block\Adminhtml\Payment\Config;

class Label extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5" style="padding-top: 5px; padding-bottom: 5px;"><div id="%s">%s</div></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
