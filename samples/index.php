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
 * @property-read mixed $attributes
 * @property-read int $parentId
 *
 * @property-read User $parent
 */
class User extends Model {
	protected function table() {
		return 'users';
	}

	protected function fields() {
		return [
			'id' => Field::INT,
			'created' => Field::DATETIME,
			'name' => Field::STRING,
			'email' => Field::STRING,
			'attributes' => Field::JSON,
			'parentId' => Field::INT
		];
	}
	
	protected function relations() {
		return [
			'parent' => new HasOneRelation(User::class, 'parentId'),
			'children' => new HasManyRelation(User::class, 'parentId')
		];
	}
}

$db = new Database('root@localhost/test');

$user = $db->table('users')->one(['id' => 2])->populate(User::class);

var_dump($user->children);