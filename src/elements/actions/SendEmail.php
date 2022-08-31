<?php namespace denshamtechnology\backinstock\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;
use craft\helpers\Queue;
use denshamtechnology\backinstock\elements\Subscription;
use denshamtechnology\backinstock\jobs\SendBackInStockMessage;

class SendEmail extends ElementAction
{
    public $triggerLabel;

    public $emailSubject;

    public $emailTemplatePath;

    public function getTriggerLabel(): string
    {
        return $this->triggerLabel ?? Craft::t('back-in-stock', 'Send email');
    }

    /**
     * @inheritdoc
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $subscriptions = $query->select('backinstock_subscriptions.id')->all();

        foreach ($subscriptions as $subscription) {
            /** @var Subscription $subscription */
            Queue::push(new SendBackInStockMessage([
                'subscriptionId'    => $subscription->id,
                'emailSubject'      => $this->emailSubject,
                'emailTemplatePath' => $this->emailTemplatePath,
            ]));
        }

        return true;
    }
}
