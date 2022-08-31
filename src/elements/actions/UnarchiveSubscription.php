<?php namespace denshamtechnology\backinstock\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use denshamtechnology\backinstock\elements\Subscription;

class UnarchiveSubscription extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('back-in-stock', 'Unarchive');
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $subscriptions = $query->all();

        foreach ($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            $subscription->dateArchived = null;
            Craft::$app->getElements()->saveElement($subscription);
        }

        return true;
    }
}
