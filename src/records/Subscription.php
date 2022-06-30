<?php
/**
 * craft-back-in-stock plugin for Craft CMS 3.x
 *
 * Plugin giving customers the ability to add their email to a list which will allow them to be notified when a product that is out of stock is restocked.
 *
 * @link      https://github.com/densham-technology
 * @copyright Copyright (c) 2022 Tom Densham
 */

namespace denshamtechnology\backinstock\records;

use craft\commerce\records\Variant;
use craft\records\User;

use Craft;
use craft\db\ActiveRecord;

/**
 * CraftbackinstockRecord Record
 *
 * ActiveRecord is the base class for classes representing relational data in terms of objects.
 *
 * Active Record implements the [Active Record design pattern](http://en.wikipedia.org/wiki/Active_record).
 * The premise behind Active Record is that an individual [[ActiveRecord]] object is associated with a specific
 * row in a database table. The object's attributes are mapped to the columns of the corresponding table.
 * Referencing an Active Record attribute is equivalent to accessing the corresponding table column for that record.
 *
 * http://www.yiiframework.com/doc-2.0/guide-db-active-record.html
 *
 * @author    Tom Densham
 * @package   Craftbackinstock
 * @since     0.1.0
 */
class Subscription extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

     /**
     * Declares the name of the database table associated with this AR class.
     * By default this method returns the class name as the table name by calling [[Inflector::camel2id()]]
     * with prefix [[Connection::tablePrefix]]. For example if [[Connection::tablePrefix]] is `tbl_`,
     * `Customer` becomes `tbl_customer`, and `OrderItem` becomes `tbl_order_item`. You may override this method
     * if the table is not named after this convention.
     *
     * By convention, tables created by plugins should be prefixed with the plugin
     * name and an underscore.
     *
     * @return string the table name
     */
    public static function tableName()
    {
        return '{{%backinstock_subscriptions}}';
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    public function getVariant()
    {
        return $this->hasOne(Variant::class, ['id' => 'variantId']);
    }
}
