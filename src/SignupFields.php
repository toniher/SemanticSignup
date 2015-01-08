<?php

namespace SES;

use SemanticSignupSettings as Settings;

use Parser;

/**
 * @license GNU GPL v3+
 * @since 1.0
 *
 * @author Serhii Kutnii
 */
class SignupFields {

	/**
	 * @var UserFieldsCreateTemplate
	 */
	private $userFieldsCreateTemplate;

	/**
	 * @since 1.0
	 *
	 * @param UserFieldsCreateTemplate $userFieldsTemplate
	 */
	public function __construct( UserFieldsCreateTemplate $userFieldsCreateTemplate ) {
		$this->userFieldsCreateTemplate = $userFieldsCreateTemplate;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $arguments
	 */
	public function parse( $arguments ) {

		$parser = array_shift( $arguments );
		global $wgEnableEmail, $wgAllowRealName, $wgEmailConfirmToEdit, $wgAuth, $wgUser;

		$this->userFieldsCreateTemplate->set( 'link', '' ); // TODO
		$this->userFieldsCreateTemplate->set( 'email', '' ); // TODO
		$this->userFieldsCreateTemplate->set( 'createemail', $wgEnableEmail && $wgUser->isLoggedIn() );
		$this->userFieldsCreateTemplate->set( 'userealname', $wgAllowRealName );
		$this->userFieldsCreateTemplate->set( 'useemail', $wgEnableEmail );
		$this->userFieldsCreateTemplate->set( 'emailrequired', $wgEmailConfirmToEdit );
		$this->userFieldsCreateTemplate->set( 'canreset', $wgAuth->allowPasswordChange() );
		// $this->userFieldsCreateTemplate->set( 'remember', $wgUser->getOption( 'rememberpassword' )  );

		global $wgLoginLanguageSelector;
		# Prepare language selection links as needed
		if ( $wgLoginLanguageSelector ) {
			$this->userFieldsCreateTemplate->set( 'languages', $this->makeLanguageSelector() ); // FIXME: $this is not accessible in a static context
		}

		// Give authentication and captcha plugins a chance to modify the form
		$type = 'signup';
		$wgAuth->modifyUITemplate( $this->userFieldsCreateTemplate, $type );

		if( Settings::get( 'useCaptcha' ) && isset( $GLOBALS['wgCaptchaClass'] ) ) {
			$captchaObject = new $GLOBALS['wgCaptchaClass'];
			$captchaObject->injectUserCreate( $this->userFieldsCreateTemplate );
		}

		ob_start();
		$this->userFieldsCreateTemplate->execute();
		$text = ob_get_clean();

		return array(
			$text,
			'noparse' => true,
			'isHTML' => true
		);
	}

}
