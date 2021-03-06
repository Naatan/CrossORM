Lightweight ORM, originally based on Idiorm and Paris.

# Project goals

* Intuitive ORM, you shouldn't need to check a doc every couple of minutes
* Support databases through drivers, each db driver can choose to implement features others dont have as long as the core methods are supported.
* Intuitive active record methods. When a model is aware of the fields in a table, why not just use where\_name('jack') instead of where('name','jack').
* Build in validation
* <del>Support for relations on non-relational database (through multiple queries)</del> (will be implemented on driver level, not part of the main library)
* ACL on a per table, per field basis

# Project Status

All parts of the project (whether Completed, In Progress or Todo) are subject to change, current versions should be considered as experimental.

## Completed 

* Base ORM / Model definition
* ACL library
* Query building library
* Support for drivers
* phpdoc configuration
* PHPUnit setup
* Unit tests for all libraries
* Reorganize libraries in sub-directories of the lib folder
* Create validation library
* Intelligently handle update/delete queries on resultsets
* Auto load dependencies (or hook into frameworks to do so)

## In Progress

* Make models useful (define structure, set ACL, etc)

## Todo (order does not indicate priority)

* <del>Fork & modify PHPUnit and VisualPHPUnit repo's and use them as submodules</del>
* Support SQL Joins
* Support raw queries, clauses & fields
* Develop proof-of-concept MongoDB driver
* Implement intuitive active record methods (see project goals)
* Support ACL in models
* Create proper documentation

# Sample usage

## Initiate connection

```php
    DB::factory(array(
        'driver'                => 'pdo',
        'connection_string'     => 'mysql:dbname=test;host=127.0.0.1',
        'username'              => 'root',
        'password'              => '',
    ));
```

## Standard select and consequent update & delete queries

```php
    // Execute query to get first row from table
    $result = DB::factory()->for_table('test')->find_one();
    
    // Format the result
    $result->as_array();
    $result->as_json();
    
    // Change the 'name' field
    $result->name = 'test';
    $result->save();
    
    // Delete the row
    $result->delete();
```

## Select query with ACL

```php
    // Define the rules, note that if the select action is not set on the table but IS set on the field then select queries will not work,
    // both the table and the fields being used need to have the permission set
    $rules = array(
        'tables.test.actions.select',
        'tables.test.fields.*.actions.select'
    );
    
    ACL::set_rules($rules);

    // Execute query to get first row from table, this one will succeed seeing as we gave the user wildcard permissions on all fields on this table
    $result = DB::factory()->acl()->for_table('test')->find_one();

    // By default ACL uses whitelist mode, when in blacklist mode all the rules you add will have the reversed behaviour, meaning that the above query will now fail with an Exceptions\ACL
    ACL::set_actor('default', MODE_BLACKLIST);
    
    // So this will fail
    $result = DB::factory()->acl()->for_table('test')->find_one();
```

## Insert row

```php
    // Execute query to get first row from table
    $entry = DB::factory()->for_table('test');
    
    $entry->name = 'test';
    $entry->save();
    
    $entry->name = 'test2';
    $entry->save();
    
    $entry->delete();
```

## Select and consequent update & delete queries on multiple entries

```php
    // Execute query to get first row from table
    $result = DB::factory()->for_table('test')->where_like('name', 'a%')->find_many();
    
    // Format the result, both of these functions work contextually, meaning if they are called on a resultset or a query
    // that generates a resultset it will return an array with each entry being a row, if its ran on a single entry
    // it will return the actual row
    $result->as_array();
    $result->as_json();
    
    // Change the 'name' field, this will be executed of all rows in the result set
    $result->name = 'test';
    $result->save();
    
    // Delete rows
    $result->delete();
```

## Model usage

```php
    class Model_Test extends Model {
        protected $table_name     = 'test';        // optional, table name can be detected from class name
        protected $db_id        = 'default';     // redundant, 'default' is already assumed
    }
    
    $Test = new Model_Test;
    $result = $Test->find_one();
```
