<?php
namespace Paymentnetwork\Pnsofortueberweisung\Block\Adminhtml\Payment\Config\Heading;

class Big extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $useContainerId = $element->getData('use_container_id');
        return sprintf('<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h2 id="%s">%s</h2></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
    }
}
