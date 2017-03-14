<?php
/**
 * Snitch plugin for Craft CMS 3.x
 *
 * Report when two people might be editing the same entry, category, or global
 *
 * @link      http://marion.newlevant.com
 * @copyright Copyright (c) 2017 Marion Newlevant
 */

namespace marionnewlevant\snitch\controllers;

use marionnewlevant\snitch\Plugin as Snitch;

use Craft;
use craft\web\Controller;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    Marion Newlevant
 * @package   Snitch
 * @since     1.0.0
 */
class CollisionController extends Controller
{

    public function actionAjaxEnter()
    {
        $this->requireAcceptsJson();
        $elementId = intval(Craft::$app->getRequest()->getBodyParam('elementId'));
        // expire any old collisions
        Snitch::getInstance()->collision->expire();
        // record this person is editing this element
        Snitch::getInstance()->collision->register($elementId);
        // get any collisions
        $collisionModels = Snitch::getInstance()->collision->getCollisions($elementId);
        // pull the user data out of our collisions
        $userData = Snitch::getInstance()->collision->userData($collisionModels);
        // and return
        $json = $this->asJson([
            'collisions' => $userData,
        ]);
        return $json;
    }

    public function actionGetConfig()
    {
        $this->requireAcceptsJson();
        $json = $this->asJson([
            'message' => Craft::$app->getConfig()->get('message', 'snitch'),
            'serverPollInterval' => Craft::$app->getConfig()->get('serverPollInterval', 'snitch'),
            'inputIdSelector' => Craft::$app->getConfig()->get('inputIdSelector', 'snitch'),
        ]);
        return $json;
    }
}
