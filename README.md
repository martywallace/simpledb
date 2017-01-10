#simpledb.

![https://api.travis-ci.org/MartyWallace/simpledb.png?branch=master](https://api.travis-ci.org/MartyWallace/simpledb.png?branch=master)

Execute common MySQL operations easily with this PDO wrapper.

> Note: This project has no relation to [AWS SimpleDB](https://aws.amazon.com/simpledb/).

## Installation.

    $ composer require martywallace/simpledb

## API.

Create an instance of `SimpleDb\Database`:

```
$db = new SimpleDb\Database('user:password@host/database');
```

Through which you can do things like:

```
// Get one item from a table.
$db->one('SELECT * FROM table');
$db->one('SELECT * FROM table WHERE id = ?', [1]);
$db->table('table')->one(1);

// Get all items from a table.
$db->all('SELECT * FROM table);
$db->table('table')->all();

// Delete a row from a table.
$db->delete('table', 1);
$db->table('table')->delete(1);

// Insert data into a table.
$db->insert('table', ['name' => 'Marty', 'age' => 25]);
$db->table('table')->insert(['name' => 'Marty', 'age' => 25]);
```
