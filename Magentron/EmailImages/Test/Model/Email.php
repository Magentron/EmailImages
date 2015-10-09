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
class Magentron_EmailImages_Test_Model_Email extends EcomDev_PHPUnit_Test_Case
{
    /** Email type: text or html
     *  @var    string
     */
    protected $_type = null;

    /** Mail mock object instance.
     *  @var    Magentron_EmailImages_Model_Mail
     */
    protected $_mail = null;


    /**
     *  Test send()
     *
     *  @test
     *  @loadFixture
     *  @doNotIndexAll
     *
     *  @see    $_type,
     *          send(),
     *          Magentron_EmailImages_Model_Mail_send()
     */
    public function testSend()
    {
        /** @var    $email  Magentron_EmailImages_Model_Email */
        $email          = Mage::getModel('core/email');
        $this->assertEquals('Magentron_EmailImages_Model_Email', get_class($email));
        
        foreach ( array('html', 'text') as $type )
        {
            // prevent mail from actually being sent and call assert functions
            /** @var    $mailMock   PHPUnit_Framework_MockObject_MockObject */
            $this->_mail    = // continue below
            $mock           = $this->getModelMock('emailimages/mail', array('send'));
    
            $mock->expects($this->any())
                    ->method('send')
                    ->will($this->returnCallback(array($this, 'Magentron_EmailImages_Model_Mail_send')));
    
            $this->replaceByMock('model', 'emailimages/mail', $mock);
            
            $email = Mage::getModel('emailimages/email');
            $email->setFromEmail(   Mage::getStoreConfig('trans_email/ident_general/email'))
                    ->setFromName(  Mage::getStoreConfig('trans_email/ident_general/name'))
                    ->setToEmail(   Mage::getStoreConfig('trans_email/ident_support/email'))
                    ->setToName(    Mage::getStoreConfig('trans_email/ident_support/name'))
                    ->setSubject('Test subject');

            $this->_type = $type;
            
            $email->setType($type);
            
            switch ( $type )
            {
                case 'html':
                    $email->setBody('<html><body><img src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/default/default/images/logo.gif" /></body></html>');
                    break;
                    
                case 'text':
                    $email->setBody('This is a test email.');
                    break;
            }
                    
            $email->send();
        }

    }


    /**
     *  Test send() with sending mail disabled
     *
     *  @test
     *  @loadFixture
     *  @doNotIndexAll
     */
    public function testSendDisabled()
    {
        /** @see: http://stackoverflow.com/a/18701405/832620 */
        Mage::app()->getStore(0)
            ->setConfig('system/smtp/disable', 1);

        $this->testSend();  
    }


    /**
     *  Magetron_EmailImages_Model_Mail function send() replacement function.
     *
     *  @see    testSend(), send(),
     *          Magentron_EmailImages_Model_Mail::send()
     */
    public function Magentron_EmailImages_Model_Mail_send( $transport = null )
    {
        // setup mail instance similar to original one in $this->_mail
        $mail = new Magentron_EmailImages_Model_Mail($this->_mail->getCharset());
        $mail->setHeaderEncoding($this->_mail->getHeaderEncoding())
                ->setMimeBoundary($this->_mail->getMimeBoundary())
                ->setDate($this->_mail->getDate())
                ->setFrom($this->_mail->getFrom(), $this->_mail->getFrom())
                ->setReplyTo($this->_mail->getReplyTo(), $this->_mail->getReplyTo())
                ->addTo(array_slice($this->_mail->getRecipients(), 0, 1));
                
        switch ( $this->_type )
        {
            case 'html':
                $mail->setBodyHtml(quoted_printable_decode($this->_mail->getBodyHtml(true)));
                break;
                
            case 'text':
                $mail->setBodyText($this->_mail->getBodyText(true));
                break;
        }

        return $mail->send($this);
    }
    
    /**
     *  Zend_Mail_Transport function send()
     *
     *  @see    $_type,
     *          Magentron_EmailImages_Model_Mail_send(),
     *          Zend_Mail::send()
     */
    public function send( $mail )
    {
        if ( Mage::getStoreConfig('system/smtp/disable') )
        {
            $this->fail('send() should not be called when the sending mail has been disabled');
        }
        else
        {
            switch ( $this->_type )
            {
                case 'html':
                    $this->assertEquals(1, $mail->getPartCount(), 'HTML email should have 1 attachment #');
                    break;
                
                case 'text':
                    $this->assertEquals(0, $mail->getPartCount(), 'Text email should have no attachments #');
                    break;
            }
        }
    }
}
