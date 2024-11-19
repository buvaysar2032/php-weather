<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `social_network`.
 * Has foreign keys to the tables:
 *
 * - `user`
 */
class m130524_201520_create_social_network_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%social_network}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'user_id' => $this->integer(11)->notNull()->comment('ID пользователя'),
            'social_network_id' => $this->string(10)->notNull()->comment('ID/тип соц сети'),
            'user_auth_id' => $this->string(300)->notNull()->comment('ID пользователя в соц. сети'),
            'access_token' => $this->string(300)->comment('Токен доступа'),
            'last_auth_date' => $this->integer(11)->comment('Дата последней авторизации'),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-social_network-user_id}}',
            '{{%social_network}}',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            '{{%fk-social_network-user_id}}',
            '{{%social_network}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function safeDown(): void
    {
        // drops foreign key for table `user`
        $this->dropForeignKey(
            '{{%fk-social_network-user_id}}',
            '{{%social_network}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-social_network-user_id}}',
            '{{%social_network}}'
        );

        $this->dropTable('{{%social_network}}');
    }
}
