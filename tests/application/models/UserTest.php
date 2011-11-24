<?php
class UsersTest extends PHPUnit_Framework_TestCase
{

	public function __construct($name = NULL)
	{
		parent::__construct($name);

		if(Zend_Registry::isRegistered('db')) {
			$this->db = Zend_Registry::get('db');
		} else {
			
			$config = Zend_Registry::get('config');

			// set up database
			$db = Zend_Db::factory($config->resources->db);
			Zend_Db_Table::setDefaultAdapter($db);
			Zend_Registry::set('db', $db);
			$this->db = $db;
		}
	}

	public function setUp()
	{
		// reset database to known state
		$this->_setupDatabase();
		$this->bootstrap = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
		parent::setUp();
	}

	protected function _setupDatabase()
	{
		echo ' setupDatabase ' ;
		$this->db->query('DROP TABLE IF EXISTS users;');

		$this->db->query(<<<EOT
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `salt` varchar(50) NOT NULL,
  `role` varchar(50) NOT NULL,
  `sf_login` varchar(50) NOT NULL,
  `sf_password` varchar(50) NOT NULL,
  `token` varchar(255) NOT NULL,
  `wdls` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  PRIMARY KEY (`id`)
)		
EOT
		);
		
		       
		$row = array (
            'username'  => 'admin', 
            'password'  => SHA1('passwordce8d96d579d389e783f95b3772785783ea1a9854'), 
            'salt'  => 'ce8d96d579d389e783f95b3772785783ea1a9854', 
            'role' => 'administrator', 
            'date_created' => '2007-02-14 00:00:00', 
		);
		$this->db->insert('users', $row);

	}

	public function testInsert()
	{
		
		$users = new Application_Model_DbTable_Users();
		
		$newUser = $users->fetchNew();
		 
		$newUser->username = 'test';
		$newUser->password = 'password';
		$newUser->role = 'administrator';
		
		$newUser->date_created = new Zend_Db_Expr('NOW()');

		$id = $newUser->save();
		 
		$nick = $users->find($id)->current();
		$this->assertSame(2, (int)$nick->id);
		 
		// check that the date_created has been filled in
		$this->assertNotNull($nick->date_created);
	}
	
	public function testUpdate () 
	{
		$table = new Application_Model_DbTable_Users();
		$users = $table->find(1);
		$user = $users->current();
		Zend_Debug::dump($user);
		$this->assertSame($user->username, 'admin');
		$this->assertSame($user->id, '1');
		
		$list = $user->toArray();
		Zend_Debug::dump($list);
		
		
		
	}
	

}