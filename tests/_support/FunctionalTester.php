<?php
/**
 * craft-back-in-stock plugin for Craft CMS 3.x
 *
 * Plugin giving customers the ability to add their email to a list which will allow them to be notified when a product that is out of stock is restocked.
 *
 * @link      https://github.com/densham-technology
 * @copyright Copyright (c) 2022 Tom Densham
 */

use Codeception\Actor;
use Codeception\Lib\Friend;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 *
 */
class FunctionalTester extends Actor
{
    use _generated\FunctionalTesterActions;

}
