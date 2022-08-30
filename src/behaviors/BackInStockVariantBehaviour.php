<?php namespace denshamtechnology\backinstock\behaviors;

use craft\commerce\elements\Variant;
use denshamtechnology\backinstock\elements\Subscription;
use yii\base\Behavior;

class BackInStockVariantBehaviour extends Behavior
{
    /** @var Variant */
    public $owner;

    public function hasBackInStockSubscription(): bool
    {
        return Subscription::find()
            ->variantId($this->owner->id)
            ->isActive()
            ->exists();
    }
}
