<?php
namespace Paymentnetwork\Pnsofortueberweisung\Block\Adminhtml\Payment\Config\Label;

class Red extends \Paymentnetwork\Pnsofortueberweisung\Block\Adminhtml\Payment\Config\Label
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><div id="%s" style="color:red;">%s</div></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), '<div>' . $element->getLabel() . '</div>'
        );
    }
}
