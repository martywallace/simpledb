<?php

header('Content-Type: text/plain');

require '../vendor/autoload.php';

use SimpleDb\Database;

$db = new Database('root@localhost/test');

print_r($db->one('SELECT * FROM users'));
print_r($db->all('SELECT * FROM users'));
print_r($db->prop('SELECT email FROM users WHERE id = ?', [1]));

print_r($db->table('users')->one(2));
print_r($db->table('users')->all());

$db->table('users')->delete(1);

$db->table('users')->insert([
	'email' => 'ts@test.com',
	'password' => password_hash('test', CRYPT_BLOWFISH)
]);