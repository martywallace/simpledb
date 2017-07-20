<?php

require('./vendor/autoload.php');

use PHPUnit\Framework\TestCase;
use SimpleDb\Database;
use SimpleDb\Row;
use SimpleDb\Query;
use SimpleDb\Model;
use SimpleDb\Field;

class User extends Model {
	protected function table() { return 'users'; }

	protected function fields() {
		return [
			'id' => Field::INT,
			'name' => Field::STRING,
			'email' => Field::STRING,
			'created' => Field::DATETIME,
			'attributes' => Field::JSON
		];
	}
}


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

		$this->assertEquals($db->table('users')->oneWhere(['email' => $email])->name, 'John');

		return $email;
	}

	/**
	 * @depends testConnect
	 * @depends testInsert
	 */
	public function testOne(Database $db, $email) {
		$record = $db->table('users')->oneWhere(['email' => $email]);

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

		$this->assertNull($db->table('users')->oneWhere(['email' => $email]));
	}

	public function testBuildSelectQuery() {
		$examples = [
			Query::select('users')->compile(),
			Query::select('users', ['name', 'email'])->compile(),
			Query::select('users')->limit(1)->compile(),
			Query::select('users')->where(['id', 'name'])->compile(),
			Query::select('users')->where(['email'])->order('id', 'asc')->limit(5, 10)->compile(),
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
			Query::delete('users')->where(['id'])->compile(),
			Query::delete('users')->limit(7)->compile()
		];

		$this->assertEquals($examples, [
			'DELETE FROM users',
			'DELETE FROM users WHERE id = ?',
			'DELETE FROM users LIMIT 7'
		]);
	}

	public function testModelCreation() {
		$model = new User();

		$this->assertTrue($model instanceof Model);

		return $model;
	}

	/**
	 * @depends testModelCreation
	 */
	public function testModelFields(Model $model) {
		$this->assertEquals($model->getFields(), ['id', 'name', 'email', 'created', 'attributes']);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testModelPrimaryFields(Model $model) {
		$this->assertEquals($model->getPrimaryFields(), ['id']);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testUniqueFields(Model $model) {
		$this->assertEquals($model->getUniqueFields(), ['id', 'email']);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testNonUniqueFields(Model $model) {
		$this->assertEquals($model->getNonUniqueFields(), ['name', 'created', 'attributes']);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testInitialPrimitiveData(Model $model) {
		$this->assertEquals($model->getPrimitiveData(), [
			'id' => null,
			'name' => null,
			'email' => null,
			'created' => null,
			'attributes' => null
		]);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testInitialRefinedData(Model $model) {
		$this->assertEquals($model->getRefinedData(), [
			'id' => null,
			'name' => null,
			'email' => null,
			'created' => null,
			'attributes' => null
		]);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testChangeData(Model $model) {
		$model->email = 'test@test.com';

		$this->assertEquals([
			'primitive' => $model->getPrimitiveData()['email'],
			'refined' => $model->getRefinedData()['email']
		], [
			'primitive' => 'test@test.com',
			'refined' => 'test@test.com'
		]);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testFillData(Model $model) {
		$model->fill([
			'name' => 'John Smith',
			'email' => 'john.smith@test.com'
		]);

		$this->assertEquals([
			'primitive' => $model->getPrimitiveData(),
			'refined' => $model->getRefinedData()
		], [
			'primitive' => [
				'id' => null,
				'name' => 'John Smith',
				'email' => 'john.smith@test.com',
				'created' => null,
				'attributes' => null
			],
			'refined' => [
				'id' => null,
				'name' => 'John Smith',
				'email' => 'john.smith@test.com',
				'created' => null,
				'attributes' => null
			]
		]);
	}

	public function testCreateWithPrimitiveDatetime() {
		$model = new User([
			'created' => '2017-01-01'
		]);

		$this->assertEquals($model->getPrimitiveData()['created'], '2017-01-01 00:00:00');

		return $model;
	}

	/**
	 * @depends testCreateWithPrimitiveDatetime
	 */
	public function testDatetimeType(Model $model) {
		$this->assertTrue($model->created instanceof DateTime);
		$this->assertEquals($model->created->format('Y-m-d'), '2017-01-01');

		$this->assertEquals([
			'primitive' => $model->getPrimitiveData()['created'],
			'refined' => $model->getRefinedData()['created']->format('Y-m-d')
		], [
			'primitive' => '2017-01-01 00:00:00',
			'refined' => '2017-01-01'
		]);
	}

	/**
	 * @depends testModelCreation
	 */
	public function testJsonType(Model $model) {
		$model->attributes = ['test' => 5, 'example' => ['value' => 'example']];

		$data = new stdClass();
		$data->test = 5;
		$data->example = new stdClass();
		$data->example->value = 'example';

		$this->assertEquals($model->attributes, $data);

		$this->assertEquals([
			'primitive' => $model->getPrimitiveData()['attributes'],
			'refined' => $model->getRefinedData()['attributes']
		], [
			'primitive' => '{"test":5,"example":{"value":"example"}}',
			'refined' => $data
		]);
	}

}