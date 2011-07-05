<?php
/**
 *	Magentron EmailImages Extension
 *
 *	@category	Magentron
 *	@package	Magentron_EmailImages
 *	@author		Jeroen Derks
 *	@copyright	Copyright (c) 2011 Jeroen Derks http://www.magentron.com
 *	@license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Magentron_EmailImages_Model_Mail extends Zend_Mail
{
	/**
	 *	Ensure images are attached only once.
	 *	@var	boolean
	 */
	protected $_hasAddedImages = false;

	/**
	 *	String representation for the context of the send() call.
	 *	When using the same context, determining image URLs from email body
	 *	and retrieving these will be cached.
	 *	@var	string
	 */
	protected $_context = null;


	/**
	 *	Set context
	 *
	 *	@param	string	$context	Context to use.
	 *	@return	Zend_Mail			Provides fluent interface.
	 *
	 *	@see	$_context
	 */
	public function setContext( $context )
	{
		$this->_context = $context;
		return $this;
	}


	/**
	 *	Get context
	 *
	 *	@return	string				Context in use.
	 *
	 *	@see	$_context
	 */
	public function getContext()
	{
		return $this->_context;
	}


	/**
	 *	Overriden to attach images from HTML body, if any.
	 *
	 *	@param	Zend_Mail_Transport_Abstract	$transport
     *	@return	Zend_Mail									Provides fluent interface
     *
     *	@see	$_hasAddedImages,
     *			Magentron_EmailImages_Helper_Data,
     *			Zend_Mail::send()
     */
    public function send( $transport = null )
    {
    	if ( !$this->_hasAddedImages )
    	{
			Mage::helper('emailimages')->addImages($this, $this->_context);
			$this->_hasAddedImages = true;
    	}
    	
        return parent::send($transport);
    }
}