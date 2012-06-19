<?php

class Model_User 
	extends Zend_Db_Table_Abstract 
{
	protected $_name = "users";

	public function createUser($email, $firstName, $lastName, $role) 
	{
		$rowUser = $this->createRow();
		if($rowUser)
		{
			$rowUser->username = substr($firstName, 0, 1) . $lastName;
			$rowUser->email = $email;
			// for all new users their password is their username, just for demonstration purposes
			$salt = Model_User::createSalt();
			$rowUser->password = Model_User::encryptPassword($rowUser->username, $salt);
			$rowUser->salt = $salt;
			$rowUser->first_name = $firstName;
			$rowUser->last_name = $lastName;
			$rowUser->role = $role;
			
			// this should be the default value in the table definition, but it's
			// declared here to be explicit
			$rowUser->reset_password = TRUE;
			$rowUser->save();
			return $rowUser;
		}
		else 
		{
			throw new Zend_Exception("Could not create user!");	
		}
	}
	
	public function updateUser ($user_id, $username, $email, $firstName, $lastName, $role)
	{
		$rowUser = $this->find($user_id)->current();
	
		if ($rowUser)
		{
			$rowUser->username 		=	$username;
			$rowUser->email 		=	$email;
			$rowUser->first_name 	=	$firstName;
			$rowUser->last_name 	=	$lastName;
			$rowUser->role 			=	$role;
			$rowUser->save();
		}
		else
		{
			throw new Zend_Exception("User update failed. User not found");
		}
	}
	
	public function updatePassword ($user_id, $password)
	{
		$rowUser = $this->find($user_id)->current();
	
		if ($rowUser)
		{
			$rowUser->password = Model_User::encryptPassword($password, $rowUser->salt);
			$rowUser->reset_password = FALSE;
			$rowUser->save();
		}
		else
		{
			throw new Zend_Exception("Password update failed. User " . $user_id . " not found");
		}
	}
	
	public function deleteUser($user_id)
	{
		$rowUser = $this->find($user_id)->current();
		if($rowUser)
		{
			$rowUser->delete();
		}
		else
		{
			throw new Zend_Exception("Could not delete user. user not found!");
		}
	}
	
	private static function createSalt()
	{
		$acceptableCharacters = "abcdefghijklmnopqrstuvwxyz1234567890";
		$temp = "";
		$salt = "";
		for($i=0;$i<8;$i++)
		{
			$temp = rand(0, strlen($acceptableCharacters)-1);
			$salt .= $acceptableCharacters{$temp};
		}
		return $salt;
	}
	
	public static function encryptPassword ($password, $salt)
	{
		return sha1($password . $salt, TRUE);
	}
	
	public static function getSalt($user)
	{
		$userModel = new self();
		$select = $userModel->select();
		$select->where('username = ?', $user)
				->orWhere('user_id = ?', $user);
		$results = $userModel->fetchAll($select);
		$salt = $results[0]->salt;
		return $salt;
	}
	
	public static function getUsers()
	{
		$userModel = new self();
		$select = $userModel->select();
		$select->order(array('last_name', 'first_name'));
		return $userModel->fetchAll($select);
	}
	
	public static function getUser_id ($username)
	{
		$userModel = new self();
		$select = $userModel->select();
		$select->where('username = ?', $username);
		$results = $userModel->fetchAll($select);
		$user_id = $results[0]->user_id;
		return $user_id;
	}
	
	public static function needsPasswordReset($user)
	{
		$userModel = new self();
		$select = $userModel->select();
		$select->where('username = ?', $user)
				->orWhere('user_id = ?', $user);
		$results = $userModel->fetchAll($select);
		if ($results->count() > 0)
		{
			$needReset = $results[0]->reset_password;
		}
		else 
		{
			$needReset = false;
		}		
		return $needReset;
	}
	
	public static function userExists($user)
	{
		$userModel = new Model_User();
		$select = $userModel->select();
		$select->where('username', $user)
				->orWhere('user_id', $user);
		$result = $userModel->fetchAll($select);
				
		if ($result->count() > 0)
		{
			return TRUE;
		}
		else 
		{
			return FALSE;
		}
	}	
}

