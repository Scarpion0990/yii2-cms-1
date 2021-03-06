<?php

use afzalroq\cms\entities\Entities;
use afzalroq\cms\widgets\CmsForm;
use kartik\widgets\FileInput;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model afzalroq\cms\entities\Items */
/* @var $form yii\widgets\ActiveForm */
/* @var $entity Entities */

$hasTranslatableAttrs = 0;
?>
<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div style="margin:5px 0 0 0;" class="alert alert-success"><?= Yii::$app->session->getFlash('success') ?></div>
<?php endif; ?>

<div class="items-form">
    <?php
    $form = ActiveForm::begin([
        'fieldConfig' => [
            'options' => [
                'enableClientValidation' => false,
                'enableAjaxValidation' => true
            ]
        ]
    ]);

    $cmsForm = new CmsForm($form, $model, $entity)
    ?>

    <?= $form->errorSummary($model) ?>
    <?= $form->field($model, 'entity_id')->textInput(['value' => $entity->id, 'type' => 'hidden'])->label(false) ?>

    <div class="box">
        <div class="box-body">
            <div class="row">
                <?php if ($entity->manual_slug): ?>
                    <div class="col-sm-6">
                        <?= $form->field($model, 'slug')->textInput() ?>
                    </div>
                <?php endif; ?>
                <?= $cmsForm->oaIFields(); ?>
            </div>
        </div>
    </div>

    <!--#region Common -->
    <div class="box">
        <div class="box-body">
            <div class="row">
                <?= $cmsForm->dateField('date') ?>

                <?= $cmsForm->textFieldsCommon() ?>
                <?= $cmsForm->fileFieldsCommon() ?>
            </div>
        </div>
    </div>
    <!--#endregion -->

    <!--#region Translatable -->
    <div class="row" id="translatable">
        <div class="col-md-12">
            <hr>
            <div class="box">
                <div class="box-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <?php foreach (Yii::$app->params['cms']['languages'] as $key => $language) : ?>
                            <li role="presentation" <?= $key == 0 ? 'class="active"' : '' ?>>
                                <a href="#<?= $key ?>" aria-controls="<?= $key ?>" role="tab"
                                   data-toggle="tab"><?= $language ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="tab-content">
                        <br>
                        <?php foreach (Yii::$app->params['cms']['languages'] as $key => $language) : ?>
                            <div role="tabpanel" class="tab-pane <?= $key == 0 ? 'active' : '' ?>" id="<?= $key ?>">

                                <?= $cmsForm->textFieldsTranslatable($key, $hasTranslatableAttrs) ?>
                                <?= $cmsForm->fileFieldsTranslatable($key, $hasTranslatableAttrs) ?>

                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--#endregion -->

    <!--#region Translatable Seo -->
    <?php if ($entity->use_seo > 0): ?>
        <div class="row">
            <div class="col-md-12">
                <hr>
                <div class="box">
                    <div class="box-body">
                        <?php if ($entity->use_seo == Entities::SEO_TRANSLATABLE): ?>
                            <ul class="nav nav-tabs" role="tablist">
                                <?php foreach (Yii::$app->params['cms']['languages'] as $key => $language) : ?>
                                    <li role="presentation" <?= $key == 0 ? 'class="active"' : '' ?>>
                                        <a href="#<?= $key ?>S" aria-controls="<?= $key ?>S" role="tab"
                                           data-toggle="tab"><?= $language ?></a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            <div class="tab-content">
                                <br>
                                <?php foreach (Yii::$app->params['cms']['languages'] as $key => $language) : ?>
                                    <div role="tabpanel" class="tab-pane <?= $key == 0 ? 'active' : '' ?>" id="<?= $key ?>S">

                                        <?= $form->field($model, 'meta_title_' . $key)->textInput() ?>
                                        <?= $form->field($model, 'meta_keyword_' . $key)->textarea() ?>
                                        <?= $form->field($model, 'meta_des_' . $key)->textarea() ?>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <?= $form->field($model, 'meta_title_0')->textInput() ?>
                            <?= $form->field($model, 'meta_keyword_0')->textarea() ?>
                            <?= $form->field($model, 'meta_des_0')->textarea() ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <!--#endregion -->

    <?php if ($entity->use_gallery): ?>
        <div class="box box-default">
            <div class="box-body">
                <?= $form->field($model, 'files[]')->widget(FileInput::class, [
                    'options' => [
                        'accept' => 'image/*',
                        'multiple' => true,
                    ]
                ]) ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('cms', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>


    <script>
        if (!<?= $hasTranslatableAttrs ?>)
            document.querySelector('#translatable').innerHTML = ''
    </script>
</div>
