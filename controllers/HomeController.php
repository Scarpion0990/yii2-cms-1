<?php

namespace afzalroq\cms\controllers;

use Yii;
use afzalroq\cms\entities\Collections;
use afzalroq\cms\entities\Entities;
use afzalroq\cms\entities\Pages;
use afzalroq\cms\forms\PagesSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class HomeController extends Controller
{
    public function actionIndex()
    {
        $collectionProvider = new ActiveDataProvider(['query' => Collections::find()]);
        $entityProvider = new ActiveDataProvider(['query' => Entities::find()]);

        return $this->render('index', [
            'collectionProvider' => $collectionProvider,
            'entityProvider' => $entityProvider
        ]);
    }
}