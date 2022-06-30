<?php namespace denshamtechnology\backinstock\models;

use craft\base\Component;
use craft\base\Element;
use craft\elements\Entry;

class Subscription extends Entry
{
    /**
     * @var int ID
     */
    public $id;

    /**
     * @var int User ID
     */
    public $userId;

    /**
     * @var int Variant ID
     */
    public $variantId;

    /**
     * @var \DateTime|null Date created
     */
    public $dateCreated;

    /**
     * @var \DateTime|null Date updated
     */
    public $dateUpdated;

    /**
     * @var string|null Uid
     */
    public $uid;

    /**
     * @var \craft\elements\User
     */
    public $user;

    /**
     * @var \craft\commerce\elements\Variant
     */
    public $variant;
}
