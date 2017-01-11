<?php

require('./vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use SimpleDb\Database;
use SimpleDb\Row;
use SimpleDb\Rows;


class SimpleDbTest extends TestCase {

	public function testConnect() {
		return new Database('root@127.0.0.1/test');
	}

	/**
	 * @depends testConnect
	 */
	public function testInsert(Database $db) {
		$email = 'example@example.com';

		$db->insert('users', ['name' => 'John', 'email' => $email]);

		$this->assertEquals($db->table('users')->one($email, 'email')->name, 'John');

		return $email;
	}

	/**
	 * @depends testConnect
	 * @depends testInsert
	 */
	public function testOne(Database $db, $email) {
		$record = $db->table('users')->one($email, 'email');

		$this->assertTrue($record instanceof Row);
	}

	/**
	 * @depends testConnect
	 */
	public function testAll(Database $db) {
		$records = $db->all('SELECT * FROM users');

		$this->assertTrue($records instanceof Rows);
	}

	/**
	 * @depends testConnect
	 * @depends testInsert
	 */
	public function testDelete(Database $db, $email) {
		$db->delete('users', $email, 'email');

		$this->assertNull($db->one('SELECT * FROM users WHERE email = ?', [$email]));
	}

}