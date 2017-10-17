<?php
require_once(Mage::getBaseDir('lib') . DS .'Magenifest' . DS . 'Countdown' . DS .'GIFEncoder.class.php');
/**
 * @author William Tran
 */
class Magenifest_Countdownwidget_Model_Timer {

    const WIDGET_TYPE_TIMER  = 'magenifest_countdownwidget/timer';
    const TIMER_CACHE_KEY_PREFIX = 'magenifest_timer_';

    protected $_widgetsCollection = null;

    /**
     * @param $code
     * @return bool|Mage_Widget_Model_Widget_Instance|mixed
     */
    public function getTimerWidgetDataByCode($code) {
        $widgets = $this->_getTimerWidgetCollection();
        foreach ($widgets as $widget) {
            $params = $widget->getWidgetParameters();
            if ($params['code'] == $code) {
                return $widget->getWidgetParameters();
            }
        }
        return false;
    }

    /**
     * @return Mage_Widget_Model_Resource_Widget_Instance_Collection
     */
    protected function _getTimerWidgetCollection() {
        if (!$this->_widgetsCollection) {
            $this->_widgetsCollection = Mage::getModel('widget/widget_instance')->getCollection();
            $this->_widgetsCollection->addFieldToFilter('instance_type', array('eq' => self::WIDGET_TYPE_TIMER));
        }
        return $this->_widgetsCollection;
    }

    /**
     * @param $code
     * @return bool|mixed|type
     */
    public function getTimerGifByCode($code) {
        $timerCacheKey = $this->_getTimerCacheKey($code);
        $gifData = Mage::app()->loadCache($timerCacheKey);
        if ($gifData) {
            return $gifData;
        }
        $timerWidgetData = $this->getTimerWidgetDataByCode($code);
        if (!$timerWidgetData) {
            return false;
        }
        try {
            $gifData = $this->_generateTimerGif($timerWidgetData);
            Mage::app()->saveCache($gifData, $timerCacheKey, array('magenifest_timer_cache'), 60);
            return $gifData;
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return false;
    }

    /**
     * @param $code
     * @return string
     */
    protected function _getTimerCacheKey($code) {
        $code = Mage::helper('core')->removeAccents($code);
        $code = preg_replace('#[^0-9a-z]+#i', '-', $code);
        $code = strtolower($code);
        $code = trim($code, '-');
        return self::TIMER_CACHE_KEY_PREFIX . $code;
    }

    /**
     * @param $timerWidgetData
     * @return type
     */
    protected function _generateTimerGif($timerWidgetData) {
        $time = $timerWidgetData['end_date'];
        $future_date = new DateTime(Mage::app()->getLocale()->date($time)->toString('r'));
        $time_now = time();
        $now = new DateTime(date('r', $time_now));
        $frames = array();
        $delays = array();

        $background = Mage::getBaseDir('media') . DS . $timerWidgetData['background_image'];
        $offsetX = $timerWidgetData['x_offset'];

        // Your image link
        $image = imagecreatefrompng($background);

        $delay = 100;// milliseconds

        $font = array(
            'size' => $timerWidgetData['font_size'], // Font size, in pts usually.
            'angle' => 0, // Angle of the text
            'x-offset' => $offsetX, // The larger the number the further the distance from the left hand side, 0 to align to the left.
            'y-offset' => $timerWidgetData['y_offset'], // The vertical alignment, trial and error between 20 and 60.
            'file' => Mage::getBaseDir('media') .  DS . $timerWidgetData['font_path'], // Font path
            'color' => imagecolorallocate($image, $timerWidgetData['color_r'], $timerWidgetData['color_g'], $timerWidgetData['color_b']), // RGB Colour of the text
        );
        for($i = 0; $i <= 60; $i++){

            $interval = date_diff($future_date, $now);

            if($future_date < $now){
                // Open the first source image and add the text.
                $image = imagecreatefrompng($background);
                $text = $interval->format('00');
                $this->imagettftextSp($image, $font['size'], $font['angle'], $font['x-offset'], $font['y-offset'], $font['color'], $font['file'], $text, 45);
                $text = $interval->format('00');
                $this->imagettftextSp($image, $font['size'], $font['angle'], $font['x-offset']+230, $font['y-offset'], $font['color'], $font['file'], $text, 40);
                $text = $interval->format('00');
                $this->imagettftextSp($image, $font['size'], $font['angle'], $font['x-offset']+459, $font['y-offset'], $font['color'], $font['file'], $text, 40);
                ob_start();
                imagegif($image);
                $frames[]=ob_get_contents();
                $delays[]=$delay;
                $loops = 1;
                ob_end_clean();
                break;
            } else {
                // Open the first source image and add the text.
                $image = imagecreatefrompng($background);
                $hours = (string)$interval->format('%H');
                $this->imagettftextSp($image, $font['size'], $font['angle'], $font['x-offset'], $font['y-offset'], $font['color'], $font['file'], $hours, 45);
                $text = $interval->format('%I');
                $this->imagettftextSp($image, $font['size'], $font['angle'], $font['x-offset']+230, $font['y-offset'], $font['color'], $font['file'], $text, 40);
                $text = $interval->format('%S');
                $this->imagettftextSp($image, $font['size'], $font['angle'], $font['x-offset']+459, $font['y-offset'], $font['color'], $font['file'], $text, 40);
                ob_start();
                imagegif($image);
                $frames[]=ob_get_contents();
                $delays[]=$delay;
                $loops = 0;
                ob_end_clean();
            }

            $now->modify('+1 second');
        }
        $gif = new AnimatedGif($frames,$delays,$loops);
        return $gif->getAnimation();
    }


    /**
     * @param $image
     * @param $size
     * @param $angle
     * @param $x
     * @param $y
     * @param $color
     * @param $font
     * @param $text
     * @param int $spacing
     */
    protected function imagettftextSp($image, $size, $angle, $x, $y, $color, $font, $text, $spacing = 0)
    {
        if ($spacing == 0) {
            imagettftext($image, $size, $angle, $x, $y, $color, $font, $text);
        } else {
            $temp_x = $x;
            for ($i = 0; $i < strlen($text); $i++) {
                $bbox   = imagettftext($image, $size, $angle, $temp_x, $y, $color, $font, $text[$i]);
                $temp_x += $spacing + ($bbox[2] - $bbox[0]);
            }
        }
    }
}