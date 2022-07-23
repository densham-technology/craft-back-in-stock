<?php namespace denshamtechnology\backinstock\elements\actions;

use Craft;
use craft\base\ElementAction;

class ResolveSubscription extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('back-in-stock', 'Resolve');
    }
}
