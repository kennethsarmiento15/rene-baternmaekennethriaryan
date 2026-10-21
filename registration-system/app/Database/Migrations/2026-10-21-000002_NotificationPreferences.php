<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NotificationPreferences extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('category', 'notifications')) {
            $this->forge->addColumn('notifications', ['category' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'service']]);
        }
        if (! $this->db->fieldExists('category', 'announcements')) {
            $this->forge->addColumn('announcements', ['category' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'update']]);
        }

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'account_role' => ['type' => 'VARCHAR', 'constraint' => 20],
            'account_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'service_updates' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'marketing_updates' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'updated_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['account_role', 'account_id'], false, true);
        $this->forge->createTable('account_notification_preferences', true);
    }

    public function down()
    {
        $this->forge->dropTable('account_notification_preferences', true);
        foreach (['notifications', 'announcements'] as $table) {
            if ($this->db->fieldExists('category', $table)) {
                $this->forge->dropColumn($table, 'category');
            }
        }
    }
}
