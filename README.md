# ParrotDb
ParrotDb is an open source object database written in PHP.

Please note this is a fun project and not intended for use in production. It is rather a proof of concept.

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
$fetchedBird = $pm->query($constraint);
```
## Update
```
$constraint = $parser->parse('get Bird id = 42');
$fetchedBird = $pm->query($constraint);
$fetchedBird->name = "Pidgeon";
$pm->commit();
```

## Delete
```
$constraint = $parser->parse('get Bird id = 42');
$pm->delete($constraint);
```

