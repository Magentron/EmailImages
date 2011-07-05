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
class Magentron_EmailImages_Model_Email extends Mage_Core_Model_Email
{
    public function send()
    {
        if (Mage::getStoreConfigFlag('system/smtp/disable')) {
            return $this;
        }

        /** @TODO check if it would be safe to set template as context on mail instance */
        /** @var	$mail	Magentron_EmailImages_Model_Mail */
        $mail = Mage::getModel('emailimages/mail');

        if (strtolower($this->getType()) == 'html') {
        	$mail->setBodyHtml($this->getBody());
        }
        else {
            $mail->setBodyText($this->getBody());
        }
        
        $mail->setFrom($this->getFromEmail(), $this->getFromName())
            ->addTo($this->getToEmail(), $this->getToName())
            ->setSubject($this->getSubject());
        $mail->send();

        return $this;
    }
}