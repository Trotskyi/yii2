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

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * Render the homepage
     */
    public function actionIndex()
    {

        $data = Article::getAll(1);
        $popular = Article::getPopular();
        $recent = Article::getRecent();
        $categories = Category::getAll();

        return $this->render('index', [
            'articles'   => $data['articles'],
            'pagination' => $data['pagination'],
            'popular'    => $popular,
            'recent'     => $recent,
            'categories' => $categories
        ]);
    }

    /**
     * User signup
     */
    public function actionSignup()
    {
        $user = new User(['scenario' => 'signup']);
        if ($user->load(Yii::$app->request->post()) && $user->save()) {
            Yii::$app->session->setFlash('user-signed-up');
            return $this->refresh();
        }

        if (Yii::$app->session->hasFlash('user-signed-up')) {
            return $this->render('signedUp');
        }

        return $this->render('signup', [
            'model' => $user,
        ]);
    }

    /**
     * Confirm email
     */
    public function actionConfirmEmail($token)
    {
        $user = User::find()->emailConfirmationToken($token)->one();

        if ($user !== null && $user->confirmEmail()) {
            Yii::$app->getUser()->login($user);
            return $this->goHome();
        }

        return $this->render('emailConfirmationFailed');
    }

    /**
     * Request password reset
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->getSession()->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Reset password
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->getSession()->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }


    public function actionView($id)
    {
        $article= Article::findOne($id);
        $popular = Article::getPopular();
        $recent = Article::getRecent();
        $categories = Category::getAll();

        return $this->render('single', [
            'article' => $article,
            'popular'    => $popular,
            'recent'     => $recent,
            'categories' => $categories
        ]);
    }

    public function actionCategory($id)
    {

        $data = Category::getArticlesByCategory($id);
        $popular = Article::getPopular();
        $recent = Article::getRecent();
        $categories = Category::getAll();


        return $this->render('category', [
            'articles' => $data['articles'],
            'pagination' => $data['pagination'],
            'popular'    => $popular,
            'recent'     => $recent,
            'categories' => $categories
        ]);
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
}
