<?php namespace denshamtechnology\backinstock\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use denshamtechnology\backinstock\elements\db\SubscriptionQuery;

class Subscription extends Element
{
    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Subscription';
    }

    /**
     * @inheritdoc
     */
    public static function pluralDisplayName(): string
    {
        return 'Subscriptions';
    }

    /**
     * @var int Amount
     */
    public $amount = 0;

    public static function find(): ElementQueryInterface
    {
        return new SubscriptionQuery(static::class);
    }

    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                            ->insert('{{%backinstock_subscriptions}}', [
                                'id' => $this->id,
                                'amount' => $this->amount,
                            ])
                            ->execute();
        } else {
            Craft::$app->db->createCommand()
                            ->update('{{%backinstock_subscriptions}}', [
                                'amount' => $this->amount,
                            ], ['id' => $this->id])
                            ->execute();
        }

        parent::afterSave($isNew);
    }
}
