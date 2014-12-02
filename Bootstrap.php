<?php
/**
 * Created by PhpStorm.
 * User: costa
 * Date: 02.12.14
 * Time: 12:51
 */

namespace rico2\yii2images;
use yii\base\BootstrapInterface;
use yii\web\GroupUrlRule;

class Bootstrap implements BootstrapInterface{
    use ModuleTrait;
    public function bootstrap($app){
        $configUrlRule = [
            'prefix' => $this->getModule()->urlPrefix,
            'rules'  => $this->getModule()->urlRules
        ];
        $app->get('urlManager')->rules[] = new GroupUrlRule($configUrlRule);
    }
}