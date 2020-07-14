<?php
namespace nogn\craftgridsome\controllers;

use Craft;

use craft\helpers\ArrayHelper;
use craft\helpers\UrlHelper;
use nogn\craftgridsome\CraftGridsome;
use nogn\craftgridsome\Site;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Sites Controller
 *
 * @author Ben Sheedy
 * @since 1.0
 */
class SitesController extends BaseController
{
    /**
     * Shows the craft-gridsome index page
     *
     * @return Response
     */
    public function actionIndex(): Response
    {
        $manager = CraftGridsome::getInstance()->getSiteManager();

        return $this->renderTemplate('craft-gridsome/_sites/index', [
            'sites' => $manager->getAllSites(),
        ]);
    }

    /**
     * Shows the edit page for a site.
     *
     * @param int|null $id
     * @param Site|null $site
     * @return Response
     * @throws NotFoundHttpException if $id is invalid
     */
    public function actionEdit(int $id = null, Site $site = null): Response
    {
        $manager = CraftGridsome::getInstance()->getSiteManager();

        if ($site === null) {
            if ($id !== null) {
                try {
                    $site = $manager->getSiteById($id);
                } catch (InvalidArgumentException $e) {
                    throw new NotFoundHttpException($e->getMessage(), 0, $e);
                }
            } else {
                $site = new Site();
            }
        }

        if ($site->id) {
            $title = trim($site->name) ?: Craft::t('sites', 'Edit Static Site');
        } else {
            $title = Craft::t('craft-gridsome', 'Add a new Gridsome Nogn');
        }

        $crumbs = [
            [
                'label' => Craft::t('craft-gridsome', 'Nogn'),
                'url' => UrlHelper::url('craft-gridsome')
            ]
        ];

        return $this->renderTemplate('craft-gridsome/_sites/edit', compact(
            'site',
            'title',
            'crumbs'
        ));

    }

    /**
     * @return Response|null
     * @throws BadRequestHttpException
     */
    public function actionSave()
    {
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();
        $manager = CraftGridsome::getInstance()->getSiteManager();

        $id = $request->getBodyParam('id');

        if ($id) {
            try {
                $site = $manager->getSiteById($id);
            } catch (InvalidArgumentException $e) {
                throw new BadRequestHttpException($e->getMessage(), 0, $e);
            }
        } else {
            $site = new Site();
        }

        $attributes = $request->getBodyParams();
 
        if (ArrayHelper::remove($attributes, 'customPayload')) {
            $attributes['userAttributes'] = null;
            $attributes['senderAttributes'] = null;
            $attributes['eventAttributes'] = null;

            if (empty($attributes['payloadTemplate'])) {
                $attributes['payloadTemplate'] = '{}';
            }
        } else {
            $attributes['payloadTemplate'] = null;
        }
        
        $site->setAttributes($attributes);

        if (!CraftGridsome::getInstance()->getSiteManager()->saveSite($site)) {
            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => false,
                    'errors' => $site->getErrors(),
                ]);
            }

            Craft::$app->getSession()->setError(Craft::t('craft-gridsome', 'Couldn’t save site.'));
            Craft::$app->getUrlManager()->setRouteParams([
                'site' => $site,
            ]);
            return null;
        }

        if ($request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'site' => $site->toArray(),
            ]);
        }

        Craft::$app->getSession()->setNotice(Craft::t('craft-gridsome', 'Site saved.'));
        return $this->redirectToPostedUrl($site);
    }

    /**
     * Deletes a site.
     *
     * @return Response
     */
    public function actionDelete(): Response
    {
        $this->requirePostRequest();
        $id = Craft::$app->getRequest()->getRequiredBodyParam('id');
        CraftGridsome::getInstance()->getSiteManager()->deleteSiteById($id);
        return $this->redirectToPostedUrl();
    }
}
