<?php namespace denshamtechnology\backinstock\elements;

use Craft;
use craft\base\Element;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin;
use craft\elements\actions\Delete;
use craft\elements\actions\Edit;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use denshamtechnology\backinstock\elements\actions\ArchiveSubscription;
use denshamtechnology\backinstock\elements\db\SubscriptionQuery;

/**
 * @property \craft\commerce\elements\Variant $variant
 * @property \craft\elements\User             $user
 */
class Subscription extends Element
{
    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';

    /**
     * @var int Variant ID
     */
    public $variantId;

    /**
     * @var int User ID
     */
    public $userId;

    /**
     * @var int Quantity
     */
    public $quantity = 0;

    /**
     * @var \DateTime Date Created
     */
    public $dateCreated;

    /**
     * @var \DateTime Date Archived
     */
    public $dateArchived;

    /**
     * @var \craft\commerce\elements\Variant Variant
     */
    private $_variant;

    /**
     * @var \craft\elements\User User
     */
    private $_user;

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

    public static function hasStatuses(): bool
    {
        return true;
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_ACTIVE   => [
                'label' => Craft::t('back-in-stock', 'Active'),
                'color' => 'green',
            ],
            self::STATUS_ARCHIVED => [
                'label' => Craft::t('back-in-stock', 'Archived'),
                'color' => 'grey',
            ],
        ];
    }

    public function getStatus()
    {
        if ($this->dateArchived !== null) {
            return self::STATUS_ARCHIVED;
        }

        return self::STATUS_ACTIVE;
    }

    public function getUser()
    {
        if ($this->_user !== null && $this->_user->id == $this->userId) {
            return $this->_user;
        }

        if ($this->userId) {
            $this->_user = Craft::$app->users->getUserById($this->userId);

            if ($this->_user == null) {
                $this->userId = null;
            }
        }

        return $this->_user;
    }

    /**
     * @param \craft\elements\User|null $user
     *
     * @return void
     */
    public function setUser(User $user = null): void
    {
        $this->_user = $user;
        $this->userId = $user->id;
    }

    public function getVariant()
    {
        if ($this->_variant !== null && $this->_variant->id == $this->variantId) {
            return $this->_variant;
        }

        if ($this->variantId) {
            $this->_variant = Plugin::getInstance()->getVariants()->getVariantById($this->variantId);

            if ($this->_variant == null) {
                $this->variantId = null;
            }
        }

        return $this->_variant;
    }

    /**
     * @param \craft\commerce\elements\Variant $variant
     *
     * @return void
     */
    public function setVariant(Variant $variant): void
    {
        $this->_variant = $variant;
        $this->variantId = $variant->id;
    }

    public static function find(): ElementQueryInterface
    {
        return new SubscriptionQuery(static::class);
    }

    protected static function defineActions(string $source = null): array
    {
        return [
            ArchiveSubscription::class,
            Edit::class,
            Delete::class,
        ];
    }

    public function getCpEditUrl()
    {
        return 'back-in-stock/subscriptions/' . $this->id;
    }

    public function getIsEditable(): bool
    {
        return true; //Craft::$app->user->checkPermission('edit-product:'.$this->getType()->id);
    }

    public function getEditorHtml(): string
    {
        $user = $this->getUser();
        $variant = $this->getVariant();

        $html = Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'elementSelectField',
            [
                [
                    'label'          => Craft::t('back-in-stock', 'Customer'),
                    'id'             => 'user',
                    'name'           => 'user',
                    'elementType'    => 'craft\\elements\\User',
                    'selectionLabel' => Craft::t('back-in-stock', 'Choose'),
                    'limit'          => 1,
                    'elements'       => $user ? [$user] : null,
                    'required'       => true,
                ],
            ]);

        $html .= Craft::$app->getView()->renderTemplateMacro('_includes/forms',
            'elementSelectField', [
                [
                    'label'          => Craft::t('back-in-stock', 'Product'),
                    'id'             => 'variant',
                    'name'           => 'variant',
                    'elementType'    => 'craft\\commerce\\elements\\Variant',
                    'selectionLabel' => Craft::t('back-in-stock', 'Choose'),
                    'limit'          => 1,
                    'elements'       => $variant ? [$variant] : null,
                    'required'       => true,
                ],
            ]);

        $html .= Craft::$app->getView()->renderTemplateMacro('_includes/forms', 'textField', [
            [
                'label'        => Craft::t('back-in-stock', 'Quantity'),
                'instructions' => Craft::t('back-in-stock', 'Enter quantity requested'),
                'id'           => 'quantity',
                'name'         => 'quantity',
                'value'        => $this->quantity,
                'required'     => true,
                'errors'       => $this->getErrors('quantity'),
            ],
        ]);

        $html .= parent::getEditorHtml();

        return $html;
    }

    public function afterSave(bool $isNew)
    {
        if ($isNew) {
            Craft::$app->db->createCommand()
                           ->insert('{{%backinstock_subscriptions}}', [
                               'id'           => $this->id,
                               'userId'       => $this->userId,
                               'variantId'    => $this->variantId,
                               'quantity'     => $this->quantity,
                               'dateArchived' => $this->dateArchived,
                           ])
                           ->execute();
        } else {
            Craft::$app->db->createCommand()
                           ->update('{{%backinstock_subscriptions}}', [
                               'userId'       => $this->userId,
                               'variantId'    => $this->variantId,
                               'quantity'     => $this->quantity,
                               'dateArchived' => $this->dateArchived,
                           ], ['id' => $this->id])
                           ->execute();
        }

        parent::afterSave($isNew);
    }

    /**
     * @inheritdoc
     */
    public function getSearchKeywords(string $attribute): string
    {
        switch ($attribute) {
            case 'variant':
                return $this->getVariant()->title;
            default:
                return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function datetimeAttributes(): array
    {
        $attributes = parent::datetimeAttributes();
        $attributes[] = 'dateArchived';
        return $attributes;
    }

    protected static function defineSources(string $context = null): array
    {
        return [
            [
                'key'      => '*',
                'label'    => Craft::t('back-in-stock', 'All Subscriptions'),
                'criteria' => [],
            ],
            ['heading' => Craft::t('back-in-stock', 'Subscription Status')],
            [
                'key'      => 'active',
                'status'   => 'green',
                'label'    => Craft::t('back-in-stock', 'Active Subscriptions'),
                'criteria' => ['isActive' => true],
            ],
            [
                'key'      => 'archived',
                'status'   => 'grey',
                'label'    => Craft::t('back-in-stock', 'Archived Subscriptions'),
                'criteria' => ['isArchived' => true],
            ],
        ];
    }

    protected static function defineTableAttributes(): array
    {
        return [
            'subscription' => Craft::t('back-in-stock', 'Subscription'),
            'user'         => Craft::t('back-in-stock', 'Customer'),
            'variant'      => Craft::t('back-in-stock', 'Product'),
            'quantity'     => Craft::t('back-in-stock', 'Quantity'),
            'dateCreated'  => Craft::t('back-in-stock', 'Date Subscribed'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected static function defineSortOptions(): array
    {
        return [
            [
                'label'      => Craft::t('back-in-stock', 'Date Subscribed'),
                'orderBy'    => 'dateCreated',
                'defaultDir' => 'desc',
            ],
        ];
    }

    /**
     * @return string[]
     */
    protected static function defineSearchableAttributes(): array
    {
        return ['variant'];
    }

    /**
     * @inheritdoc
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        switch ($attribute) {
            case 'user':
                return $this->getCustomerLinkHtml();
            case 'variant':
                return $this->getVariantLinktHtml();
            default:
                return parent::tableAttributeHtml($attribute);
        }
    }

    public function getCustomerLinkHtml(): string
    {
        if ($this->getUser()) {
            return '<span><a href="' . $this->getUser()->getCpEditUrl() . '">' . $this->getUser()->email . '</a></span>';
        }

        return '';
    }

    public function getVariantLinktHtml(): string
    {
        if ($this->getVariant()) {
            return '<span><a href="' . $this->getVariant()->getCpEditUrl() . '">' . $this->getVariant()->title . '</a></span>';
        }
    }
}
