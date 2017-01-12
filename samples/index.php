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
			'parent' => new HasOneRelation('parentId', 'users', 'id', User::class),
			'children' => new HasManyRelation('id', 'users', 'childOf', User::class)
		];
	}

}

class Another extends Model {
	protected function table() { return 'test'; }
	protected function fields() { return []; }
}


$db = new Database('root@localhost/test');

$user = User::from($db->table('users')->one(7));
$comp = $db->table('users')->one(7)->populate(User::class);

var_dump($user->equalTo($comp));

print_r($user);