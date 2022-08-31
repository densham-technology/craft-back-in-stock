<?php

namespace denshamtechnology\backinstock;

use craft\commerce\elements\db\VariantQuery;
use craft\commerce\elements\Variant;
use craft\commerce\Plugin as CommercePlugin;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\web\UrlManager;
use denshamtechnology\backinstock\behaviors\BackInStockVariantBehaviour;
use denshamtechnology\backinstock\behaviors\BackInStockVariantQueryBehaviour;
use denshamtechnology\backinstock\elements\Subscription;
use denshamtechnology\backinstock\fields\SubscriptionsField;
use denshamtechnology\backinstock\models\Settings;
use denshamtechnology\backinstock\services\Subscriptions;

use Craft;
use craft\base\Plugin;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    Tom Densham
 * @package   Craftbackinstock
 * @since     0.1.0
 *
 * @property  Subscriptions $subscriptions
 */
class BackInStock extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * BackInStock::$plugin
     *
     * @var BackInStock
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '0.1.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * Craftbackinstock::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Subscription::class;
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['back-in-stock'] = ['template' => 'back-in-stock/variants/index'];
                $event->rules['back-in-stock/products'] = ['template' => 'back-in-stock/variants/index'];
                $event->rules['back-in-stock/products/<variantId:\d+>/subscriptions'] = 'back-in-stock/subscriptions/index';
                $event->rules['back-in-stock/subscriptions'] = 'back-in-stock/subscriptions/index';
                $event->rules['back-in-stock/subscriptions/new'] = 'back-in-stock/subscriptions/edit';
                $event->rules['back-in-stock/subscriptions/<subscriptionId:\d+>'] = 'back-in-stock/subscriptions/edit';
            }
        );

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['back-in-stock/subscriptions/list'] = 'back-in-stock/subscriptions/list';
                $event->rules['back-in-stock/subscriptions/list/<variantId:\d+>'] = 'back-in-stock/subscriptions/list';
            }
        );

        Event::on(
            Variant::class,
            Variant::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->sender->attachBehaviors([
                    BackInStockVariantBehaviour::class,
                ]);
            }
        );

        Event::on(
            VariantQuery::class,
            VariantQuery::EVENT_DEFINE_BEHAVIORS,
            function (DefineBehaviorsEvent $event) {
                $event->sender->attachBehaviors([
                    BackInStockVariantQueryBehaviour::class,
                ]);
            }
        );

        // Register our Field
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                Craft::debug(
                    'Fields::EVENT_REGISTER_FIELD_TYPES',
                    __METHOD__
                );
                $event->types[] = SubscriptionsField::class;
            }
        );

        /**
         * Logging in Craft involves using one of the following methods:
         *
         * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
         * Craft::info(): record a message that conveys some useful information.
         * Craft::warning(): record a warning message that indicates something unexpected has happened.
         * Craft::error(): record a fatal error that should be investigated as soon as possible.
         *
         * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
         *
         * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
         * the category to the method (prefixed with the fully qualified class name) where the constant appears.
         *
         * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
         * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
         *
         * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
         */
        Craft::info(
            Craft::t(
                'back-in-stock',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    public function getCpNavItem()
    {
        $item = parent::getCpNavItem();
        $item['badgeCount'] = $this->subscriptions->getActiveSubscriptionsCount();
        $item['subnav'] = [
            'products' => ['label' => 'Products', 'url' => 'back-in-stock/products'],
            'subscriptions' => ['label' => 'Subscriptions', 'url' => 'back-in-stock/subscriptions'],
        ];
        return $item;
    }

    // Protected Methods
    // =========================================================================

    protected function createSettingsModel()
    {
        return new Settings();
    }

    protected function settingsHtml()
    {
        $commerceEmails = array_map(function ($email) {
            return [
                'label' => $email->name,
                'value' => $email->uid
            ];
        }, CommercePlugin::getInstance()->getEmails()->getAllEmails());

        return Craft::$app->getView()->renderTemplate(
            'back-in-stock/settings',
            [
                'settings'       => $this->getSettings(),
                'commerceEmails' => $commerceEmails,
            ],
        );
    }
}
