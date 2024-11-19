<?php

namespace admin\modules\rbac\commands;

use yii\base\InvalidConfigException;
use yii\console\controllers\BaseMigrateController;
use yii\db\{Connection, Exception, Query};
use yii\di\Instance;
use yii\helpers\BaseConsole;

/**
 * Class MigrateController
 *
 * Below are some common usages of this command:
 *
 * ```
 * # creates a new migration named 'create_rule'
 * yii rbac/migrate/create create_rule
 *
 * # applies ALL new migrations
 * yii rbac/migrate
 *
 * # reverts the last applied migration
 * yii rbac/migrate/down
 * ```
 */
class MigrateController extends BaseMigrateController
{
    /**
     * The database connection
     */
    public Connection|string $db = 'db';

    public string $migrationTable = '{{%auth_migration}}';

    /**
     * @inheritdoc
     */
    public $migrationPath = '@admin/modules/rbac/migrations';

    /**
     * @inheritdoc
     */
    public $templateFile = '@admin/modules/rbac/views/migration.php';

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        $this->db = Instance::ensure($this->db, Connection::class);

        parent::init();
    }

    /**
     * @return Connection
     */
    public function getDb(): Connection
    {
        return $this->db;
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function getMigrationHistory($limit): array
    {
        if ($this->db->schema->getTableSchema($this->migrationTable, true) === null) {
            $this->createMigrationHistoryTable();
        }

        $history = (new Query())
            ->select(['apply_time'])
            ->from($this->migrationTable)
            ->orderBy(['apply_time' => SORT_DESC, 'version' => SORT_DESC])
            ->limit($limit)
            ->indexBy('version')
            ->column($this->db);

        unset($history[self::BASE_MIGRATION]);

        return $history;
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function addMigrationHistory($version): void
    {
        $this->db->createCommand()
            ->insert($this->migrationTable, ['version' => $version, 'apply_time' => time()])
            ->execute();
    }

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    protected function removeMigrationHistory($version): void
    {
        $this->db->createCommand()
            ->delete($this->migrationTable, ['version' => $version])
            ->execute();
    }

    /**
     * Creates the migration history table.
     *
     * @throws Exception
     */
    protected function createMigrationHistoryTable(): void
    {
        $tableName = $this->db->schema->getRawTableName($this->migrationTable);

        $this->stdout("Creating migration history table \"$tableName\"...", BaseConsole::FG_YELLOW);

        $this->db->createCommand()
            ->createTable(
                $this->migrationTable,
                ['version' => 'VARCHAR(180) NOT NULL PRIMARY KEY', 'apply_time' => 'INTEGER']
            )
            ->execute();

        $this->db->createCommand()
            ->insert(
                $this->migrationTable,
                ['version' => self::BASE_MIGRATION, 'apply_time' => time()]
            )
            ->execute();

        $this->stdout("Done.\n", BaseConsole::FG_GREEN);
    }
}
