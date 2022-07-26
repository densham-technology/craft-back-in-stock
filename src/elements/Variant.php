<?php namespace denshamtechnology\backinstock\elements;

use Craft;
use craft\commerce\elements\Variant as BaseVariant;
use denshamtechnology\backinstock\BackInStock;

class Variant extends BaseVariant
{
    protected static function defineTableAttributes(): array
    {
        return array_merge(parent::defineTableAttributes(), [
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
        $attributes[] = 'stock';
        $attributes[] = 'subscribers';

        return $attributes;
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'subscribers':
                $subscribers = BackInStock::$plugin->subscriptions->getSubscriptionsCountForVariant($this->id);
                return '<span><a href="subscriptions/product/' . $this->id . '">' . $subscribers . '</a></span>';
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
