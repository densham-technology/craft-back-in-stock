<?php namespace denshamtechnology\backinstock\behaviors;

use craft\commerce\elements\db\VariantQuery;
use craft\elements\db\ElementQuery;
use craft\helpers\ArrayHelper;
use yii\base\Behavior;
use yii\db\Expression;

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
            if ($this->hasBackInStockSubscription && ($this->owner->join === null || !$this->owner->isJoined('craft_backinstock_subscriptions'))) {
                $this->owner->addSelect([
                    'subquery.subscribers',
                    'subquery.quantity',
                ]);
                $this->owner->innerJoin('craft_backinstock_subscriptions subscriptions', 'subscriptions.variantId = commerce_variants.id');

                $this->owner->subQuery->addSelect([
                    'COUNT(subscriptions.id) AS subscribers',
                    'SUM(subscriptions.quantity) AS quantity',
                ]);
                $this->owner->subQuery->andWhere('subscriptions.variantId IS NOT NULL');
                $this->owner->subQuery->andWhere('subscriptions.dateArchived IS NULL');
                $this->owner->subQuery->groupBy(['subscriptions.variantId', 'commerce_variants.id', 'elements_sites.id', 'content.id']);

                $firstSelectValue = ArrayHelper::firstValue($this->owner->select);

                if ($firstSelectValue === 'COUNT(*)') {
                    $this->owner->select = [new Expression('COUNT(DISTINCT(subscriptions.variantId))')];
                } else {
                    $this->owner->groupBy(['subscriptions.variantId', 'commerce_variants.id', 'elements_sites.id', 'content.id']);
                }
            } else {
                //
            }
        }

//        if ($this->distinct) {
//            $this->owner->query->distinct();
//        }
    }
}
