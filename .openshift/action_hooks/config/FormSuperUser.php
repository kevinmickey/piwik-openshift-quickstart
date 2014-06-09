<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use HTML_QuickForm2_DataSource_Array;
use HTML_QuickForm2_Factory;
use HTML_QuickForm2_Rule;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\UsersManager;
use Piwik\QuickForm2;

/**
 *
 */
class FormSuperUser extends QuickForm2
{
    function __construct($id = 'generalsetupform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes = array('autocomplete' => 'off'), $trackSubmit);
    }

    function init()
    {
        HTML_QuickForm2_Factory::registerRule('checkLogin', 'Piwik\Plugins\Installation\Rule_isValidLoginString');
        HTML_QuickForm2_Factory::registerRule('checkEmail', 'Piwik\Plugins\Installation\Rule_isValidEmailString');

        $login = $this->addElement('text', 'login')
            ->setLabel(Piwik::translate('Installation_SuperUserLogin'));
        $login->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_SuperUserLogin')));
        $login->addRule('checkLogin');

        $password = $this->addElement('password', 'password')
            ->setLabel(Piwik::translate('Installation_Password'));
        $password->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_Password')));
        $pwMinLen = UsersManager::PASSWORD_MIN_LENGTH;
        $pwMaxLen = UsersManager::PASSWORD_MAX_LENGTH;
        $pwLenInvalidMessage = Piwik::translate('UsersManager_ExceptionInvalidPassword', array($pwMinLen, $pwMaxLen));
        $password->addRule('length', $pwLenInvalidMessage, array('min' => $pwMinLen, 'max' => $pwMaxLen));

        $passwordBis = $this->addElement('password', 'password_bis')
            ->setLabel(Piwik::translate('Installation_PasswordRepeat'));
        $passwordBis->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_PasswordRepeat')));
        $passwordBis->addRule('eq', Piwik::translate('Installation_PasswordDoNotMatch'), $password);

        $email = $this->addElement('text', 'email')
            ->setLabel(Piwik::translate('Installation_Email'));
        $email->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_Email')));
        $email->addRule('checkEmail', Piwik::translate('UsersManager_ExceptionInvalidEmail'));

        $this->addElement('checkbox', 'subscribe_newsletter_security', null, array(
                                                                                  'content' => '&nbsp;&nbsp;' . Piwik::translate('Installation_SecurityNewsletter'),
                                                                             ));

        $this->addElement('checkbox', 'subscribe_newsletter_community', null, array(
                                                                                   'content' => '&nbsp;&nbsp;' . Piwik::translate('Installation_CommunityNewsletter'),
                                                                              ));

        $this->addElement('submit', 'submit', array('value' => Piwik::translate('General_Next') . ' »', 'class' => 'submit'));

        // default values
        $this->addDataSource(new HTML_QuickForm2_DataSource_Array(array(

                                                                       'login' => 'OpenShiftSuperUser',
                                                                       'email' => 'user@example.com',
                                                                       'subscribe_newsletter_community' => 1,
                                                                       'subscribe_newsletter_security'  => 1,
                                                                  )));
    }
}

/**
 * Login id validation rule
 *
 */
class Rule_isValidLoginString extends HTML_QuickForm2_Rule
{
    function validateOwner()
    {
        try {
            $login = $this->owner->getValue();
            if (!empty($login)) {
                Piwik::checkValidLoginString($login);
            }
        } catch (\Exception $e) {
            $this->setMessage($e->getMessage());
            return false;
        }
        return true;
    }
}

/**
 * Email address validation rule
 *
 */
class Rule_isValidEmailString extends HTML_QuickForm2_Rule
{
    function validateOwner()
    {
        return Piwik::isValidEmailString($this->owner->getValue());
    }
}