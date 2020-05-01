# Baka Database

Baka Database

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bakaphp/database/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/database/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/bakaphp/database/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/database/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/bakaphp/database/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bakaphp/database/build-status/master)

# Model

The default behavior of the baka model is giving you the normal functions to work with any mc project

- automatic updated_at , created_at  times
- soft delete by just calling softDelete() insted of delete()
- toFullArray() instead of toArray() to avoid dynamic fields on your model been removed by phalcon serialization
- Custom Fields Trait and CLI
- Hash Table Traits for dynamic atributes 

# Custom Fields Model

One of the things we look for is a table that growth in a vertical way instead of horizontal . We made custom fields to avoid having to go later on in proyect and having to add new fields to the table, with this we can managed them dynamicly and later on add UI for the client to better manage the info

To create a custom fields table from a module you will need to use our CLI 

To use you need your  model to extend from ModelCustomFields

```php
<?php

namespace Canvas\Models;

class Leads extends \Baka\Database\Model
{
    use CustomFieldsTrait;
}
```

And you also need to create the custom fields model value

```php
<?php

namespace Canvas\Models;

use \Baka\Database\CustomeFieldsInterface;

class LeadsCustomFields extends \Baka\Database\Model implements CustomeFieldsInterface
{
   /**
     * Set the custom primary field id
     *
     * @param int $id
     */
    public function setCustomId(int $id)
    {
        $this->leads_id = $id;
    }
}
```

Thats it now you can use this custom fields model like any other, no other explication is needed they will work like any phalcon normal model

# Hash Tables

Like its name implies, you have a table with key value for any entity you desire. This is usefull when you need to add settings to any tables in your system

```php
<?php

namespace Canvas\Models;

class Leads extends \Baka\Database\Model
{
    use HashTableTrait;
}
```