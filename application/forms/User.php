<?php
class Form_User extends Zend_Form
{
	public function init()
	{
		$this->setMethod('post');
		
		// user_id number
		$user_id = new Zend_Form_Element_Hidden('user_id');
		$this->addElement($user_id);

		// username textbox
		$user = new Zend_Form_Element_Text('username');
		$user->setLabel('Username:');
		$user->setRequired(TRUE);
		$user->addFilters(array('StripTags','StringTrim'));
		$user->addErrorMessage('The username is required');
		$user->setAttrib('size', 50);
		$this->addElement($user);
		
		// email textbox
		$email = new Zend_Form_Element_Text('email');
		$email->setLabel('Email address: ');
		$email->setRequired(TRUE);
		$email->addFilters(array('StripTags','StringTrim'));
		$email->addValidator('EmailAddress', TRUE);
		$email->setAttrib('size', 50);
		$this->addElement($email);
		
		// password_1 textbox
		$password_1 = new Zend_Form_Element_Password('password_1');
		$password_1->setLabel('Your Password: ');
		$password_1->setRequired(TRUE);
		$password_1->setAttrib('size', 50);
		$password_1->setAttrib('maxlength', '250');
		$this->addElement($password_1);
		
		// password_2 textbox
		$password_2 = new Zend_Form_Element_Password('password_2');
		$password_2->setLabel('Confirm Password: ');
		$password_2->setRequired(TRUE);
		$password_2->setAttrib('size', 50);
		$password_2->setAttrib('maxlength', '250');
		$password_2->addValidator('identical', FALSE, array('token' => 'password_1'));
		$this->addElement($password_2);
		
		// first name textbox
		$firstName = new Zend_Form_Element_Text('first_name');
		$firstName->setLabel('First Name: ');
		$firstName->setRequired(TRUE);
		$firstName->addFilters(array('StripTags','StringTrim'));
		$firstName->setAttrib('size', 50);
		$this->addElement($firstName);

		// last name textbox
		$lastName = new Zend_Form_Element_Text('last_name');
		$lastName->setLabel('Last Name: ');
		$lastName->setRequired(TRUE);
		$lastName->addFilters(array('StripTags','StringTrim'));
		$lastName->setAttrib('size', 50);
		$this->addElement($lastName);
		
		// role select box
		$role = new Zend_Form_Element_Select('role');
/**
 * @todo use the acl roles definitions to populate this list
 */
		$role->setLabel('Select Role: ');
		$role->setRequired(TRUE);
		$role->addMultiOptions(array(
			'Member'	=>	'Member',
			'Admin'		=>	'Admin'	
		));
		$this->addElement($role);
						
		// submit button
		$submit = new Zend_Form_Element_Submit('submit');
		$submit->setLabel('Submit');
		$this->addElement($submit);
	}
}