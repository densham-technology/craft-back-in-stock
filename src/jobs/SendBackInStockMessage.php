<?php namespace denshamtechnology\backinstock\jobs;

use Craft;
use craft\commerce\Plugin as CommercePlugin;
use craft\helpers\App;
use craft\helpers\DateTimeHelper;
use craft\mail\Message;
use craft\queue\BaseJob;
use DateTime;
use denshamtechnology\backinstock\elements\Subscription;
use Throwable;

class SendBackInStockMessage extends BaseJob
{
    public $emailSubject;

    public $emailTemplatePath;

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

        // Set Craft to the site template mode
        $view = Craft::$app->getView();
        $oldTemplateMode = $view->getTemplateMode();
        $view->setTemplateMode($view::TEMPLATE_MODE_SITE);
        $generalConfig = Craft::$app->getConfig()->getGeneral();
        // Temporarily disable lazy transform generation
        $generateTransformsBeforePageLoad = $generalConfig->generateTransformsBeforePageLoad;
        $generalConfig->generateTransformsBeforePageLoad = true;

        $originalLanguage = Craft::$app->language;
        $craftMailSettings = App::mailSettings();

        $message = new Message();

        $fromEmail = CommercePlugin::getInstance()->getSettings()->emailSenderAddress ?: $craftMailSettings->fromEmail;
        $fromEmail = Craft::parseEnv($fromEmail);

        $fromName = CommercePlugin::getInstance()->getSettings()->emailSenderName ?: $craftMailSettings->fromName;
        $fromName = Craft::parseEnv($fromName);

        if ($fromEmail) {
            $message->setFrom($fromEmail);
        }

        if ($fromName && $fromEmail) {
            $message->setFrom([$fromEmail => $fromName]);
        }

        if ($subscription->getUser()) {
            $message->setTo($subscription->user->email);
        }

        $message->setSubject($this->emailSubject);

        $body = $view->renderTemplate($this->emailTemplatePath, [
            'subscription' => $subscription,
        ]);

        $message->setHtmlBody($body);

        Craft::$app->language = $originalLanguage;
        $view->setTemplateMode($oldTemplateMode);
        $generalConfig->generateTransformsBeforePageLoad = $generateTransformsBeforePageLoad;

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
        return Craft::t('back-in-stock', 'Sending a back in stock email');
    }
}
