[EXPERIMENT] Venne\Generics
===========================


Usage
-----

**Basic usage**

Use phpDoc `@template` as definition of template types:

```php
/**
 * @template IEntity
 */
class Repository {

	public function save(IEntity $entity)
	{
		...
	}

}
```
Now you can generate own class generated from template. Symbol `_` is default separator between class name and template type.

```php
class Article {}

$articleRepository = new Repository_Article;
$articleRepository->save(new Article); // works
$articleRepository->save(11); // fail
```

If you need to use multiple template types, define them in phpDoc:

```php
/**
 * @template IEntity, IEntityManager
 */
...
```

and work with it similarly:

```php
$articleRepository = new Repository_Article_EntityManager;
...
```

If you want to remove typehint:

```php
$repository = new Repository_;
$repository->save('yeah'); // works
$repository->save(33); // works
$repository->save(new stdClass()); // works
...
```

**Working with namespaces**

Use absolute class names:

```php
$article = new App\Article;
$article->text = 'Foo';

$articleRepository = new App\Repository_App\Article;
$articleRepository->save($article);
```

Or you can use `use statements`:

```php
use App\Article;
use App\Repository_App\Article as ArticleRepository;

$article = new Article;
$article->text = 'Foo';

$articleRepository = new ArticleRepository;
$articleRepository->save($article);
```


**Autowiring**

```yml
services:
	- ArticleRepository_Article
	- ArticleService
```

```php
use ArticleRepository_Article as ArticleRepository;

class ArticleService {

	private $repository;

	public function __construct(ArticleRepository $repository)
	{
		$this->repository = $repository;
	}

}
```


**Inheritance rules**

```php
abstract class Entity {}
class Page extends Entity {}
class Article extends Page {}
abstract class Repository {}

/** @template Page */
class PageRepository extends Repository {
	public function(Page $entity) {}
}

$pageRepository = new PageRepository;
$articleRepository = new PageRepository_Article;

echo $pageRepository instanceof Repository; // TRUE
echo $pageRepository instanceof PageRepository; // TRUE

echo $articleRepository instanceof Repository; // TRUE
echo $articleRepository instanceof PageRepository; // FALSE
```


Installation
------------

```sh
composer require venne/generics
```


Configuration in Nette framework
--------------------------------

```yml
extensions:
	generics: Venne\Generics\DI\GenericsExtension
```

```yml
generics:
	separator: '_'
```


Manual configuration
--------------------

```php
$generics = new Venne\Generics\Generics;
$loader = new Venne\Generics\Loader($generics);
$loader->register();
```
