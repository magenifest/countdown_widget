<?php
class Magenifest_Countdownwidget_TimerController extends Mage_Core_Controller_Front_Action {

    /**
     * Access using /countdownwidget/timer/show
     * Make sure that request is not cached
     */
    public function showAction() {
        $widgetCode = Mage::helper('core')->escapeHtml($this->getRequest()->getParam('code'));
        $gif = Mage::getSingleton('magenifest_countdownwidget/timer')->getTimerGifByCode($widgetCode);
        if ($gif) {
            $this->getResponse()->setHeader('Content-type', 'image/gif');
            $this->getResponse()->setBody($gif);
        } else {
            $this->getResponse()->setHeader('Content-type', 'text/plain');
            $this->getResponse()->setBody('Not available');
        }
    }

}