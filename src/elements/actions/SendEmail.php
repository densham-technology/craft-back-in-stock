<?php namespace denshamtechnology\backinstock\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Queue;
use denshamtechnology\backinstock\elements\Subscription;
use denshamtechnology\backinstock\jobs\SendBackInStockMessage;

class SendEmail extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('back-in-stock', 'Send email');
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $subscriptions = $query->select('id')->all();

        foreach ($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            Queue::push(new SendBackInStockMessage([
                'subscriptionId' => $subscription->id,
            ]));
        }

        return true;
    }
}
