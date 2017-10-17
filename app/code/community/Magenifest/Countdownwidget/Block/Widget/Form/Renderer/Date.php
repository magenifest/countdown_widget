<?php
/**
 * @author William Tran
 */

class Magenifest_Countdownwidget_Block_Widget_Form_Renderer_Date extends Mage_Adminhtml_Block_Widget_Form_Renderer_Fieldset_Element {

    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element) {
        $dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $dateHtml = new Varien_Data_Form_Element_Date(
            array('name' =>
                'end_date',
                'html_id'   =>  $element->getHtmlId(),
                'label' => Mage::helper('core')->__('End Date'),
                'name'  => $element->getName(),
                'title' => Mage::helper('core')->__('End Date'),
                'image' => $this->getSkinUrl('images/grid-cal.gif'),
                'input_format' => $dateFormatIso,
                'format' => $dateFormatIso, 'time' => true)
        );
        $dateHtml->setValue($element->getEscapedValue());
        $dateHtml->setForm($element->getForm());
        $this->_element = $dateHtml;
        return $this->toHtml();
    }

}