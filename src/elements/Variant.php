<?php namespace denshamtechnology\backinstock\elements;

use Craft;
use craft\commerce\elements\Variant as BaseVariant;
use craft\helpers\UrlHelper;
use denshamtechnology\backinstock\BackInStock;

class Variant extends BaseVariant
{
    public $subscribers;
    public $quantity;

    protected static function defineSearchableAttributes(): array
    {
        return array_merge(parent::defineSearchableAttributes(), [
            'supplier',
        ]);
    }

    public function getSearchKeywords(string $attribute): string
    {
        switch ($attribute) {
            case 'username':
                return $this->getUser()->username ?? '';
            case 'supplier':
                return $this->vendProductSupplierName ?? '';
            default:
                return parent::getSearchKeywords($attribute);
        }
    }

    protected static function defineSortOptions(): array
    {
        return [
            'subscribers' => Craft::t('back-in-stock', 'Subscribers'),
            'quantity' => Craft::t('back-in-stock', 'Requested stock'),
            'stock' => Craft::t('commerce', 'Stock'),
            'title' => Craft::t('commerce', 'Title'),
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return array_merge(parent::defineTableAttributes(), [
            'supplier' => Craft::t('back-in-stock', 'Supplier'),
            'quantity' => Craft::t('back-in-stock', 'Requested Stock'),
            'subscribers' => Craft::t('back-in-stock', 'Subscribers'),
        ]);
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        $attributes = [];

        if ($source == '*') {
            $attributes[] = 'type';
        }

        $attributes[] = 'sku';
        $attributes[] = 'supplier';
        $attributes[] = 'stock';
        $attributes[] = 'quantity';
        $attributes[] = 'subscribers';

        return $attributes;
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'supplier':
                return $this->vendProductSupplierName ?? '';
            case 'subscribers':
                $url = UrlHelper::cpUrl("back-in-stock/products/$this->id/subscriptions");
                return '<span><a href="' . $url . '">' . $this->subscribers . '</a></span>';
            default:
                return parent::tableAttributeHtml($attribute);
        }
    }

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function __toString(): string
    {
        $this->getProduct();

        // Use a combined Product and Variant title, if the variant belongs to a product with other variants.
        if (((string)$this->product) !== $this->title) {
            return "{$this->product}: {$this->title}";
        } else {
            if ($this->title !== null && $this->title !== '') {
                return (string)$this->title;
            }
            return (string)$this->id ?: static::class;
        }
    }
}
