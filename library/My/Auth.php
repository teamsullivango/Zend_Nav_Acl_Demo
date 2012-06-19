<?php
class My_Auth
	extends	Zend_Controller_Plugin_Abstract 
{ 
	private $_acl = null;
	 
	public function __construct(Zend_Acl $acl) 
	{
		$this->_acl = $acl;
	}
	
	public function preDispatch(Zend_Controller_Request_Abstract $request) 
	{		
		$resource =	$request->getControllerName(); 
		$action = $request->getActionName();
		$role = 'guest';
		$auth = Zend_Auth::getInstance();
		
		if ( !$auth->getStorage()->isEmpty() )
		{
			$identity = $auth->getStorage()->read();
			$role = $identity->role;
			Zend_Registry::set('identity', $identity);
		}

		$role = strtolower($role);
		if( !$this->_acl->isAllowed($role, $resource, $action) ) 
		{
			if ($role == 'guest')
			{
				$request->setControllerName('user');
				$request->setActionName('login');
			}
			else
			{
				$request->setControllerName('error');
				$request->setActionName('noauth');
			}
		}
	}
	
	public function postDispatch(Zend_Controller_Request_Abstract $request)
	{
		$auth = Zend_Auth::getInstance();
		$storage = $auth->getStorage();
		if (Zend_Registry::isRegistered('identity'))
		{
			$auth = Zend_Auth::getInstance();
			$storage = $auth->getStorage();
			$storage->write(Zend_Registry::get('identity'));
		}		
	}
}
