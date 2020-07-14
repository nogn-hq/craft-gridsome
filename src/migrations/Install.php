<?php

namespace nogn\craftgridsome\migrations;

use craft\db\Migration;

/**
 * Install migration.
 *
 * @author Ben Sheedy
 * @since 0.1
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Create the nogn_sites table
        $this->createTable('{{%nogn_sites}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'url' => $this->string()->notNull(),
            'sectionIds' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Drop the DB table
        $this->dropTableIfExists('{{%nogn_sites}}');
    }
}
