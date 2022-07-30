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
    public $hasProduct;

    public function quantity($value)
    {
        $this->quantity = $value;
        return $this;
    }

    public function variantId($value)
    {
        $this->variantId = $value;
        return $this;
    }

    public function isActive($value)
    {
        $this->isActive = $value;
        return $this;
    }

    public function isArchived($value)
    {
        $this->isArchived = $value;
        return $this;
    }

    public function hasProduct($value)
    {
        $this->hasProduct = $value;
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
            $this->subQuery->andWhere(Db::parseParam('backinstock_subscriptions.quantity', $this->quantity));
        }

        if ($this->variantId) {
            $this->subQuery->andWhere(Db::parseParam('backinstock_subscriptions.variantId', $this->variantId));
        }

        if ($this->isActive) {
            $this->subQuery->andWhere(['dateArchived' => null]);
        }

        if ($this->isArchived) {
            $this->subQuery->andWhere(['not', ['dateArchived' => null]]);
        }

        $this->applyHasProductParam();

        return parent::beforePrepare();
    }

    /**
     * Applies the hasProduct query condition
     */
    private function applyHasProductParam()
    {
        if ($this->hasProduct === null) {
            return;
        }

        if ($this->hasProduct instanceof ProductQuery) {
            $productQuery = $this->hasProduct;
        } elseif (is_array($this->hasProduct)) {
            $query = Product::find();
            $productQuery = Craft::configure($query, $this->hasProduct);
        } else {
            return;
        }

        $productQuery->limit = null;
        $productQuery->select('commerce_products.id');
        $productIds = $productQuery->column();

        // Remove any blank product IDs (if any)
        $productIds = array_filter($productIds);
        $this->subQuery->leftJoin('craft_commerce_variants', 'craft_commerce_variants.id = backinstock_subscriptions.variantId');
        $this->subQuery->andWhere(['craft_commerce_variants.productId' => $productIds]);
    }
}
