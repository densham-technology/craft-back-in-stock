<?php namespace denshamtechnology\backinstock\models;

use craft\base\Model;

class Subscription extends Model
{
    public $id;
    public $quantity;
    public $variant;
    public $productType;
    public $dateCreated;
    public $dateUpdated;
    public $dateArchived;
}
