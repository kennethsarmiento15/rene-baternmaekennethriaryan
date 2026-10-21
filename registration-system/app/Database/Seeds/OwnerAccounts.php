<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\CLI\CLI;
use RuntimeException;

class OwnerAccounts extends Seeder
{
    public function run()
    {
        $katPassword = getenv('SUNSON_OWNER_KAT_PASSWORD') ?: '';
        $solPassword = getenv('SUNSON_OWNER_SOL_PASSWORD') ?: '';
        if ($katPassword === '' || $solPassword === '') {
            throw new RuntimeException('Set SUNSON_OWNER_KAT_PASSWORD and SUNSON_OWNER_SOL_PASSWORD in the CLI environment before running this seeder.');
        }

        $owners = [
            [
                'first_name' => 'Katherine', 'middle_name' => 'Olap', 'last_name' => 'Sinagaraw',
                'birthdate' => '1990-07-01', 'gender' => null,
                'email' => 'katherine.sinagaraw@sunsonsolar.com', 'phone_number' => '09291230983',
                'address' => 'Pasig City', 'department' => 'Administration', 'role' => 'admin',
                'username' => 'KittyKat16', 'password' => $katPassword,
            ],
            [
                'first_name' => 'Sol', 'middle_name' => 'Sun', 'last_name' => 'Solis',
                'birthdate' => '1967-01-08', 'gender' => null,
                // The interview gives only "sol.solis" for the email. This uses the company domain inferred from Katherine's address.
                'email' => 'sol.solis@sunsonsolar.com', 'phone_number' => '09291230983',
                'address' => 'Pasig City', 'department' => 'IT', 'role' => 'admin',
                'username' => 'admin', 'password' => $solPassword,
            ],
        ];

        foreach ($owners as $owner) {
            $customerCollision = $this->db->table('customers')->where('username', $owner['username'])->get(1)->getRowArray();
            if ($customerCollision !== null) {
                throw new RuntimeException('Owner username ' . $owner['username'] . ' is already assigned to a customer account. Resolve the username collision before seeding owners.');
            }
            $existing = $this->db->table('employees')->where('username', $owner['username'])->get(1)->getRowArray();
            if ($existing !== null) {
                if ($existing['first_name'] !== $owner['first_name'] || $existing['last_name'] !== $owner['last_name']) {
                    throw new RuntimeException('Owner username ' . $owner['username'] . ' belongs to a different employee record. Resolve the username collision before seeding owners.');
                }
                unset($owner['password']);
                $this->db->table('employees')->where('id', $existing['id'])->update($owner);
                CLI::write('Updated owner profile and admin access for ' . $existing['username'] . '; existing password was preserved.', 'yellow');
                continue;
            }

            $owner['password'] = password_hash($owner['password'], PASSWORD_DEFAULT);
            $owner['is_active'] = 1;
            $this->db->table('employees')->insert($owner);
            CLI::write('Created owner admin account: ' . $owner['username'], 'green');
        }
    }
}
