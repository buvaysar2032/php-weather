<?php

namespace common\modules\user\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `user_ext`.
 * Has foreign keys to the tables:
 *
 * - `user`
 */
class m130524_201510_create_user_ext_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    final public function safeUp(): void
    {
        $this->createTable('{{%user_ext}}', [
            'id' => $this->primaryKey()->comment('ID'),
            'user_id' => $this->integer(11)->notNull()->comment('ID пользователя'),
            // name
            'first_name' => $this->string(30)->comment('Имя'),
            'middle_name' => $this->string(30)->comment('Отчество'),
            'last_name' => $this->string(30)->comment('Фамилия'),
            // phone
            'phone' => $this->string(25)->comment('Номер телефона'),
            //
            'rules_accepted' => $this->boolean()->notNull()->defaultValue(0)->comment('Согласие с правилами'),
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_ext-user_id}}',
            '{{%user_ext}}',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            '{{%fk-user_ext-user_id}}',
            '{{%user_ext}}',
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
            '{{%fk-user_ext-user_id}}',
            '{{%user_ext}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_ext-user_id}}',
            '{{%user_ext}}'
        );

        $this->dropTable('{{%user_ext}}');
    }
}
