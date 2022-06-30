<?php
/**
 * craft-back-in-stock plugin for Craft CMS 3.x
 *
 * Plugin giving customers the ability to add their email to a list which will allow them to be notified when a product that is out of stock is restocked.
 *
 * @link      https://github.com/densham-technology
 * @copyright Copyright (c) 2022 Tom Densham
 */

namespace denshamtechnology\craftbackinstocktests\unit;

use Codeception\Test\Unit;
use UnitTester;
use Craft;
use denshamtechnology\backinstock\BackInStock;

/**
 * ExampleUnitTest
 *
 *
 * @author    Tom Densham
 * @package   Craftbackinstock
 * @since     0.1.0
 */
class ExampleUnitTest extends Unit
{
    // Properties
    // =========================================================================

    /**
     * @var UnitTester
     */
    protected $tester;

    // Public methods
    // =========================================================================

    // Tests
    // =========================================================================

    /**
     *
     */
    public function testPluginInstance()
    {
        $this->assertInstanceOf(
            BackInStock::class,
            BackInStock::$plugin
        );
    }

    /**
     *
     */
    public function testCraftEdition()
    {
        Craft::$app->setEdition(Craft::Pro);

        $this->assertSame(
            Craft::Pro,
            Craft::$app->getEdition()
        );
    }
}
