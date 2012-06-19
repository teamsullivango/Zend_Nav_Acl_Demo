<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	private $_acl = null;
	
	protected function _initView()
	{
		$view = new Zend_View();
		$view->doctype('XHTML1_STRICT');
		$view->headTitle('Zend_Nav_Acl_Demo');
		
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper(
			'ViewRenderer'
		);
		
		$viewRenderer->setView($view);
		return $view;
	}

	protected function _initAutoload()
	{
		$autoLoader = Zend_Loader_Autoloader::getInstance();
		$resourceLoader = new Zend_Loader_Autoloader_Resource(array(
			'basePath'		=>	APPLICATION_PATH,
			'namespace'		=>	'',
			'resourceTypes'	=>	array(
				'form'		=>	array(
					'path'		=>	'forms/',
					'namespace'	=>	'Form_'
				),
				'model'		=> array(
					'path'		=>	'models/',
					'namespace'	=>	'Model_'
				),
				'plugin'	=> array(
					'path'		=>	'plugins/',
					'namespace'	=>	'Plugin_'
				),
				'helper'	=> array(
					'path'		=>	'forms/Element',
					'namespace'	=>	'Element_'
				),
				'my'		=> array(
					'path'		=>	'/../library/My/',
					'namespace'	=>	'My_'
				)	
			)
		));	
		return $autoLoader;		
	}
	
	protected function _initAuth()
	{
		$this->_acl = new My_Acl();
		
		$fc = Zend_Controller_Front::getInstance();
		$fc->registerPlugin(new My_Auth($this->_acl));
	}
	
	protected function _initNavigation()
	{
		//precondition, layout exists or else
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml', 'nav');
		$navigation = new Zend_Navigation($config);
		$view->navigation($navigation)->setAcl($this->_acl);
		Zend_Registry::set('nav', $view->navigation($navigation));
	}
}

