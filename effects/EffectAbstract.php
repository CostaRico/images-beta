<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 04.12.14
 * Time: 18:47
 */

namespace rico2\yii2images\effects;


class EffectAbstract {
    public $isEnabled = true;

    public function disable()
    {
        $this->isEnabled = false;
    }


}