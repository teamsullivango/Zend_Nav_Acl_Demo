<?php
class My_Acl
	extends Zend_Acl
{
	public function __construct()
	{
		//declare each of the site's controllers
		$this->addResource('docs')
			->addResource('error')
			->addResource('index')
			->addResource('user');
	
	
		//declare each of the site's users and their permissions
		$this->addRole(new Zend_Acl_Role('guest'))
			->addRole(new Zend_Acl_Role('member'),'guest')
			->addRole(new Zend_Acl_Role('admin'), 'member');
	
// 		$this->allow(null, null);
		$this->allow(null, array('index','error'))
			->allow('guest', 'user', array('login','logout'))
			->allow('member', 'user', array('index','password'))
			->allow('member', 'docs')
			->allow('admin', null);	
	}
}