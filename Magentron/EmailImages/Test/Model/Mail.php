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
class Magentron_EmailImages_Test_Model_Mail extends EcomDev_PHPUnit_Test_Case
{
    /**
     *	Test setContent()
     *
     *	@test
     *	@doNotIndexAll
     */
    public function testContext()
    {
    	/** @var	$mail	Magentron_EmailImages_Model_Mail */
    	$mail		= Mage::getModel('emailimages/mail');
    	$context	= md5(uniqid('', 1));

        $mail->setContext($context);

    	$this->assertEquals('Magentron_EmailImages_Model_Mail', get_class($mail), 'emailimages/mail');
        $this->assertEquals($context, $mail->getContext(), 'context');
        
        return $this;
    }
    
    
    /**
     *	Test send()
     *
     *	test
     *	@loadFixture
     *	@doNotIndexAll
     */
    public function testSend()
    {
    	/** @var	$mail	Magentron_EmailImages_Model_Mail */
    	$mail = Mage::getModel('emailimages/mail')
    				->setFrom(	Mage::getStoreConfig('trans_email/ident_general/email'))
    				->addTo(	Mage::getStoreConfig('trans_email/ident_support/email'))
    				->setSubject('Test subject')
    				->setContext('test-html-' . md5('test-html'))
    				->setBodyHtml('<html><body><img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/default/default/images/logo.gif" /></body></html>');

		// set transport to $this to prevent actually sending mail and calling assert functions
		$mail->send($this);
    }
    
    
    /**
     *	Zend_Mail_Transport function send()
     */
   	public function send( $mail )
   	{
		$this->assertEquals(1, $mail->getPartCount(), 'HTML email should have 1 attachment');
   	}
}