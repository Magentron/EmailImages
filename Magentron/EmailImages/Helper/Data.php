<?php
/**
 *  Magentron EmailImages Extension
 *
 *  @category   Magentron
 *  @package    Magentron_EmailImages
 *  @author     Jeroen Derks
 *  @copyright  Copyright (c) 2011-2015 Jeroen Derks http://www.magentron.com
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magentron_EmailImages_Helper_Data extends Mage_Core_Helper_Abstract
{
    /** Configuration path for extension enablement.
     *  @var    string
     *  @see    isEnabled()
     */
    const XML_EMAIL_IMAGES_ENABLE       = 'system/emailimages/enable';
    
    /** Configuration path for maximum cache lifetime.
     *  @var    string
     *  @see    getCacheTime()
     */
    const XML_EMAIL_IMAGES_CACHE_TIME   = 'system/emailimages/cache_time';

    /** Configuration path for regular expression used to find image URLs in HTML.
     *  @var    string
     *  @see    getRegularExpression()
     */
    const XML_EMAIL_IMAGES_REGEXP       = 'system/emailimages/regexp';

    /** Configuration path for index into matches of regular expression used to find image URLs in HTML.
     *  @var    string
     *  @see    XML_EMAIL_IMAGES_REGEXP,
     *          getRegularExpressionIndex()
     */
    const XML_EMAIL_IMAGES_REGEXP_INDEX = 'system/emailimages/regexp_index';
    
    /** Default maximum lifetime used for cache.
     *  @var    integer
     *  @see    getCacheTime()
     */
    const DEFAULT_CACHE_TIME            = 86400;
    
    /** Default regular expression to extract image URLs from the email HTML body.
     *  @var    string
     *  @see    getRegularExpression()
     */
    const DEFAULT_REGEXP                = '/((<[iI][mM][gG] [^>]*[sS][rR][cC]|[Bb][Aa][Cc][Kk][Gg][Rr][Oo][Uu][Nn][Dd])="|image:url\(\'?)([^\'"\)]*)(["\'\)])/';
    
    /** Default index in the regular expression matches to extract the image URLs from the email HTML body.
     *  @var    integer
     *  @see    getRegularExpressionIndex()
     */
    const DEFAULT_REGEXP_INDEX          = 3;
    
    /** Cache tag to use.
     *  @var    string
     */
    const CACHE_TAG                     = 'MAGENTRON_EMAILIMAGES';

    /** Cache type to use.
     *  @var    string
     */
    const CACHE_TYPE                    = 'emailimages';
    
    
    /**
     *  Is the EmailImages extension enabled to actually attach images?
     *
     *  @return boolean
     *
     *  @see    XML_EMAIL_IMAGES_ENABLE
     */
    public function isEnabled()
    {
        return (boolean) Mage::getStoreConfig(self::XML_EMAIL_IMAGES_ENABLE);
    }

    
    /**
     *  Retrieve the maximum lifetime for caching in seconds.
     *
     *  @return integer
     *
     *  @see    XML_EMAIL_IMAGES_CACHE_TIME, DEFAULT_CACHE_TIME
     */
    public function getCacheTime()
    {
        $config = Mage::getStoreConfig(self::XML_EMAIL_IMAGES_CACHE_TIME);

        if ( !is_numeric($config) )
            $config = self::DEFAULT_CACHE_TIME;

        return (integer) $config;   
    }


    /**
     *  Retrieve the regular expression to extract the image URLs from the email HTML body.
     *
     *  @return string
     *
     *  @see    XML_EMAIL_IMAGES_REGEXP, DEFAULT_REGEXP
     */
    public function getRegularExpression()
    {
        $config = Mage::getStoreConfig(self::XML_EMAIL_IMAGES_REGEXP);

        if ( '' == $config )
            $config = self::DEFAULT_REGEXP;

        return (string) $config;    
    }


    /**
     *  Retrieve the index in the regular expression matches to extract the image URLs from the email HTML body.
     *
     *  @return integer
     *
     *  @see    XML_EMAIL_IMAGES_REGEXP_INDEX, DEFAULT_REGEXP_INDEX
     */
    public function getRegularExpressionIndex()
    {
        $config = Mage::getStoreConfig(self::XML_EMAIL_IMAGES_REGEXP_INDEX);

        if ( !is_numeric($config) )
            $config = self::DEFAULT_REGEXP_INDEX;
            
        return (integer) $config;   
    }
    
    /**
     *  Attach images to mail object.
     *
     *  @param  Zend_Mail   $mail       Zend_Mail instance to attach images to.
     *  @param  string      $context    [optional] Set to unique identifier for template, so that body needs to be parsed only once per template (NB: case-insensitive). 
     *  @return void                    Fails silently if unable to attach image, warning message sent to log.
     *
     *  @see    isEnabled(), _getImageUrlsFromMail(), _attachImageUrls()
     */
    public function addImages( Zend_Mail $mail, $context = null )
    {
        // check whether the administrator has enabled the module
        if ( !$this->isEnabled() )
        {
            Mage::log('EmailImages - extension disabled');
            return; 
        }

        try
        {
            $urls = $this->_getImageUrlsFromMail($mail, $context);
            if ( $urls )
                $this->_attachImageUrls($mail, $urls);
        }
        catch ( Exception $e )
        {
            // ignore exception, but do log it
            Mage::log('EmailImages - ERROR: exception caught: ' . $e, Zend_Log::ERR);
        }
    }
    
    
    /**
     *  Remove cached image and context data from the cache.
     *
     *  @return Magentron_EmailImages_Helper_Data               Provides fluent interface
     *
     *  @see    CACHE_TAG,
     *          Mage_Core_Model_Cache::flush()
     */
    public function cleanCache()
    {
        /** @var    $cache  Mage_Core_Model_Cache */
        $cache = Mage::getSingleton('core/cache');
        $cache->flush(self::CACHE_TAG);
        
        return $this;
    }

    /**
     *  Retrieve image URLs from email content
     *
     *  @param  Zend_Mail   $mail       Zend_Mail instance to attach images to.
     *  @param  string      $context    [optional] Set to unique identifier for template, so that body needs to be parsed only once per template (NB: case-insensitive). 
     *  @return array                   Array of image URLs.
     *
     *  @see    CACHE_TYPE,
     *          _getContextDataFromCache(), _getBodyHtml(), _getImageUrlsFromBodyHtml(), _saveContextDataToCache(),
     *          Mage_Core_Model_Cache::canUse()
     */
    protected function _getImageUrlsFromMail( Zend_Mail $mail, $context = null )
    {
        // check cache for context
        /** @var    $cache  Mage_Core_Model_Cache */
        $cache          = Mage::getSingleton('core/cache');
        $context_data   = null;
        $use_cache      = null !== $context && $cache->canUse(self::CACHE_TYPE);
        
        Mage::log(__CLASS__ . '::' . __FUNCTION__ . '(): use_cache = ' . var_export($use_cache, 1));
        
        if ( $use_cache )
        {
            $context_cache_id   = self::CACHE_TYPE . '-urls-' . (is_string($context) ? $context : md5(serialize($context)));
            $context_data       = $this->_getContextDataFromCache($context_cache_id);
        }
        
        if ( !$context_data )
        {
            $bodyHtml   = $this->_getBodyHtml($mail);
            $urls       = $this->_getImageUrlsFromBodyHtml($bodyHtml);
            
            // save URLs to cache, if context defined
            if ( $use_cache )
            {
                $isHtml = (boolean) $bodyHtml;

                $this->_saveContextDataToCache($context_cache_id, $isHtml, $urls);
            }
        }
        else
        {
            $urls = $context_data['is_html'] ? $context_data['urls'] : array();

            Mage::log('EmailImages - loaded URLs from cache (cache ID: ' . $context_cache_id . ')');
        }
        
        return $urls;
    }

    /**
     *  Retrieve image URL from email body HTML
     *
     *  @param  string  $bodyHtml       Email body HTML to use.
     *  @return array                   Array of image URLs.
     *
     *  @see    getRegularExpression(), getRegularExpressionIndex()
     */
    protected function _getImageUrlsFromBodyHtml( $bodyHtml )
    {
        $urls = array();

        Mage::log('EmailImages - parsing HTML body');

        if ( $bodyHtml )
        {
            // find image URLs in email HTML body
            $regexp = $this->getRegularExpression();
            if ( preg_match_all($regexp, $bodyHtml, $matches) )
            {
                $index  = $this->getRegularExpressionIndex();
                $urls   = $matches[$index];
                $urls   = array_unique($urls);
            }
            
            if ( 0 == count($urls) )    // no URLs in HTML body?
                Mage::log('EmailImages - no images found in email HTML body', Zend_Log::WARN);
        }
        else    // otherwise no HTML body?
        {
            Mage::log('EmailImages - no HTML body for email', Zend_Log::WARN);
        }
        
        return $urls;
    }

    /**
     *  Retrieve cached context data.
     *
     *  @param  string  $context_cache_id   Cached context data cache ID.
     *  @return array                       Array containing keys 'isHtml' to indicate a HTML body, 'urls' for image URLs from that HTML body.
     *
     *  @see    Mage_Core_Model_Cache::load()
     */
    protected function _getContextDataFromCache( $context_cache_id )
    {
        /** @var    $cache  Mage_Core_Model_Cache */
        $cache          = Mage::getSingleton('core/cache');
        $context_data   = $cache->load($context_cache_id);
        
        if ( $context_data )
            $context_data = unserialize($context_data);

        Mage::log(__CLASS__ . '::' . __FUNCTION__ . '(): context_data=' . print_r($context_data, 1));
        
        return $context_data;
    }

    /**
     *  Retrieve HTML body from mail object.
     *
     *  @param  Zend_Mail       $mail   Mail object instance to use.
     *  @return string|false            HTML body from the mail object instance, if any;
     *                                  false, otherwise.
     *
     *  @see    Zend_Mail::getBodyHtml(), Zend_Mime_Part::getContent()
     */
    protected function _getBodyHtml( Zend_Mail $mail )
    {
        $bodyHtmlObject = $mail->getBodyHtml();
        if ( $bodyHtmlObject instanceof Zend_Mime_Part )
        {
            $bodyHtml = $bodyHtmlObject->getContent();

            if ( $bodyHtmlObject->encoding == Zend_Mime::ENCODING_QUOTEDPRINTABLE )
                $bodyHtml = quoted_printable_decode($bodyHtml);
        }
        else
        {
            $bodyHtml = $bodyHtmlObject;
        }
        
        if ( !is_string($bodyHtml) )
        {
            Mage::log(__CLASS__ . '::' . __FUNCTION__ . '(): unsupported bodyHtml = ' . substr(var_export($bodyHtml, 1), 0, 128) . '...');

            $bodyHtml = false;
        }
        
        Mage::log(__CLASS__ . '::' . __FUNCTION__ . '(): bodyHtml = ' . ($bodyHtml ? preg_replace('/[\s\t\r\n\k]+/', ' ', substr($bodyHtml, 0, 128)) : '<empty>'));
        
        return $bodyHtml;
    }               

    /**
     *  Save context data to cache.
     *
     *  @param  string  $context_cache_id   Cache ID to use.
     *  @param  boolean $isHtml             Set to true if email body is HTML.
     *  @param  array   $urls               Array of image URLs from HTML body.
     *  @return void
     *
     *  @see    CACHE_TAG,
     *          getCacheTime(),
     *          Mage_Core_Model_Cache::save()
     */
    protected function _saveContextDataToCache( $context_cache_id, $isHtml, $urls )
    {
        /** @var    $cache  Mage_Core_Model_Cache */
        $cache          = Mage::getSingleton('core/cache');
        $cache_time     = $this->getCacheTime();

        $context_data   = array(
                                'urls'      => $urls,
                                'is_html'   => $isHtml,
                             );
        $context_data   = serialize($context_data);

        $cache->save($context_data, $context_cache_id, array( self::CACHE_TAG ), $cache_time);

        Mage::log('EmailImages - saved context URLs to cache');
    }
    
    /**
     *  Attach image URLs to the email.
     *
     *  @param  Zend_Mail   $mail       Zend_Mail instance to attach images to.
     *  @param  array       $urls       Array of image URLs to attach.
     *  @return void
     *
     *  @see    _retrieveImageData(),
     *          Zend_Mime::MULTIPART_RELATED,
     *          Zend_Mail::createAttachment(), Zend_Mail::setType(),
     *          Zend_Mime_Part
     */
    protected function _attachImageUrls( Zend_Mail $mail, array $urls )
    {
        foreach ( $urls as $index => $url )
        {
            if ( $url )
            {
                // retrieved image data
                $data = $this->_retrieveImageData($url);
                
                if ( $data )
                {
                    // attach image data
                    $mp             = $mail->createAttachment($data['image']
                                                                , $data['size']['mime']
                                                                , Zend_Mime::DISPOSITION_INLINE
                                                                , Zend_Mime::ENCODING_BASE64, basename($url)
                                                              );
                    $mp->id         = md5($url);
                    $mp->location   = $url;
                }
                else
                {
                    Mage::log('EmailImages - unable to retrieve image from URL ' . $url, Zend_Log::WARN);
                    
                    // remove images that failed to load
                    UnSet($urls[$index]);
                }
            }
        }
    
        // set Content-Type to multipart/related to properly display the images inline, if any
        if ( 0 < count($urls) )
            $mail->setType(Zend_Mime::MULTIPART_RELATED);
    }
    
    /**
     *  Retrieve image data from URL.
     *  NB: uses file_get_contents().
     *
     *  @TODO   should we try to use curl if available? should that be configurable by admin?
     * 
     *  @param  string      $url        URL to retrieve image data from.
     *  @return array|false             Array with keys 'image' for image binary, 'size' for image size, if successfully retrieved image from URL;
     *                                  false, otherwise.
     *
     *  @see    CACHE_TAG, CACHE_TYPE,
     *          getCacheTime(),
     *          Mage_Core_Model_Cache::canUse(), Mage_Core_Model_Cache::load(), Mage_Core_Model_Cache::save(),
     *          file_get_contents()
     */
    protected function _retrieveImageData( $url )
    {
        // retrieve image from cache or URL
        /** @var    $cache  Mage_Core_Model_Cache */
        $cache      = Mage::getSingleton('core/cache');
        $use_cache  = $cache->canUse(self::CACHE_TYPE);
        $data       = false;

        if ( $use_cache )
        {
            $cache_id   = self::CACHE_TYPE . $url;
            $data       = $cache->load(self::CACHE_TYPE . $url);

            if ( $data )
                $data = unserialize($data);
        }
        
        if ( !$data )
        {
            // retrieve image data from URL
            Mage::log('EmailImages - loading image from URL ' . $url);

            $data = file_get_contents($url);
            if ( $data )
            {
                $data = array(
                                'image' => $data,
                                'size'  => getimagesize($url),
                             );
            }

            // save retrieved image data to cache even if retrieving the image failed, if allowed
            if ( $use_cache )
            {
                $serialized_data    = serialize($data);
                $cache_time         = $this->getCacheTime();

                $cache->save($serialized_data, $cache_id, array( self::CACHE_TAG ), $cache_time);

                Mage::log('EmailImages - saved image to cache');
            }
        }
        else
        {
            Mage::log('EmailImages - loaded image from cache');
        }
        
        return $data;
    }
}
