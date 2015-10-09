# EmailImages

<a href="https://codeclimate.com/github/Magentron/EmailImages"><img src="https://codeclimate.com/github/Magentron/EmailImages/badges/gpa.svg" /></a>

###### A magento extension to automatically attach images to emails for inline display.

This Magento extension allows images that are used in the email (HTML) content to be attached to the email so that they are displayed inline without having to depend on the user to click a button like 'Show Remote Content' to display these images.

Obvious advantage is that it is more probable that the email is rendered as you would expect, obvious disadvantage is that the emails sent are bigger in size because they include the attached images. It is up to you to use properly sized images.

This extension works nicely well with the <a href="http://dot.collective.ro/magento-output/magento-send-a-test-newsletter/">DC_Newsletter</a> extension which allows you to send a test newsletter to yourself.

Includes full PHPUnit test for <a href="https://github.com/EcomDev/EcomDev_PHPUnit">Ecomdev_PHPUnit</a>, 100% code coverage. API documentation can be found at http://www.magentron.com/apidoc/magentron_emailimages/index.html and PHPUnit test coverage report at <a href="http://www.magentron.com/report/magentron_emailimages/.modman_magentron_emailimages_Magentron_EmailImages.html">http://www.magentron.com/report/magentron_emailimages/.modman_magentron_emailimages_Magentron_EmailImages.html</a>

NB: Currently not all email readers support the CSS background-image url functionality. 
NB: This extension currently does not work properly with other modules that override email behavior such as <a href="http://www.magentocommerce.com/magento-connect/smtp-pro-email-free-custom-smtp-email.html">ASchroder_SMTPPro</a> and the likes! This is due to the architecture of Magento, and we are open to work with the creators of these extensions to integrate EmailImages.
