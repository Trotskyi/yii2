<?php

namespace app\controllers;

use app\models\Article;
use app\models\Category;
use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\models\User;
use app\models\forms\LoginForm;
use app\models\forms\PasswordResetRequestForm;
use app\models\forms\ResetPasswordForm;
use yii\data\Pagination;

class AuthController extends Controller
{
    /**
     * User login
     */
    public function actionLogin()
    {
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            return $this->render('login', [
                'model' => $model,
            ]);
        }
    }

    /**
     * User logout
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionTest()
    {
        $user = User::findOne(1);

        Yii::$app->user->login($user);

        if (Yii::$app->user->isGuest) {
            echo 'Гость';
        } else {

          echo  'Адмін';
        }
    }

}
