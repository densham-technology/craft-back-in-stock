<?php namespace denshamtechnology\backinstock\jobs;

use Craft;
use craft\helpers\DateTimeHelper;
use craft\mail\Message;
use craft\queue\BaseJob;
use DateTime;
use denshamtechnology\backinstock\elements\Subscription;
use Throwable;

class SendBackInStockMessage extends BaseJob
{
    public $subscriptionId;

    /**
     * @inheritDoc
     */
    public function execute($queue): void
    {
        /** @var Subscription $subscription */
        $subscription = Subscription::findOne($this->subscriptionId);

        if ($subscription->dateArchived !== null) {
            return;
        }

        $message = new Message();

        $message->setTo($subscription->user->email);
        $message->setSubject('Back in stock!');
        $message->setTextBody('Hello from the queue system! 👋');

        try {
            Craft::$app->getMailer()->send($message);
            $subscription->dateArchived = DateTimeHelper::toIso8601(new DateTime('now'));
            Craft::$app->getElements()->saveElement($subscription);
        } catch (Throwable $exception) {
            // Don’t let an exception block the queue
            Craft::warning("Something went wrong: {$exception->getMessage()}", __METHOD__);
        }
    }

    /**
     * @inheritdoc
     */
    protected function defaultDescription(): string
    {
        return Craft::t('back-in-stock', 'Sending a worthless email');
    }
}
