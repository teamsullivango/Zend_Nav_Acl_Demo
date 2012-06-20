Zend_Nav_Acl_Demo
=================

A working demonstration of integrating Zend_Acl rules upon Zend_Navigation presentation.

Database
========

create table users (

  user_id not null auto_increment primary key,
  
  username varchar(50),
  
  email text,
  
  password varchar(250),
  
  salt varchar(8),
  
  first_name varchar(50),
  
  last_name varchar(50),
  
  role varchar(25),
  
  reset_password tinyint(1)  
);
