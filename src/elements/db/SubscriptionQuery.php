<?php namespace denshamtechnology\backinstock\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriptionQuery extends ElementQuery
{
    public $amount;

    public function amount($value)
    {
        $this->amount = $value;

        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the backinstock_subscriptions table
        $this->joinElementTable('backinstock_subscriptions');

        // select the amount and currency columns
        $this->query->select([
            'backinstock_subscriptions.userId',
            'backinstock_subscriptions.variantId',
            'backinstock_subscriptions.amount',
            'backinstock_subscriptions.dateCreated',
        ]);

        if ($this->amount) {
            $this->subQuery->andWhere(Db::parseParam('backinstock_subscriptions.amount', $this->amount));
        }

        return parent::beforePrepare();
    }
}
