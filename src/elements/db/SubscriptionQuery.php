<?php namespace denshamtechnology\backinstock\elements\db;

use Craft;
use craft\commerce\elements\db\ProductQuery;
use craft\commerce\elements\Product;
use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class SubscriptionQuery extends ElementQuery
{
    public $quantity;
    public $variantId;
    public $isActive;
    public $isArchived;

    public function quantity($value): self
    {
        $this->quantity = $value;
        return $this;
    }

    public function variantId($value): self
    {
        $this->variantId = $value;
        return $this;
    }

    public function isActive(bool $value = true): self
    {
        $this->isActive = $value;
        return $this;
    }

    public function isArchived($value = true): self
    {
        $this->isArchived = $value;
        return $this;
    }

    protected function beforePrepare(): bool
    {
        // join in the backinstock_subscriptions table
        $this->joinElementTable('backinstock_subscriptions');

        // select the quantity and currency columns
        $this->query->select([
            'backinstock_subscriptions.userId',
            'backinstock_subscriptions.variantId',
            'backinstock_subscriptions.quantity',
            'backinstock_subscriptions.dateCreated',
            'backinstock_subscriptions.dateArchived',
        ]);

        if ($this->quantity) {
            $this->subQuery->andWhere(['backinstock_subscriptions.quantity' => $this->quantity]);
        }

        if ($this->variantId) {
            $this->subQuery->andWhere(['backinstock_subscriptions.variantId' => $this->variantId]);
        }

        if ($this->isActive) {
            $this->subQuery->andWhere(['backinstock_subscriptions.dateArchived' => null]);
        }

        if ($this->isArchived) {
            $this->subQuery->andWhere(['not', ['backinstock_subscriptions.dateArchived' => null]]);
        }

        return parent::beforePrepare();
    }
}
