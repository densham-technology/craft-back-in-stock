<?php namespace denshamtechnology\backinstock\behaviors;

use craft\commerce\elements\db\VariantQuery;
use craft\elements\db\ElementQuery;
use yii\base\Behavior;

class BackInStockVariantQueryBehaviour extends Behavior
{
    /** @var VariantQuery */
    public $owner;

    /**
     * @var
     */
    public $hasBackInStockSubscription;

    public $distinct;

    /**
     * Narrows the query results to only variants that are on sale.
     *
     * Possible values include:
     *
     * | Value | Fetches {elements}…
     * | - | -
     * | `true` | on sale
     * | `false` | not on sale
     *
     * @param bool $value
     * @return static self reference
     */
    public function hasBackInStockSubscription(bool $value = true)
    {
        $this->hasBackInStockSubscription = $value;
        return $this;
    }

    public function distinct(bool $value = true)
    {
        $this->distinct = $value;
        return $this;
    }

    public function events()
    {
        return [
            ElementQuery::EVENT_BEFORE_PREPARE => 'beforePrepare',
        ];
    }

    public function beforePrepare()
    {
        if ($this->hasBackInStockSubscription !== null) {
            if ($this->hasBackInStockSubscription) {
                $this->owner->subQuery->join('LEFT JOIN', 'craft_backinstock_subscriptions', 'craft_backinstock_subscriptions.variantId = commerce_variants.id');
                $this->owner->subQuery->andWhere('craft_backinstock_subscriptions.variantId IS NOT NULL');
            } else {
                //
            }
        }

        if ($this->distinct) {
            $this->owner->subQuery->distinct();
        }
    }
}
