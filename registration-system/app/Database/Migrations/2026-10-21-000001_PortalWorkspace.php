<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class PortalWorkspace extends Migration
{
    public function up()
    {
        $this->addIfMissing('customers', 'is_active', ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1]);
        $this->addIfMissing('employees', 'role', ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'employee']);
        $this->addIfMissing('employees', 'is_active', ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1]);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'base_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'is_available' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('service_categories', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'category' => ['type' => 'VARCHAR', 'constraint' => 60],
            'description' => ['type' => 'TEXT', 'null' => true],
            'price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true],
            'is_available' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('products', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'service_category_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'preferred_date' => ['type' => 'DATE'],
            'service_address' => ['type' => 'TEXT'],
            'notes' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'estimated_cost' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'assigned_employee_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'assigned_by' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('customer_id');
        $this->forge->addKey('status');
        $this->forge->addKey('preferred_date');
        $this->forge->addKey('assigned_employee_id');
        $this->forge->createTable('service_bookings', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'recipient_role' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'customer'],
            'recipient_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'null' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'body' => ['type' => 'TEXT'],
            'is_read' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['recipient_role', 'recipient_id']);
        $this->forge->createTable('notifications', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 180],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'created_at' => ['type' => 'DATETIME'],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['customer_id', 'status']);
        $this->forge->createTable('support_tickets', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'sender_role' => ['type' => 'VARCHAR', 'constraint' => 20],
            'sender_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'message' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('ticket_id');
        $this->forge->createTable('support_ticket_messages', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'latitude' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'longitude' => ['type' => 'DECIMAL', 'constraint' => '10,7', 'null' => true],
            'recorded_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['booking_id', 'employee_id']);
        $this->forge->createTable('work_checkins', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'employee_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'report_type' => ['type' => 'VARCHAR', 'constraint' => 40],
            'details' => ['type' => 'TEXT'],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('booking_id');
        $this->forge->createTable('work_reports', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'constraint' => 20, 'unsigned' => true],
            'customer_id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true],
            'amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'reference' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'recorded'],
            'paid_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('booking_id');
        $this->forge->addKey('customer_id');
        $this->forge->createTable('payments', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 10, 'unsigned' => true, 'auto_increment' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => 160],
            'body' => ['type' => 'TEXT'],
            'target_role' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'all'],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('announcements', true);

        $this->seedCatalog();
    }

    public function down()
    {
        foreach (['announcements', 'payments', 'work_reports', 'work_checkins', 'support_ticket_messages', 'support_tickets', 'notifications', 'service_bookings', 'products', 'service_categories'] as $table) {
            $this->forge->dropTable($table, true);
        }

        if ($this->db->fieldExists('is_active', 'customers')) {
            $this->forge->dropColumn('customers', 'is_active');
        }
        foreach (['role', 'is_active'] as $column) {
            if ($this->db->fieldExists($column, 'employees')) {
                $this->forge->dropColumn('employees', $column);
            }
        }
    }

    private function addIfMissing(string $table, string $column, array $definition): void
    {
        if (! $this->db->fieldExists($column, $table)) {
            $this->forge->addColumn($table, [$column => $definition]);
        }
    }

    private function seedCatalog(): void
    {
        $now = date('Y-m-d H:i:s');
        $services = [
            ['name' => 'Solar consultation', 'description' => 'Talk through your energy goals with a solar specialist.', 'base_price' => 0],
            ['name' => 'System design', 'description' => 'A site-aware design and load assessment for your property.', 'base_price' => 2000],
            ['name' => 'Permitting', 'description' => 'Guidance through the approvals required for your project.', 'base_price' => 1500],
            ['name' => 'Solar installation', 'description' => 'Professional installation and system commissioning.', 'base_price' => 15000],
            ['name' => 'Maintenance', 'description' => 'Keep your solar system clean, safe, and performing well.', 'base_price' => 1500],
            ['name' => 'Repair', 'description' => 'Troubleshooting and repair for solar equipment.', 'base_price' => 2500],
            ['name' => 'System monitoring', 'description' => 'Review your generation and system performance.', 'base_price' => 0],
        ];
        if ($this->db->table('service_categories')->countAllResults() === 0) {
            foreach ($services as &$service) {
                $service['is_available'] = 1;
                $service['created_at'] = $now;
            }
            unset($service);
            $this->db->table('service_categories')->insertBatch($services);
        }

        if ($this->db->table('products')->countAllResults() === 0) {
            $products = [
                ['name' => 'Monocrystalline solar panels', 'category' => 'Panels', 'description' => 'High-efficiency panels for residential and commercial rooftops.', 'price' => null],
                ['name' => 'Hybrid solar inverters', 'category' => 'Inverters', 'description' => 'Reliable conversion with grid and battery integration options.', 'price' => null],
                ['name' => 'Lithium battery storage', 'category' => 'Batteries', 'description' => 'Store surplus solar energy for use when you need it.', 'price' => null],
                ['name' => 'Roof racking and mounts', 'category' => 'Racking & mounting', 'description' => 'Secure mounting hardware selected for the roof and site.', 'price' => null],
                ['name' => 'Solar cables and connectors', 'category' => 'Wires', 'description' => 'Weather-resistant wiring and system connection components.', 'price' => null],
            ];
            foreach ($products as &$product) {
                $product['is_available'] = 1;
                $product['created_at'] = $now;
            }
            unset($product);
            $this->db->table('products')->insertBatch($products);
        }
    }
}
