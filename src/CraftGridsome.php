<?php
/**
 * Craft Gridsome plugin for Craft CMS 3.x
 *
 * Make headless Craft great again (for the first time).
 *
 * @link      bensheedy.com
 * @copyright Copyright (c) 2020 Ben Sheedy
 */

namespace nogn\craftgridsome;


use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;

use craft\elements\Entry;
use craft\events\RegisterUrlRulesEvent;
use craft\events\RegisterPreviewTargetsEvent;
use craft\web\UrlManager;
use yii\base\Event;

/**
 * @author    Ben Sheedy
 * @package   CraftGridsome
 * @since     0.1.0
 *
 */
class CraftGridsome extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * CraftGridsome::$plugin
     *
     * @var CraftGridsome
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @inheritdoc
     */
    public $hasCpSection = true;

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '0.1.0';

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * CraftGridsome::$plugin
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
        
        if (!$this->isInstalled) {
            return;
        }

        self::$plugin = $this;

        

        $manager = new SiteManager();
        $this->set('siteManager', $manager);

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

        // Register CP routes
        Event::on(UrlManager::class, UrlManager::EVENT_REGISTER_CP_URL_RULES, function(RegisterUrlRulesEvent $e) {
            $e->rules['craft-gridsome'] = 'craft-gridsome/sites/index';
            $e->rules['craft-gridsome/new'] = 'craft-gridsome/sites/edit';
            $e->rules['craft-gridsome/<id:\d+>'] = 'craft-gridsome/sites/edit';
        });


        // Register site preview targets
        $sites = $manager->getAllSites();
        
        foreach ($sites as $site) {
            
            Event::on(Entry::class, Entry::EVENT_REGISTER_PREVIEW_TARGETS, function(RegisterPreviewTargetsEvent $event) use ($site) {
                $sectionId = $event->sender->getSection()->id;
                $sectionHandle = $event->sender->getSection()->handle;
                $typeHandle = $event->sender->getType()->handle;

                // target the the Gridsome template
                $template = 'craftEntry' . ucfirst($sectionHandle) . ucfirst ($typeHandle);
                // $templateKebab = strtolower(preg_replace('%([A-Z])([a-z])%', '-\1\2', $template));
                
                if (in_array($sectionId, $site->sectionIds)) {
                    $uid = $event->sender->getSourceUid() ?? $event->sender->uid;
                    $event->previewTargets[] = [
                        'label' => $site->name,
                        'url' => $site->url . $event->sender->uri 
                            . '?nogn-uid=' . $uid
                            . '&nogn-slug=' . $event->sender->slug
                            . '&nogn-template=' . $template
                            . '&nogn-id=' . $event->sender->sourceId ?? $event->sender->id,
                        'refresh' => false,
                    ];
                };
            });
        }

        $js = 'Garnish.on(Craft.Preview, \'beforeUpdateIframe\', function(event) {
            if (!event.refresh && event.target.$iframe) {
                 event.target.$iframe[0].contentWindow.postMessage(\'content updated\', \'*\')
            }
        })';

        if (Craft::$app->request->getIsCpRequest()) {
            Craft::$app->view->registerJs($js);
        }

       

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
                'craft-gridsome',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    /**
     * @inheritdoc
     */
    // public function getCpNavItem()
    // {
    //     $item = parent::getCpNavItem();
    //     // $item['subnav'] = [
    //     //     'manage' => ['label' => Craft::t('craft-gridsome', 'Manage Sites'), 'url' => 'craft-gridsome'],
    //     // ];
    //     return $item;
    // }

    /**
     * Returns the site manager.
     *
     * @return SiteManager
     */
    public function getSiteManager(): SiteManager
    {
        return $this->get('siteManager');
    }

    // Protected Methods
    // =========================================================================

}
