# ParrotDb
ParrotDb is an open source object database written in PHP.

Please note this is a fun project and not intended for use in production.
It is rather a proof of concept. However, feel free to check out the [benchmark section](https://github.com/Annoraaq/ParrotDb/wiki/Benchmarks)
 of the [wiki](https://github.com/Annoraaq/ParrotDb/wiki) to get a first impression of performance.
 
ParrotDb comes with an own [query language](https://github.com/Annoraaq/ParrotDb/wiki/Language-of-the-Birds) as well as an own [text file format](https://github.com/Annoraaq/ParrotDb/wiki/Feather-file-format).

# Installation
```git clone https://github.com/Annoraaq/ParrotDb.git```

```composer install```

# Run tests
```vendor/bin/phpunit```

# Basic usage
Let's say we have an example class:
```
class Bird
{
    public $id;
    public $name;
}
```

Now we need to create a session, a parser and a persistence manager:
```
$session = \ParrotDb\Core\PSessionFactory::createSession(
  "Testfile",
  \ParrotDb\Core\PSession::DB_XML
);

$parser = new \ParrotDb\Query\LotB\Parser\Parser($session->getDatabase());

$pm = $session->createPersistenceManager();
```

## Insert
```
$bird = new Bird();
$bird->id = 42;
$bird->name = "Pelecanidae";

$pm->persist($bird);

$pm->commit();
```

## Select
```
$constraint = $parser->parse('get Bird id = 42');
$resultSet = $pm->query($constraint);
$fetchedBird = $resultSet->first();
```
## Update
```
$constraint = $parser->parse('get Bird id = 42');
$resultSet = $pm->query($constraint);
$fetchedBird = $resultSet->first();
$fetchedBird->name = "Pidgeon";
$pm->commit();
```

## Delete
```
$constraint = $parser->parse('get Bird id = 42');
$pm->delete($constraint);
```

