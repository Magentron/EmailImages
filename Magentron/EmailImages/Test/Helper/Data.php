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
class Magentron_EmailImages_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
	/**
	 *	Test isEnabled() for enabled
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function isEnabled()
	{
		$enabled = Mage::helper('emailimages')->isEnabled();
		$this->assertTrue($enabled);
	}

	/**
	 *	Test isEnabled() for disabled
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function isEnabledFalse()
	{
		$enabled = Mage::helper('emailimages')->isEnabled();
		$this->assertFalse($enabled);
	}

	/**
	 *	Test getCacheTime() for valid config value
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function getCacheTime()
	{
		$value = Mage::helper('emailimages')->getCacheTime();
		$this->assertEquals($this->expected('cache_time')->getValid(), $value);
	}
	
	/**
	 *	Test getCacheTime() for invalid config value
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function getCacheTimeInvalid()
	{
		$value = Mage::helper('emailimages')->getCacheTime();
		$this->assertEquals(Magentron_EmailImages_Helper_Data::DEFAULT_CACHE_TIME, $value);
	}

	/**
	 *	Test getRegularExpression() for valid config value
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function getRegularExpression()
	{
		$value = Mage::helper('emailimages')->getRegularExpression();
		$this->assertEquals($this->expected('regexp')->value, $value);
	}
	
	/**
	 *	Test getRegularExpression() for invalid config value
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function getRegularExpressionInvalid()
	{
		$value = Mage::helper('emailimages')->getRegularExpressionIndex();
		$this->assertEquals(Magentron_EmailImages_Helper_Data::DEFAULT_REGEXP_INDEX, $value);
	}
	
	/**
	 *	Test getRegularExpressionIndex() for valid config value
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function getRegularExpressionIndex()
	{
		$value = Mage::helper('emailimages')->getRegularExpressionIndex();
		$this->assertEquals($this->expected('regexp')->index, $value);
	}

	/**
	 *	Test getRegularExpressionIndex() for invalid config value
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function getRegularExpressionIndexInvalid()
	{
		$value = Mage::helper('emailimages')->getRegularExpressionIndex();
		$this->assertEquals(Magentron_EmailImages_Helper_Data::DEFAULT_REGEXP_INDEX, $value);
	}
	
	/**
	 *	Test addImages()
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function addImages()
	{
		foreach ( array('HTML', 'Html', 'html', 'text') as $type )
		{
			foreach ( array(null, 'test-', 'test-') as $context )
			{
				if ( $context )
				{
		    		$context .= $type . '-' . md5($context . $type);
				}

				$mail = new Zend_Mail();
				$mail->setFrom(	Mage::getStoreConfig('trans_email/ident_general/email'))
						->addTo(Mage::getStoreConfig('trans_email/ident_support/email'))
						->setSubject('Test subject');

				switch ( $type )
				{
					case 'HTML':
						$mail->setBodyHtml('<html><body>This is HTML with images.</body></html>');
						break;
						
		    		case 'Html':
				    	$mail->setBodyHtml('<html><body><img src="http://does.not.exits/does-not-exist.jpg" /></body></html>');
				    	break;
		    		
					case 'html':
						$mail->setBodyHtml('<html><body><img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/default/default/images/logo.gif" /></body></html>');
						break;
						
					case 'text':
						$mail->setBodyText('This is a test email.');
						break;
				}

				Mage::helper('emailimages')->addImages($mail, $context);
				
		   		switch ( $type )
		   		{
		   			case 'html':
		   				if ( Mage::getStoreConfig('system/emailimages/enable') )
				   			$this->assertEquals(1, $mail->getPartCount(), 'HTML email should have 1 attachment');
				   		else
				   			$this->assertEquals(0, $mail->getPartCount(), 'HTML email should have no attachment if EmailImages has been disabled');
				   		break;
		
		   			case 'HTML':
					case 'Html':
				   	case 'text':
				   		$this->assertEquals(0, $mail->getPartCount(), ucfirst($type) . ' email should have no attachments');
				   		break;
		   		}
			}
		}
	}
	
	/**
	 *	Test addImages() with cache turned on
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function addImagesCache()
	{
		$this->_overrideCacheCanUse();

		$this->addImages();
	}
	
	/**
	 *	Test addImages() with the EmailImages extension disabled
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function addImagesDisabled()
	{
		$this->_overrideCacheCanUse();

		Mage::helper('emailimages')->addImages(new Zend_Mail());
	}
	
	/**
	 *	Test addImages() with exception thrown
	 *
	 *	@test
	 *	@loadFixture
     *	@doNotIndexAll
	 */
	public function addImagesException()
	{
		$this->_overrideCacheCanUse();

		Mage::helper('emailimages')->addImages(new Zend_Mail(), 'test');
	}
	
	/**
	 *	Test cleanCache()
	 *
	 *	@test
     *	@doNotIndexAll
	 */
	public function cleanCache()
	{
		Mage::helper('emailimages')->cleanCache();
	}

	/**
	 *	Mage_Core_Model_Cache function canUse() replacement function.
	 *
	 *	@param	string	$typeCode
	 *	@return	boolean
	 *
	 *	@see	addImagesCache(), addImagesDisabled(), addImagesException(),
	 *			Mage_Core_Model_Cache::canUse()
	 */
	public function Mage_Core_Model_Cache_canUse( $typeCode )
	{
		if ( Mage::getStoreConfig('system/emailimages/enable') )
		{
	    	if ( Mage::getStoreConfig('test/emailimages/use_cache') )
	    	{
	    		if ( Magentron_EmailImages_Helper_Data::CACHE_TYPE == $typeCode )
	    		{
	    			return true;
	    		}
	    	}
	    	elseif ( Mage::getStoreConfig('test/emailimages/throw_exception') )
	    	{
				// when the extension has been enabled, we want to test an exception
				throw new Exception('dummy exception to get 100% code coverage on addImages()');
	    	}
		}
		else
		{
			// when the extension has been disabled, getType() should not be called		
			$this->fail('canUse() should not be called when the EmailImages extension has been disabled');
		}
    			
		return false;
	}

	/**
	 *	Run addImages() with Mage_Core_Model_Cache::canUse() overriden.
	 */
	protected function _overrideCacheCanUse()
	{
		$mock = $this->getModelMock('core/cache', array('canUse'));

		$mock->expects($this->any())
				->method('canUse')
				->will($this->returnCallback(array($this, 'Mage_Core_Model_Cache_canUse')));

		$this->replaceByMock('model', 'core/cache', $mock);
	}
}