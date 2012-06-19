<?php
class Zend_View_Helper_SetNavRole
	extends Zend_View_Helper_Abstract
{
	public function setNavRole()
	{
		$identity = null;
		if (Zend_Registry::isRegistered('identity'))
			$identity = Zend_Registry::get('identity');
		if (Zend_Registry::isRegistered('nav'))
			$nav = Zend_Registry::get('nav');

		if ($identity !== null )
			$nav->setRole(strtolower($identity->role));
		else
			$nav->setRole('guest');
	}
}