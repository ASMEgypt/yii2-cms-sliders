<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 22.06.14
 * Time: 16:58
 */

namespace infoweb\sliders\behaviors;

use infoweb\sliders\models\Image;

use yii;
use yii\helpers\BaseFileHelper;
use yii\db\Query;

class ImageBehave extends \rico\yii2images\behaviors\ImageBehave
{
    /**
     * Method copies image file to module store and creates db record.
     *
     * @param $absolutePath
     * @param bool $isFirst
     * @return bool|Image
     * @throws \Exception
     */
    public function attachImage($absolutePath, $isMain = false)
    {
        if(!preg_match('#http#', $absolutePath)){
            if (!file_exists($absolutePath)) {
                throw new \Exception(Yii::t('infoweb/sliders', 'File {path} does not exist!', ['path' => $absolutePath]));
            }
        }

        if (!$this->owner->id) {
            throw new \Exception(Yii::t('infoweb/sliders', 'There was a problem while attaching the image'));
        }

        // Custom
        $pictureFileName = basename($absolutePath);

        $pictureSubDir = $this->getModule()->getModelSubDir($this->owner);
        $storePath = $this->getModule()->getStorePath($this->owner);

        $newAbsolutePath = $storePath .
            DIRECTORY_SEPARATOR . $pictureSubDir .
            DIRECTORY_SEPARATOR . $pictureFileName;

        BaseFileHelper::createDirectory($storePath . DIRECTORY_SEPARATOR . $pictureSubDir,
            0775, true);

        copy($absolutePath, $newAbsolutePath);

        if (!file_exists($absolutePath)) {
            throw new \Exception(Yii::t('infoweb/sliders', 'There was a problem while uploading the file'));
        }

        // Custom
        unlink($absolutePath);

        if($this->modelClass === null) {
            $image = new Image;
        }else{
            $image = new ${$this->modelClass}();
        }
        $image->itemId = $this->owner->id;
        $image->filePath = $pictureSubDir . '/' . $pictureFileName;
        $modelName = $this->getModule()->getShortClass($this->owner);
        $image->modelName = $modelName;

        $image->urlAlias = $this->getAlias($image);

        // Custom
        $nameWithoutExt = preg_replace('/\\.[^.\\s]{3,4}$/', '', $pictureFileName);
        $image->name = $nameWithoutExt;
        // Get the highest position
        // @todo Create function
        $query = (new Query)->select('MAX(`position`)')->from(Image::tableName())->where(['modelName' => $modelName]);
        $command = $query->createCommand();
        $image->position = $command->queryOne(\PDO::FETCH_COLUMN)+1;

        if(!$image->save()){
            return false;
        }

        if (count($image->getErrors()) > 0) {

            $ar = array_shift($image->getErrors());

            unlink($newAbsolutePath);
            throw new \Exception(array_shift($ar));
        }
        $img = $this->owner->getImage();

        //If main image not exists
        if(
            is_object($img) && get_class($img)=='rico\yii2images\models\PlaceHolder'
            or
            $img == null
            or
            $isMain
        ){
            $this->setMainImage($image);
        }

        return $image;
    }

    /** Make string part of image's url
     * @return string
     * @throws \Exception
     */
    private function getAliasString()
    {
        if ($this->createAliasMethod) {
            $string = $this->owner->{$this->createAliasMethod}();
            if (!is_string($string)) {
                throw new \Exception(Yii::t('infoweb/sliders', 'Invalid image alias'));
            } else {
                return $string;
            }

        } else {
            return substr(md5(microtime()), 0, 10);
        }
    }

    /**
     *
     * Обновить алиасы для картинок
     * Зачистить кэш
     */
    private function getAlias()
    {
        $imagesCount = count($this->owner->getImages());

        return $this->getImage()->name . '-' . intval($imagesCount + 1);
    }

    /**
     * Returns model images
     * First image alwats must be main image
     * @return array|yii\db\ActiveRecord[]
     */
    public function getImages()
    {
        $finder = $this->getImagesFinder();

        $imageQuery = Image::find()
            ->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $imageRecords = $imageQuery->all();
        if(!$imageRecords){
            return [$this->getModule()->getPlaceHolder()];
        }
        return $imageRecords;
    }

    /**
     * Remove all model images
     */
    public function removeImages()
    {
        $images = $this->owner->getImages();
        if (count($images) < 1) {
            return true;
        } else {
            foreach ($images as $image) {
                $this->owner->removeImage($image);
            }
        }
    }

    /**
     * returns main model image
     * @return array|null|ActiveRecord
     */
    public function getImage()
    {
        $finder = $this->getImagesFinder(['isMain' => 1]);
        $imageQuery = Image::find()
            ->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $img = $imageQuery->one();
        if(!$img){
            return $this->getModule()->getPlaceHolder();
        }

        return $img;
    }

    private function getImagesFinder($additionWhere = false)
    {
        $base = [
            'itemId' => $this->owner->id,
            'modelName' => $this->getModule()->getShortClass($this->owner)
        ];

        if ($additionWhere) {
            $base = \yii\helpers\BaseArrayHelper::merge($base, $additionWhere);
        }

        return $base;
    }

    /**
     * Returns model images
     * First image alwats must be main image
     * @return array|yii\db\ActiveRecord[]
     */
    public function clearCache()
    {
        $finder = $this->getImagesFinder();

        $imageQuery = Image::find()
            ->where($finder);
        $imageQuery->orderBy(['isMain' => SORT_DESC, 'id' => SORT_ASC]);

        $imageRecords = $imageQuery->all();
        if(!$imageRecords){
            return [$this->getModule()->getPlaceHolder()];
        }
        return $imageRecords;
    }

    /**
     *
     * removes concrete model's image
     * @param Image $img
     * @throws \Exception
     */
    public function removeImage($img)
    {
        $img = new Image;
        $img->clearCache();

        $storePath = $this->getModule()->getStorePath();

        $fileToRemove = $storePath . DIRECTORY_SEPARATOR . $img->filePath;
        if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
            unlink($fileToRemove);
        }
        $img->delete();
    }


}