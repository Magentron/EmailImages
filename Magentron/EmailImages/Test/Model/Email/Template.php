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
class Magentron_EmailImages_Test_Model_Email_Template extends EcomDev_PHPUnit_Test_Case
{
	/**
	 *	Test getMail() function
	 *
	 *	@test
	 *	@doNotIndexAll
	 */
	public function getMail()
	{
		// model for email/template should have been rewritten as Magentron_EmailImages_Model_Email_Template
		$template = Mage::getModel('core/email_template');
		$this->assertEquals('Magentron_EmailImages_Model_Email_Template', get_class($template), 'core/email_template model');
		
		// at the beginning the protected variable $_mail should be null
		$variables = (array) $template;
		$this->assertFalse(IsSet($variables['\x00*\x00_mail']), 'variable $_mail should be null');
		
		// when called it should return a Magentron_EmailImages_Model_Mail object instance
		$mail = $template->getMail();
		$this->assertEquals('Magentron_EmailImages_Model_Mail', get_class($mail), 'returned mail object instance');
		
		// at the end the protected variable $_mail should be equal to the returned value
		$variables = (array) $template;
		$this->assertEquals($mail, $variables["\x00*\x00_mail"], 'variable $_mail should be equal to the returned mail object instance');
	}
}