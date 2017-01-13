<?php

require('./vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use SimpleDb\Database;
use SimpleDb\Row;
use SimpleDb\Rows;
use SimpleDb\Query;


class SimpleDbTest extends TestCase {

	public function testConnect() {
		return new Database('root@127.0.0.1/test');
	}

	/**
	 * @depends testConnect
	 */
	public function testInsert(Database $db) {
		$email = 'example@example.com';

		$db->table('users')->insert(['name' => 'John', 'email' => $email]);

		$this->assertEquals($db->table('users')->one(['email' => $email])->name, 'John');

		return $email;
	}

	/**
	 * @depends testConnect
	 * @depends testInsert
	 */
	public function testOne(Database $db, $email) {
		$record = $db->table('users')->one(['email' => $email]);

		$this->assertTrue($record instanceof Row);
	}

	/**
	 * @depends testConnect
	 */
	public function testAll(Database $db) {
		$records = $db->table('users')->all();

		$this->assertTrue($records instanceof Rows);
	}

	/**
	 * @depends testConnect
	 * @depends testInsert
	 */
	public function testDelete(Database $db, $email) {
		$db->table('users')->delete(['email' => $email]);

		$this->assertNull($db->table('users')->one(['email' => $email]));
	}

	public function testBuildSelectQuery() {
		$examples = [
			Query::select('users')->compile(),
			Query::select('users', ['name', 'email'])->compile(),
			Query::select('users')->limit(1)->compile(),
			Query::select('users')->where(['id' => 1, 'name' => 'Steve'])->compile(),
			Query::select('users')->where(['email' => 'test@test.com'])->order('id', 'asc')->limit(5, 10)->compile(),
			Query::select('users', 'email')->order(['email' => 'desc', 'id' => 'asc'])->compile()
		];

		$this->assertEquals($examples, [
			'SELECT * FROM users',
			'SELECT name, email FROM users',
			'SELECT * FROM users LIMIT 1',
			'SELECT * FROM users WHERE id = ? AND name = ?',
			'SELECT * FROM users WHERE email = ? ORDER BY id ASC LIMIT 5, 10',
			'SELECT email FROM users ORDER BY email DESC, id ASC'
		]);
	}

	public function testBuildDeleteQuery() {
		$examples = [
			Query::delete('users')->compile(),
			Query::delete('users')->where(['id' => 1]),
			Query::delete('users')->limit(7)
		];

		$this->assertEquals($examples, [
			'DELETE FROM users',
			'DELETE FROM users WHERE id = ?',
			'DELETE FROM users LIMIT 7'
		]);
	}

}