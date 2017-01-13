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

	protected function table() { return 'another'; }
	protected function fields() { return []; }

}

//print_r(User::getNonUnique());

$db = new Database('root@localhost/test', true);

/*
$row = $db->table('users')->insert([
	'name' => 'hi',
	'email' => 'test@test.com'
]);
*/

$user = new User();

print_r(Another::getPrimaryFields());

//$user = $db->table('users')->one(['id' => 17])->populate(User::class);

//var_dump($user->getUniquePrimitiveData());

//echo Query::select('test')->where(['id' => 1])->order(['id' => 'asc', 'name' => 'desc'])->limit(1);

//echo Query::insert('test', User::getFields(), User::getNonUnique());

//print_r($db->table('users')->describe());