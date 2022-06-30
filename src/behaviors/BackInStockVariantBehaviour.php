<?php namespace denshamtechnology\backinstock\behaviors;

use craft\commerce\elements\Variant;
use denshamtechnology\backinstock\records\Subscription;
use yii\base\Behavior;

class BackInStockVariantBehaviour extends Behavior
{
    /** @var Variant */
    public $owner;

    public function hasBackInStockSubscription(): bool
    {
        return Subscription::find()
            ->where(['variantId' => $this->owner->id])
            ->exists();
    }
}
