<?php


/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property string $filePath
 * @property integer $itemId
 * @property integer $isMain
 * @property string $modelName
 * @property string $urlAlias
 */

namespace rico2\yii2images\models;

use Yii;
use yii\base\Exception;
use yii\helpers\Url;
use yii\helpers\BaseFileHelper;
use \rico2\yii2images\ModuleTrait;



abstract class ImageAbstract extends \yii\db\ActiveRecord implements ImageInterface
{
    use ModuleTrait;

    public function clearCache(){
        $subDir = $this->getSubDur();

        $dirToRemove = $this->getModule()->getCachePath().DIRECTORY_SEPARATOR.$subDir;

        if(preg_match('/'.preg_quote($this->modelName, '/').DIRECTORY_SEPARATOR, $dirToRemove)){
            BaseFileHelper::removeDirectory($dirToRemove);

        }

        return true;
    }

    public function setAsMain($isMain = true){
        $this->isMain = 1;
        $this->save();
    }
    
    public function getExtension(){
        $ext = pathinfo($this->getPathToOrigin(), PATHINFO_EXTENSION);
        return $ext;
    }

    public function getUrl($size = false){
        $urlSize = ($size) ? '_'.$size : '';
        $url = Url::toRoute([
            '/'.$this->getModule()->id.'/images/image-by-item-and-alias',
            'item' => $this->modelName.$this->itemId,
            'dirtyAlias' =>  $this->urlAlias.$urlSize.'.'.$this->getExtension()
        ]);

        return $url;
    }

    public function getPath($size = false){
        $urlSize = ($size) ? '_'.$size : '';
        $base = $this->getModule()->getCachePath();
        $sub = $this->getSubDur();

        $origin = $this->getPathToOrigin();

        $filePath = $base.DIRECTORY_SEPARATOR.
            $sub.DIRECTORY_SEPARATOR.$this->urlAlias.$urlSize.'.'.pathinfo($origin, PATHINFO_EXTENSION);;
        if(!file_exists($filePath)){
            $this->createVersion($origin, $size);

            if(!file_exists($filePath)){
                throw new \Exception('Problem with image creating.');
            }
        }

        return $filePath;
    }

    public function getContent($size = false){
        return file_get_contents($this->getPath($size));
    }

    public function getPathToOrigin(){

        $base = $this->getModule()->getStorePath();

        $filePath = $base.DIRECTORY_SEPARATOR.$this->filePath;

        return $filePath;
    }


    abstract public function getSizes();

    public function getSizesWhen($sizeString){

        $size = $this->getModule()->parseSize($sizeString);
        if(!$size){
            throw new \Exception('Bad size..');
        }



        $sizes = $this->getSizes();

        $imageWidth = $sizes['width'];
        $imageHeight = $sizes['height'];
        $newSizes = [];
        if(!$size['width']){
            $newWidth = $imageWidth*($size['height']/$imageHeight);
            $newSizes['width'] = intval($newWidth);
            $newSizes['heigth'] = $size['height'];
        }elseif(!$size['height']){
            $newHeight = intval($imageHeight*($size['width']/$imageWidth));
            $newSizes['width'] = $size['width'];
            $newSizes['heigth'] = $newHeight;
        }

        return $newSizes;
    }

    abstract public function createVersion($imagePath, $sizeString = false);

    protected function getSubDur(){
        return $this->modelName. 's/' . $this->modelName.$this->itemId;
    }
        
    /** ----========= GENERATED =========-----*/
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['filePath', 'itemId', 'modelName', 'urlAlias'], 'required'],
            [['itemId', 'isMain'], 'integer'],
            [['filePath', 'urlAlias'], 'string', 'max' => 400],
            [['modelName'], 'string', 'max' => 150]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'filePath' => 'File Path',
            'itemId' => 'Item ID',
            'isMain' => 'Is Main',
            'modelName' => 'Model Name',
            'urlAlias' => 'Url Alias',
        ];
    }
}
