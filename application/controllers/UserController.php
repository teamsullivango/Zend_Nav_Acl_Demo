<?php
class UserController extends Zend_Controller_Action
{

    public function init()
    {
    	$auth = Zend_Auth::getInstance();
    	if($auth->hasIdentity())
    	{
    		$this->view->identity = $auth->getIdentity();
    		if (Model_User::needsPasswordReset($auth->getIdentity()->username))
    		{
    			$this->view->needsReset = '<p>You need to reset your password. <a href="/user/password/user_id/' . Model_User::getUser_id($auth->getIdentity()->username) . '">Click Here Reset Your Password.</a>';
    		}
    	}
    }

    public function indexAction()
    {
       
    }

    public function createAction()
    {
    	$userForm = new Form_User();
    	$userForm->removeElement('username');
    	$userForm->removeElement('password_1');
    	$userForm->removeElement('password_2');
    	if ($this->_request->isPost())
    	{
    		if($userForm->isValid($_POST))
    		{
    			$userModel = new Model_User();
    			$userModel->createUser(
    				$userForm->getValue('email'),
    				$userForm->getValue('first_name'), 
    				$userForm->getValue('last_name'),
    				$userForm->getValue('role')
    			);
    			return $this->_forward('list');    			
    		}
    	}
    	$userForm->setAction('/user/create');
    	$this->view->form = $userForm;
    }

    public function listAction()
    {
    	$currentUsers = Model_User::getUsers();
    	if ($currentUsers->count() > 0 )
    	{
    		$this->view->users = $currentUsers;
    	}
    	else 
    	{
    		$this->view->users = null;
    	}
    	
    }

    public function updateAction()
    {
        $userForm = new Form_User();
        $userForm->setAction('/user/update');
        $userForm->removeElement('password_1');
        $userForm->removeElement('password_2');
        $userModel = new Model_User();
        if($this->_request->isPost())
        {
        	if($userForm->isValid($_POST)) 
        	{
        		$userModel->updateUser(
        			$userForm->getValue('user_id'),
	        		$userForm->getValue('username'),
        			$userForm->getValue('email'),
	        		$userForm->getValue('first_name'),
	        		$userForm->getValue('last_name'),
	        		$userForm->getValue('role')
        		);
        		return $this->_forward('list');
        	}
        }
        else
        {
        	$user_id = $this->_request->getParam('user_id');
        	$currentUser = $userModel->find($user_id)->current();
        	$userForm->populate($currentUser->toArray());
        }
        $this->view->form = $userForm;
    }

    public function passwordAction()
    {
    	$passwordForm = new Form_User();
    	$passwordForm->removeElement('user_id');
    	$passwordForm->removeElement('first_name');
    	$passwordForm->removeElement('last_name');
    	$passwordForm->removeElement('username');
    	$passwordForm->removeElement('email');
    	$passwordForm->removeElement('role');
    	
    	$passwordForm->getElement('password_1')->setLabel('New Password');
    	$passwordForm->getElement('password_2')->setLabel('Confirm New Password');

    	// password_old textbox
    	$password_old = new Zend_Form_Element_Password('password_old');
    	$password_old->setLabel('Old Password: ');
    	$password_old->setRequired(TRUE);
    	$password_old->setAttrib('size', 50);
    	$password_old->setAttrib('maxlength', '250');
    	$password_old->setOrder(0);
    	$passwordForm->addElement($password_old);
    	
    	$user_id = $this->_request->getParam('user_id');
    	if ( ($user_id != FALSE) && Model_User::userExists($user_id))
    	{
    		if ($this->_request->isPost() && $passwordForm->isValid($_POST))
    		{
   				$userModel = new Model_User();
   				$currentUser = $userModel->find($user_id)->current();
   				
   				$old_password = $passwordForm->getValue('password_old');
   				$new_password = $passwordForm->getValue('password_1');    				
   				
   				$db = Zend_Db_Table::getDefaultAdapter();
   				$authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'user_id', 'password');
   				$authAdapter->setIdentity($currentUser->user_id);
   				$authAdapter->setCredential(Model_User::encryptPassword($old_password, $currentUser->salt));
   				$result = $authAdapter->authenticate();

   				if ($result->isValid())
   				{
   					$userModel->updatePassword(
    					$currentUser->user_id,
    					$new_password
   					);
   					
   					return $this->view->confirmation = 'Your password was successfully updated. <a href="/user/">Return to Welcome Screen</a>';
   				}
   				else 
   				{
   					return $this->view->confirmation = "Your password wasn't updated";
   				}
  			}
  			else 
  			{
  				$passwordForm->setAction('/user/password/user_id/' . $user_id);  				   				
   				$this->view->form = $passwordForm;
   			}
    	}
    	else 
    	{
    		$this->_forward('list', 'user');
    	}
    }

    public function deleteAction()
    {
    	$user_id = $this->_request->getParam('user_id');
    	$userModel = new Model_User();
    	$userModel->deleteUser($user_id);
    	return $this->_forward('list');
    }

    public function loginAction()
    {
        $userForm = new Form_User();
        $userForm->setAction('/user/login');
        $userForm->removeElement('first_name');
        $userForm->removeElement('last_name');
        $userForm->removeElement('role');
        $userForm->removeElement('password_2');
        $userForm->removeElement('email');

        $user_validator = new Zend_Validate_Db_RecordExists(
			array(
        		'table' => 'users',
        		'field' => 'username'
        	)
        );
        $userForm->getElement('username')->addValidator($user_validator);
        
        if ($this->_request->isPost() && $userForm->isValid($_POST))
        {
        	$guestPassword = $userForm->getValue('password_1');
        	$guestUsername = $userForm->getValue('username');
      		$user_salt = Model_User::getSalt($guestUsername);       		 
       		$db = Zend_Db_Table::getDefaultAdapter();
       		$authAdapter = new Zend_Auth_Adapter_DbTable($db, 'users', 'username', 'password');
       		$authAdapter->setIdentity($guestUsername);
       		$authAdapter->setCredential(Model_User::encryptPassword($guestPassword, $user_salt));
       		$result = $authAdapter->authenticate();
       		
       		if($result->isValid())
       		{
       			$auth = Zend_Auth::getInstance();
       			$storage = $auth->getStorage();
       			$storage->write($auth->getIdentity());
       			$storage->write($authAdapter->getResultRowObject(array(
       						'username', 
       						'first_name', 
       						'last_name',
       						'role'
       			)));

				return $this->_forward('index');
       		}
       		else
       		{
       			$userForm->getElement('password_1')->addError('incorrect password');
       		}       	
        }
        elseif (Zend_Registry::isRegistered('identity'))
        {
        	return $this->_forward('index');
        }
        $this->view->form = $userForm;        
    }

    public function logoutAction()
    {
    	$authAdapter = Zend_Auth::getInstance();
    	$authAdapter->clearIdentity();
    	Zend_Registry::set('identity', null);
    }
}



