<?php namespace denshamtechnology\backinstock\controllers;

use Craft;
use craft\web\Controller;
use denshamtechnology\backinstock\BackInStock;
use denshamtechnology\backinstock\elements\Subscription;
use yii\web\BadRequestHttpException;
use yii\web\Response;

class SubscriptionsController extends Controller
{
    public function actionIndex(int $variantId = null): Response
    {
        return $this->renderTemplate('back-in-stock/subscriptions/index', [
            'criteria' => $variantId ? "{variantId:$variantId}" : '[]',
        ]);
    }

    public function actionEdit(
        ?int $subscriptionId = null,
        ?Subscription $subscription = null
    ): Response {
        // Ensure the user has permission to save events
//        $this->requirePermission('edit-events');

        if (!$subscription) {
            // Are we editing an existing event?
            if ($subscriptionId) {
                $subscription = BackInStock::$plugin->subscriptions->getSubscriptionById($subscriptionId);
                if (!$subscription) {
                    throw new BadRequestHttpException("Invalid subscription ID: $subscriptionId");
                }
            } else {
                // We're creating a new event
                $subscription = new Subscription();
            }
        }

        return $this->renderTemplate('back-in-stock/subscriptions/_edit', [
            'subscription' => $subscription,
            'user'         => $subscription->getUser(),
            'variant'      => $subscription->getVariant(),
        ]);
    }

    /**
     * @return \yii\web\Response|null
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave(): ?Response
    {
        $subscriptionId = $this->request->getBodyParam('subscriptionId');

        if ($subscriptionId) {
            // Find existing subscription element
            $subscription = BackInStock::$plugin->subscriptions->getSubscriptionById($subscriptionId);

            if (!$subscription) {
                throw new BadRequestHttpException("Invalid subscription ID: $subscriptionId");
            }
        } else {
            // Create a new subscription element
            $subscription = new Subscription();
        }

        // Set the main properties from POST data
        $subscription->quantity = $this->request->getBodyParam('quantity');
        $subscription->userId = $this->request->getBodyParam('user')[0];
        $subscription->variantId = $this->request->getBodyParam('variant')[0];

        // Set custom field values from POST data in a `fields` namespace
//        $subscription->setFieldValuesFromRequest('fields');

        // Save the subscription
        $success = Craft::$app->elements->saveElement($subscription);

        if (!$success) {
            $this->setFailFlash(Craft::t('back-in-stock', 'Couldn’t save subscription.'));

            // Send the subscription back to the edit action
            Craft::$app->urlManager->setRouteParams([
                'subscription' => $subscription,
            ]);

            return null;
        }

        if ($this->request->acceptsJson) {
            return $this->asJson(['success' => true]);
        }

        $this->setSuccessFlash(Craft::t('back-in-stock', 'Subscription saved.'));

        return $this->redirectToPostedUrl($subscription);
    }
}
