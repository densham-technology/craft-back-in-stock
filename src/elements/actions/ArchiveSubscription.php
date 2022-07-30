<?php namespace denshamtechnology\backinstock\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\DateTimeHelper;
use DateTime;
use denshamtechnology\backinstock\elements\Subscription;

class ArchiveSubscription extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('back-in-stock', 'Archive');
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $subscriptions = $query->all();

        foreach ($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            $subscription->dateArchived = DateTimeHelper::toIso8601(new DateTime('now'));
            Craft::$app->getElements()->saveElement($subscription);
        }

        return true;
    }
}
