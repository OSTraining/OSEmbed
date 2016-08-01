<?php
/**
 * @package   OSEmbed
 * @contact   www.alledia.com, support@alledia.com
 * @copyright 2016 Alledia.com, All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

use Alledia\Framework\Joomla\Extension;
use Alledia\Framework;

include_once 'include.php';

if (defined('OSEMBED_LOADED')) {
    /**
     * OSEmbed Content Plugin
     */
    class PlgContentOSEmbed extends Extension\AbstractPlugin
    {
        protected $namespace = 'OSEmbed';

        public $type = 'content';

        protected $allowedToRun = true;

        /**
         * Constructor
         *
         * @param   object  &$subject  The object to observe
         * @param   array   $config    An optional associative array of configuration settings.
         *                             Recognized key values include 'name', 'group', 'params', 'language'
         *                             (this list is not meant to be comprehensive).
         *
         * @since   1.5
         */
        public function __construct(&$subject, $config = array())
        {
            parent::__construct($subject, $config);

            $this->init();

            // Check the minumum requirements
            $helperClass = $this->getHelperClass();
            if (!$helperClass::complyBasicRequirements()) {
                $this->allowedToRun = false;
            }
        }

        protected function getHelperClass()
        {
            if ($this->isPro()) {
                return 'Alledia\\OSEmbed\\Pro\\Helper';
            }

            return 'Alledia\\OSEmbed\\Free\\Helper';
        }

        protected function getEmbedClass()
        {
            if ($this->isPro()) {
                return 'Alledia\\OSEmbed\\Pro\\Embed';
            }

            return 'Alledia\\OSEmbed\\Free\\Embed';
        }

        /**
         * Plugin that loads module positions within content
         *
         * @param   string   $context   The context of the content being passed to the plugin.
         * @param   object   &$article  The article object.  Note $article->text is also available
         * @param   mixed    &$params   The article params
         * @param   integer  $page      The 'page' number
         *
         * @return  mixed   true if there is an error. Void otherwise.
         *
         * @since   1.6
         */
        public function onContentPrepare($context, &$article, &$params, $page = 0)
        {
            // Don't run this plugin when the content is being indexed
            if ($context == 'com_finder.indexer' || !$this->allowedToRun) {
                return true;
            }

            $versionUID = md5($this->extension->getVersion());

            $doc = Framework\Factory::getDocument();
            $doc->addStyleSheetVersion('media/plg_content_osembed/css/osembed.css', $versionUID);
            $doc->addScriptVersion('media/plg_content_osembed/js/osembed.js', $versionUID);

            $embedClass = $this->getEmbedClass();
            $article->text = $embedClass::parseContent($article->text, false);
        }

        public function onContentBeforeSave($context, $article, $isNew)
        {
            $embedClass = $this->getEmbedClass();

            return $embedClass::onContentBeforeSave($article);
        }
    }
}
