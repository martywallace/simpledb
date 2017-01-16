<?php

require('../vendor/autoload.php');

header('Content-Type: text/plain');

use SimpleDb\Database;
use SimpleDb\Field;
use SimpleDb\Model;
use SimpleDb\SingleRelation;
use SimpleDb\Row;
use SimpleDb\Relation;
use SimpleDb\MultipleRelation;
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
			'parent' => new SingleRelation(User::class, 'parentId'),
			'children' => new MultipleRelation(User::class, 'parentId')
		];
	}
}

$db = new Database('root@localhost/test', true);

$user = $db->table('users')->find(1)->populate(User::class);

print_r($user->parent);
print_r($db->table('users')->count());

print_r($db->prepared);