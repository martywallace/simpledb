<?php

require('./vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use SimpleDb\Database;


class SimpleDbTest extends TestCase {

	public function testConnect() {
		return new Database('root@127.0.0.1/test');
	}

	/**
	 * @depends testConnect
	 */
	public function testOne(Database $db) {
		$record = $db->one('SELECT * FROM users');

		$this->assertNotNull($record, null);
	}

	/**
	 * @depends testConnect
	 */
	public function testAll(Database $db) {
		$records = $db->all('SELECT * FROM users');

		$this->assertTrue(is_array($records));
	}

	/**
	 * @depends testConnect
	 */
	public function testInsert(Database $db) {
		$db->insert('users', ['name' => 'John', 'email' => 'example@example.com']);

		$this->assertEquals($db->one('SELECT * FROM users WHERE email = ?', ['example@example.com'])->name, 'John');

		return 'example@example.com';
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