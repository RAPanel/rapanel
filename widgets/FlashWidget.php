<?php
/**
 * Created by ReRe-Design.
 * User: Semyonchick
 * MailTo: webmaster@rere-design.ru
 */

class FlashWidget extends CWidget
{
    public function run()
    {
        if (count(Yii::app()->user->getFlashes(false))):
            echo CHtml::openTag('section', array('class' => 'alert-block'));
            foreach (Yii::app()->user->getFlashes() as $class => $flash)
                echo CHtml::tag('div', array('class' => "alert alert-{$class}"), $flash);
            echo CHtml::closeTag('section');
        endif;
    }
} 