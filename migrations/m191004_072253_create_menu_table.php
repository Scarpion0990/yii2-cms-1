<?php

use yii\db\Migration;

/**
 * Handles the creation of table `cms_menu`.
 */
class m191004_072253_create_menu_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%cms_menu}}', [
            'id' => $this->primaryKey(),
            'title_0' => $this->string(),
            'title_1' => $this->string(),
            'title_2' => $this->string(),
            'title_3' => $this->string(),
            'type' => $this->tinyInteger()->notNull(),
            'type_helper' => $this->string(),
            'created_at' => $this->integer()->unsigned()->notNull(),
            'updated_at' => $this->integer()->unsigned()->notNull(),
        ], $tableOptions);
    }


    public function safeDown()
    {
        $this->dropTable('cms_menu');
    }
}
