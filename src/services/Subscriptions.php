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
use craft\commerce\elements\Variant;
use denshamtechnology\backinstock\elements\Subscription as SubscriptionElement;
use denshamtechnology\backinstock\models\Subscription;

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
        $results = SubscriptionElement::find()
                                      ->where([
                                          'in',
                                          'variantId',
                                          Variant::find()->where(['productId' => $id])->select(['commerce_variants.id']),
                                      ])
                                      ->all();

        return $results;
    }

    public function getActiveSubscriptionsCount(): int
    {
        return SubscriptionElement::find()->isActive()->count();
    }

    public function getActiveSubscriptionsCountForVariant($id): int
    {
        return SubscriptionElement::find()->isActive()->variantId($id)->count();
    }

    public function getActiveSubscriptionsRequestedStockForVariant($id): int
    {
        return SubscriptionElement::find()->isActive()->variantId($id)->sum('quantity');
    }

    public function getSubscriptionsForVariant($id)
    {
        return SubscriptionElement::find()
                                  ->where(['variantId' => $id])
                                  ->all();
    }

    public function getSubscriptionById($id)
    {
        return SubscriptionElement::find()->where(['backinstock_subscriptions.id' => $id])->one();
    }

    public function getUserSubscriptionById($id)
    {
        $userId = Craft::$app->getUser()->id;

        return SubscriptionElement::find()
                                  ->where([
                                      'backinstock_subscriptions.id'     => $id,
                                      'backinstock_subscriptions.userId' => $userId,
                                  ])
                                  ->one();
    }

    public function getActiveSubscriptionsForUser($userId, $variantId = null): array
    {
        $subscriptions = SubscriptionElement::find()
                                            ->isActive()
                                            ->filterWhere([
                                                'variantId' => $variantId,
                                                'userId'    => $userId,
                                            ])
                                            ->all();

        return $this->toRecords($subscriptions);
    }

    public function getUserSubscriptions()
    {
        $userId = Craft::$app->getUser()->id;

        $subscriptions = SubscriptionElement::find()->where(['userId' => $userId])->all();

        return $this->toRecords($subscriptions);
    }

    public function updateUserSubscription($id, $quantity): ?Subscription
    {

    }

    /**
     * @param array $subscriptions
     *
     * @return \denshamtechnology\backinstock\models\Subscription[]
     * @throws \yii\base\InvalidConfigException
     */
    private function toRecords(array $subscriptions): array
    {
        return array_map(function (SubscriptionElement $element) {
            $variant = $element->getVariant();

            // Eager load product
            $variant->getProduct();

            return new Subscription([
                'id'           => $element->id,
                'quantity'     => $element->quantity,
                'variant'      => $variant,
                'productType'  => $variant->getProduct()->getType()->handle,
                'dateCreated'  => $element->dateCreated,
                'dateUpdated'  => $element->dateUpdated,
                'dateArchived' => $element->dateArchived,
            ]);
        }, $subscriptions);
    }
}
