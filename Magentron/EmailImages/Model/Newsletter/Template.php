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
class Magentron_EmailImages_Model_Newsletter_Template extends Mage_Newsletter_Model_Template
{
    /**
     *	Retrieve mail object instance
     *
     *	@return Magentron_EmailImages_Model_Mail
     *
     *	@see	$_mail
     */
	public function getMail()
	{
        if (is_null($this->_mail)) {
            $this->_mail = Mage::getModel('emailimages/mail', 'utf-8');
        }
        return $this->_mail;
	}
}
