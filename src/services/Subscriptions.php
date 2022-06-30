<?php
/**
 * craft-back-in-stock plugin for Craft CMS 3.x
 *
 * Plugin giving customers the ability to add their email to a list which will allow them to be
 * notified when a product that is out of stock is restocked.
 *
 * @link      https://github.com/densham-technology
 * @copyright Copyright (c) 2022 Tom Densham
 */

namespace denshamtechnology\backinstock\services;

use Craft;
use craft\base\Component;
use craft\commerce\Plugin as Commerce;
use craft\commerce\records\Product;
use craft\elements\User;
use denshamtechnology\backinstock\models\Subscription;
use denshamtechnology\backinstock\records\Subscription as SubscriptionRecord;

/**
 * CraftbackinstockService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    Tom Densham
 * @package   Craftbackinstock
 * @since     0.1.0
 */
class Subscriptions extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     BackInStock::$plugin->subscriptions->getSubscriptionsForVariant()
     *
     * @return mixed
     */
    public function getSubscriptionsForProduct($id)
    {
        $results = SubscriptionRecord::find()
                                     ->where(['in', 'variantId', Product::findOne(['id', $id])])
                                     ->all();

        return array_map(function (&$record) {
            $subscription = new Subscription($record);
            $subscription->user = Craft::$app->users->getUserById($record->userId);
            $subscription->variant = Commerce::getInstance()->variants->getVariantById($record->variantId);
            return $subscription;
        }, $results);
    }

    public function getSubscriptionsForVariant($id)
    {
        return SubscriptionRecord::find()
                                 ->where(['variantId' => $id])
                                 ->all();
    }
}
