<?php

namespace T4\Tests\Orm\Relations\ManyToManyModels {

    use T4\Orm\Model;

    class Category extends Model {
        protected static $schema = [
            'table' => 'cats',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'items' => ['type' => self::MANY_TO_MANY, 'model' => Item::class]
            ]
        ];
    }

    class Item extends Model {
        protected static $schema = [
            'table' => 'items',
            'columns' => ['num' => ['type' => 'int']],
            'relations' => [
                'categories' => ['type' => self::MANY_TO_MANY, 'model' => Category::class]
            ]
        ];
    }
}

namespace T4\Tests\Orm\Relations {

    require_once realpath(__DIR__ . '/../../../framework/boot.php');

    use T4\Dbal\QueryBuilder;
    use T4\Tests\Orm\Relations\ManyToManyModels\Category;
    use T4\Tests\Orm\Relations\ManyToManyModels\Item;

    class ManyToManySaveTest
        extends BaseTest
    {

        protected function setUp()
        {
            $this->getT4Connection()->execute('CREATE TABLE cats (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO cats (num) VALUES (1), (2), (3), (4)
            ');
            Category::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE items (__id SERIAL, num INT)');
            $this->getT4Connection()->execute('
              INSERT INTO items (num) VALUES (1), (2), (3), (4)
            ');
            Item::setConnection($this->getT4Connection());

            $this->getT4Connection()->execute('CREATE TABLE cats_to_items (__category_id BIGINT, __item_id BIGINT)');
            $this->getT4Connection()->execute('
              INSERT INTO cats_to_items (__category_id, __item_id) VALUES (1, 1), (2, 2), (2, 3), (3, 2), (4, NULL )
            ');

        }

        protected function tearDown()
        {
            $this->getT4Connection()->execute('DROP TABLE cats');
            $this->getT4Connection()->execute('DROP TABLE items');
            $this->getT4Connection()->execute('DROP TABLE cats_to_items');
        }

        public function testNoChanges()
        {
            $cat = Category::findByPK(1);
            $cat->save();

            $item = Item::findByPK(1);
            $item->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3], $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4], $data[3]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3], $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4], $data[3]);
        }

        /*
        public function testCreateWORelation()
        {
            $cat = new Category;
            $cat->num = 2;
            $cat->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => null], $data[2]);
        }

        public function testCreateWRelation()
        {
            $cat = new Category;
            $cat->num = 2;
            $cat->items->add(Item::findByPK(3));
            $cat->items->add(new Item(['num' => 4]));
            $cat->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2], $data[1]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 2],    $data[2]);
            $this->assertEquals(['__id' => 4, 'num' => 4, '__category_id' => 2],    $data[3]);
        }

        public function testAdd()
        {
            $cat = Category::findByPK(1);
            $cat->items->add(Item::findByPK(3));
            $cat->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => 1],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => 1],    $data[2]);
        }

        public function testUnset()
        {
            $cat = Category::findByPK(1);
            unset($cat->items[0]);
            $cat->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => null],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => 1],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => null],    $data[2]);
        }

        public function testClear()
        {
            $cat = Category::findByPK(1);
            $cat->items = new Collection();
            $cat->save();

            $data =
                Category::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Category::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1], $data[0]);

            $data =
                Item::getDbConnection()
                    ->query(
                        (new QueryBuilder())->select()->from(Item::getTableName())
                    )->fetchAll(\PDO::FETCH_ASSOC);

            $this->assertEquals(['__id' => 1, 'num' => 1, '__category_id' => null],    $data[0]);
            $this->assertEquals(['__id' => 2, 'num' => 2, '__category_id' => null],    $data[1]);
            $this->assertEquals(['__id' => 3, 'num' => 3, '__category_id' => null],    $data[2]);
        }
        */
    }

}