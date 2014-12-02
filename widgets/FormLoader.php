<?php

/*
 *
 * 1. set file property for model
	=========================
	class Item extends \yii\db\ActiveRecord
	{

    public $file;
    ...

    ==========================

2. in your form view be sure, that attr enctype equile "multipart/form-data"
	=========================

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

	=========================

3. in your form view insert widget

	==============================
	<?=rico2\yii2images\widgets\FormLoader::widget([
        'model'=>$model,
        'form'=>$form,
    ])?>
	==============================

4. in your controller after item save and validate insert
	=====================================
	$files = \yii\web\UploadedFile::getInstances($model, 'file');
            //images
            foreach ($files as $attach) {
                $fileWithExt = $attach->tempName.'.'.$attach->getExtension();
                rename($attach->tempName, $fileWithExt);
                $model->attachImage($fileWithExt);
            }
	=====================================
 *
 * */

/**
 * Created by PhpStorm.
 * User: costa
 * Date: 06.11.14
 * Time: 14:59
 */
namespace rico2\yii2images\widgets;
use rico2\yii2images\ModuleTrait;
use yii\base\Widget;
use yii\helpers\Html;


class FormLoader extends Widget {

    use ModuleTrait;
    public $model;
    public $urlRemoveImage;
    public $urlSetMainImage;
    public $form;

    public function run()
    {

        return $this->render('formLoader',
            [
                'model'=>$this->model,
                'form'=>$this->form,
                'urls' => [
                    'removeImage' => $this->urlRemoveImage,
                    'setMain' => $this->urlSetMainImage
                ]
            ]);
    }

    public function init()
    {
        $this->urlRemoveImage = $this->getModule()->removeImageUrl;
        $this->urlSetMainImage = $this->getModule()->setMainImageUrl;
    }

}