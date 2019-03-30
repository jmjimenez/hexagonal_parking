<?php

namespace Jmj\Test\Unit\Common;

use Exception;
use Jmj\Parking\Common\Exception\PdoConnectionError;
use Jmj\Parking\Common\Exception\PdoExecuteError;
use Jmj\Parking\Common\Pdo\PdoProxy;
use PHPUnit\Framework\TestCase;

class PdoProxyTest extends TestCase
{
    /** @var PdoProxy */
    protected static $pdoProxy;

    /** @var string */
    protected $tableName;

    /** @var array */
    protected $testData;

    public function setUp()
    {
        parent::setUp();

        $this->tableName = 'test';

        $this->testData = [
            [ 'id' => 1, 'message' => 'hello' ],
            [ 'id' => 2, 'message' => 'good bye' ],
            [ 'id' => 3, 'message' => 'sayonara' ],
        ];
    }

    /**
     * @throws PdoConnectionError
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        self::$pdoProxy = new PdoProxy();
        self::$pdoProxy->connectToSqlite(':memory:');
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$pdoProxy = null;
    }

    /**
     *
     * @throws PdoExecuteError
     */
    public function testCreateTable()
    {
        self::$pdoProxy->createTable(
            $this->tableName,
            [
                '`id` INT NOT NULL',
                '`message` VARCHAR(30) NOT NULL',
                'PRIMARY KEY (`id`)',
            ]
        );

        $this->assertEquals('00000', self::$pdoProxy->errorInfo()[0]);
    }

    /**
     * @throws Exception
     */
    public function testInsert()
    {
        $result = self::$pdoProxy->insert($this->tableName, $this->testData[0]);
        $this->assertEquals(1, $result);
    }

    /**
     * @throws PdoExecuteError
     * @throws Exception
     */
    public function testInsertWhenJsonData()
    {
        $tableName = 'objects';

        self::$pdoProxy->createTable(
            $tableName,
            [
                '`id` INT NOT NULL',
                '`object` TEXT  NOT NULL',
                'PRIMARY KEY (`id`)',
            ]
        );

        /** @var DummyClass[] $children */
        $children = [
            new DummyClass('child1'),
            new DummyClass('child2'),
            new DummyClass('child3'),
        ];

        $parent = new DummyClass('parent');

        foreach ($children as $child) {
            $parent->addChild($child);
        }

        $serialized = serialize($parent);

        $result = self::$pdoProxy->insert($tableName, [ 'id' => 1, 'object' => $serialized ]);
        $this->assertEquals(1, $result);

        $record = self::$pdoProxy->fetchOne("SELECT * FROM {$tableName} WHERE id = :id", [ ':id' => 1 ]);
        $serializedField = $record['object'];
        $this->assertEquals($serialized, $serializedField);

        /** @var DummyClass $object */
        $object = unserialize($serializedField);

        $this->assertEquals($parent->name(), $object->name());

        /** @var DummyClass[] $recordedChildren */
        $recordedChildren = $parent->children();
        $this->assertEquals(3, count($recordedChildren));

        for ($i = 0; $i < 3; $i++) {
            $this->assertEquals($children[$i]->name(), $recordedChildren[$i]->name());
        }
    }

    /**
     * @throws PdoExecuteError
     */
    public function testFetchOne()
    {
        $result = self::$pdoProxy->fetchOne("SELECT * FROM {$this->tableName} WHERE id = :id", [ ':id' => 1 ]);
        $this->assertEquals($this->testData[0], $result);
    }

    /**
     * @throws Exception
     */
    public function testFetchAll()
    {
        self::$pdoProxy->insert($this->tableName, $this->testData[1]);
        self::$pdoProxy->insert($this->tableName, $this->testData[2]);

        $result = self::$pdoProxy->fetchAll("SELECT * FROM {$this->tableName}");
        $this->assertEquals($this->testData, $result);
    }

    /**
     * @throws PdoExecuteError
     * @throws Exception
     */
    public function testExecute()
    {
        $result = self::$pdoProxy->execute("DELETE FROM `{$this->tableName}` WHERE id = :id", [ ':id' => 1 ]);
        $this->assertEquals(1, $result);

        $record = self::$pdoProxy->fetchOne("SELECT * FROM {$this->tableName} WHERE id = 1");
        $this->assertFalse($record);
    }

    /**
     * @throws PdoExecuteError
     * @throws Exception
     */
    public function testRollbackTransaction()
    {
        self::$pdoProxy->startTransaction();
        $result = self::$pdoProxy->execute("DELETE FROM `{$this->tableName}` WHERE id = :id", [ ':id' => 2 ]);
        $this->assertEquals(1, $result);
        self::$pdoProxy->rollbackTransaction();

        $record = self::$pdoProxy->fetchOne("SELECT * FROM {$this->tableName} WHERE id = 2");
        $this->assertEquals($this->testData[1], $record);
    }

    /**
     * @throws PdoExecuteError
     * @throws Exception
     */
    public function testRollbackTransactionWhenNested()
    {
        self::$pdoProxy->insert($this->tableName, $this->testData[0]);

        self::$pdoProxy->startTransaction();
        $result = self::$pdoProxy->execute("DELETE FROM `{$this->tableName}` WHERE id = :id", [ ':id' => 1 ]);
        $this->assertEquals(1, $result);

        self::$pdoProxy->startTransaction();
        $result = self::$pdoProxy->execute("DELETE FROM `{$this->tableName}` WHERE id = :id", [ ':id' => 2 ]);
        $this->assertEquals(1, $result);
        self::$pdoProxy->commitTransaction();

        self::$pdoProxy->startTransaction();
        $result = self::$pdoProxy->execute("DELETE FROM `{$this->tableName}` WHERE id = :id", [ ':id' => 3 ]);
        $this->assertEquals(1, $result);
        self::$pdoProxy->commitTransaction();

        self::$pdoProxy->rollbackTransaction();

        $record = self::$pdoProxy->fetchOne("SELECT * FROM {$this->tableName} WHERE id = 1");
        $this->assertEquals($this->testData[0], $record);

        $record = self::$pdoProxy->fetchOne("SELECT * FROM {$this->tableName} WHERE id = 2");
        $this->assertEquals($this->testData[1], $record);

        $record = self::$pdoProxy->fetchOne("SELECT * FROM {$this->tableName} WHERE id = 3");
        $this->assertEquals($this->testData[2], $record);
    }
}
