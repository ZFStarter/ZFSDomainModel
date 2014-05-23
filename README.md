zfs-domain-model
================

Альтернативная реализация слоя доступа к реляционным БД на основе Zend\Db для ZF2/ZFStarter проектов.

Подключение
---
- Добавляем в конфиг фабрику сервиса автоматического создания Gateway'ев:

```php
'service_manager' => array(
    'factories' => array(
        'ZFS\DomainModel\Service' => 'ZFS\DomainModel\Service\Factory'
    )
)

```
- Добавляем алиас на дефолтное подключение к БД:

```php
'service_manager' => array(
    'aliases' => array(
        // допустим, у нас есть подключение к БД как 'Db\Adapter'
        // чтоб ZFS\DomainModel могла его использовать по-умолчанию, делаем алиас:
        'ZFS\DomainModel\Adapter' => 'Db\Adapter'
    )
)
```

Использование
---
Единственное необходимые действия:
- Создать абстрактную фабрику для создания Gateway'ев:

```php
<?php
namespace Application\Model\Gateway;

use ZFS\DomainModel\Gateway\AbstractFactory as BaseAbstractFactory;

class AbstractFactory extends BaseAbstractFactory
{
    protected $provides = array(
        'UsersGateway' => array(
            'tableName' => 'users'
        )
    );
}
```
- И добавить эту фабрику в настройки ServiceManager'а:

```php
'service_manager' => array(
    'abstract_factories' => array(
        'Application\Model\Gateway\AbstractFactory'
    )
)
```

Теперь мы можем получить доступ к Gateway через сервис локатор (на примере контроллера):
```php
/** @var \ZFS\DomainModel\Gateway\TableGateway $gateway */
$gateway = $this->getServiceLocator()->get('UsersGateway');
```

Библиотека создала `ZFS\DomainModel\Gateway\TableGateway`, подключенный к таблице 'users', использующую `ZFS\DomainModel\ResultSet\ResultSet` как прототип результата операции select и `ZFS\DomainModel\Object\ObjectMagic` как прототип абстракции строчки таблицы. Последний содержит магические `__get` и `__set` для доступа к данным строчки.

Расширенное использование
---

Абстрактная фабрика для каждого Gateway может определить множество опций, определенных в `ZFS\DomainModel\Service\Options`:
```php
const OPTION_TABLE_GATEWAY        = 'tableGateway';         // имя класса, создается через new
const OPTION_TABLE_NAME           = 'tableName';            // имя таблицы, единственное обязательное поле
const OPTION_ADAPTER              = 'adapter';              // имя, доступное через ServiceLocator
const OPTION_TABLE_FEATURES       = 'tableFeatures';        // массив из обьектов ***Feature
const OPTION_RESULT_SET_PROTOTYPE = 'resultSetPrototype';   // имя класса, создается через new
const OPTION_OBJECT_PROTOTYPE     = 'objectPrototype';      // имя класса, создается через new
const OPTION_SQL                  = 'sql';                  // имя класса, создается через new
```

Каждый класс должен наследоваться от соответствующего класса из `ZFS\DomainModel`
OPTION_OBJECT_PROTOTYPE должен как минимум реализовывать `ZFS\DomainModel\Object\ObjectInterface`.
Помимо интерфейса, в распоряжении программиста есть `ZFS\DomainModel\Object\Object` и `ZFS\DomainModel\Object\ObjectMagic`. Первый будет полезен для любителей писать геттеры и сеттеры через методы:
```php
<?php
namespace Application\Model\Object;

use ZFS\DomainModel\Object\Object;

class User extends Object
{
    protected $primaryColumns = array(
        'id'
    );

    public function getId()
    {
        return $this->data['id']; // напрямую из массива данных
    }

    public function getName()
    {
        return $this->get('name'); // использует настройку $fieldToColumnMap, а так же возвращает null если isset($this->data[$name]) == false 
    }
}
```

Второй более минималистичен. Испольузет `__get` и `__set` для доступа к полям, внутри которых используется `$this->get()` (из примера выше) и `$this->set()`:
```php
<?php
namespace Application\Model\Object;

use ZFS\DomainModel\Object\ObjectMagic;

/**
 * @property int    id
 * @property string name
 */
class User extends ObjectMagic
{
    protected $primaryColumns = array(
        'id'
    );
}
```
PHPDoc @property желательно использовать как минимум для автокомплита.

В помощь camelCase стилю программирования и underscore именования колонок в БД можно использовать параметр `$fieldToColumnMap`:

```php
<?php
namespace Application\Model\Object;

use ZFS\DomainModel\Object\ObjectMagic;

/**
 * @property int    id
 * @property string name
 * @property string dateOfBirth
 */
class User extends ObjectMagic
{
    protected $primaryColumns = array(
        'id'
    );

    protected $fieldToColumnMap = array(
        'dateOfBirth' => 'date_of_birth'
    );
}
```

Отсеять лишние поля в массиве `$data` (полезно для ObjectMagic), которых нет в исходной таблице, можно использовать `ZFS\DomainModel\Feature\FilterColumnsFeature`, просто добавить ее в описание Gateway:
```php
<?php
namespace Application\Model\Gateway;

use ZFS\DomainModel\Feature\FilterColumnsFeature;
use ZFS\DomainModel\Gateway\AbstractFactory as BaseAbstractFactory;
use ZFS\DomainModel\Service\Options;

class AbstractFactory extends BaseAbstractFactory
{
    public function __construct()
    {
        $this->provides['UsersGateway'] = array(
            Options::OPTION_TABLE_NAME       => 'users',
            Options::OPTION_TABLE_FEATURES   => array(new FilterColumnsFeature()),
            Options::OPTION_OBJECT_PROTOTYPE => 'Application\Model\Object\User'
        );
    }
}
```
Здесь, как видно, ключи опций используются из `Options`, определение перенесено в конструктор из-за необходимости создать инстанс `FilterColumnsFeature` и используется `Application\Model\Object\User` из примера выше.


Лицензия
----

MIT