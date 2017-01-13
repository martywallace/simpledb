<?php

require('../vendor/autoload.php');

header('Content-Type: text/plain');

use SimpleDb\Database;
use SimpleDb\Field;
use SimpleDb\Model;
use SimpleDb\HasOneRelation;
use SimpleDb\Row;
use SimpleDb\Relation;
use SimpleDb\HasManyRelation;
use SimpleDb\Query;

/**
 * @property-read int $id
 * @property-read DateTime $created
 * @property-read string $name
 * @property-read string $email
 * @property-read int $parentId
 *
 * @property-read User $parent
 * @property-read User[] $children
 */
class User extends Model {

	protected function table() { return 'users'; }

	protected function fields() {
		return [
			'id' => Field::INT,
			'created' => Field::DATETIME,
			'name' => Field::STRING,
			'email' => Field::STRING,
			'parentId' => Field::INT,
			'childOf' => Field::INT
		];
	}
	
	protected function relations() {
		return [
			'parent' => new HasOneRelation(User::class, 'parentId'),
			'children' => new HasManyRelation(User::class, 'childOf')
		];
	}

}

class Another extends Model {

	protected function fields() {
		return [
			'two' => 'int'
		];
	}

	protected function table() { return 'another'; }

}

//print_r(User::getNonUnique());

$db = new Database('root@localhost/test', true);

$id = $db->table('users')->insert([
	//'id' => 65,
	'email' => 'marty@martywallace.com'
]);

print_r($id);