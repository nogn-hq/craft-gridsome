<?php

namespace nogn\craftgridsome\controllers;

use craft\web\Controller;

/**
 * Base CraftGridsom Controller
 *
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 2.0
 */
abstract class BaseController extends Controller
{
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $this->requirePermission('accessPlugin-craft-gridsome');

        return true;
    }
}
