<?php namespace denshamtechnology\backinstock\fields;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Html;
use denshamtechnology\backinstock\BackInStock;

class SubscriptionsField extends Field
{
    /**
     * @inheritdoc
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('back-in-stock', 'Subscriptions');
    }

    /**
     * @inheritdoc
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Get our id and namespace
        $id = Html::id($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);

        $canonicalId = $element->getCanonicalId();
        $subscriptions = BackInStock::$plugin->subscriptions->getSubscriptionsForProduct($canonicalId);

        return Craft::$app->getView()->renderTemplate(
            'back-in-stock/fields/_subscriptions',
            [
                'elementId' => $element->getCanonicalId(),
                'name' => $this->handle,
                'subscriptions' => $subscriptions,
                'field' => $this,
                'id' => $id,
                'namespacedId' => $namespacedId,
            ]
        );
    }
}
