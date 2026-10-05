<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class AccountModel
{
    private BaseConnection $db;

    private const TABLES = [
        'customer' => 'customers',
        'employee' => 'employees',
    ];

    private const ALIASES = [
        'id'          => ['id', 'customerid', 'employeeid'],
        'first_name'  => ['firstname', 'givenname'],
        'last_name'   => ['lastname', 'familyname', 'surname'],
        'middle_name' => ['middlename'],
        'birthdate'   => ['birthdate', 'birthday', 'dateofbirth', 'dob'],
        'gender'      => ['gender', 'sex'],
        'email'       => ['email', 'emailaddress'],
        'phone'       => ['phonenumber', 'phone', 'contactnumber', 'mobilenumber', 'mobile'],
        'address'     => ['address', 'homeaddress'],
        'department'  => ['department', 'departmentname', 'position'],
        'username'    => ['username'],
        'password'    => ['password', 'passwordhash'],
    ];

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function usernameExists(string $username): bool
    {
        foreach (self::TABLES as $table) {
            $map = $this->columnMap($table);
            if (! isset($map['username'])) {
                throw new RuntimeException('The ' . $table . ' table needs a username column.');
            }

            $exists = $this->db->table($table)
                ->where($map['username'], $username)
                ->countAllResults() > 0;

            if ($exists) {
                return true;
            }
        }

        return false;
    }

    public function create(string $role, array $account): void
    {
        $table = $this->tableFor($role);
        $map = $this->columnMap($table);
        $required = ['first_name', 'last_name', 'email', 'phone', 'username', 'password'];
        $missing = array_values(array_filter($required, static fn ($field) => ! isset($map[$field])));

        if ($missing !== []) {
            throw new RuntimeException('The ' . $table . ' table is missing required columns: ' . implode(', ', $missing));
        }

        $values = [
            'first_name'  => $account['firstName'],
            'last_name'   => $account['lastName'],
            'middle_name' => $account['middleName'] !== '' ? $account['middleName'] : null,
            'birthdate'   => $account['birthdate'] !== '' ? $account['birthdate'] : null,
            'gender'      => $account['gender'] !== '' ? $account['gender'] : null,
            'email'       => $account['email'],
            'phone'       => $account['phone'],
            'address'     => $account['address'] !== '' ? $account['address'] : null,
            'department'  => $account['department'] !== '' ? $account['department'] : null,
            'username'    => $account['username'],
            'password'    => password_hash($account['password'], PASSWORD_DEFAULT),
        ];

        $insert = [];
        foreach ($values as $field => $value) {
            if (isset($map[$field])) {
                $insert[$map[$field]] = $value;
            }
        }

        $this->db->table($table)->insert($insert);
    }

    /** Returns a password-free profile, or null when the credentials do not match. */
    public function authenticate(string $username, string $password): ?array
    {
        foreach (self::TABLES as $role => $table) {
            $map = $this->columnMap($table);
            if (! isset($map['username'], $map['password'])) {
                throw new RuntimeException('The ' . $table . ' table needs username and password columns.');
            }

            $row = $this->db->table($table)
                ->where($map['username'], $username)
                ->get(1)
                ->getRowArray();

            if ($row === null) {
                continue;
            }

            $stored = (string) ($row[$map['password']] ?? '');
            $info = password_get_info($stored);
            $isHash = ($info['algo'] ?? null) !== null;
            $valid = $isHash ? password_verify($password, $stored) : hash_equals($stored, $password);

            if (! $valid) {
                continue;
            }

            if (! $isHash || password_needs_rehash($stored, PASSWORD_DEFAULT)) {
                $this->db->table($table)
                    ->where($map['username'], $username)
                    ->update([$map['password'] => password_hash($password, PASSWORD_DEFAULT)]);
            }

            return $this->profile($role, $row, $map);
        }

        return null;
    }

    private function profile(string $role, array $row, array $map): array
    {
        $read = static function (string $field) use ($row, $map): string {
            return isset($map[$field]) ? (string) ($row[$map[$field]] ?? '') : '';
        };

        return [
            'role'       => $role,
            'id'         => isset($map['id']) ? (string) ($row[$map['id']] ?? '') : '',
            'firstName'  => $read('first_name'),
            'lastName'   => $read('last_name'),
            'middleName' => $read('middle_name'),
            'email'      => $read('email'),
            'username'   => $read('username'),
            'department' => $read('department'),
        ];
    }

    private function tableFor(string $role): string
    {
        if (! isset(self::TABLES[$role])) {
            throw new RuntimeException('Unknown account type.');
        }

        return self::TABLES[$role];
    }

    private function columnMap(string $table): array
    {
        $actual = [];
        foreach ($this->db->getFieldNames($table) as $field) {
            $key = strtolower((string) preg_replace('/[^a-z0-9]/i', '', $field));
            $actual[$key] = $field;
        }

        $map = [];
        foreach (self::ALIASES as $standard => $aliases) {
            foreach ($aliases as $alias) {
                if (isset($actual[$alias])) {
                    $map[$standard] = $actual[$alias];
                    break;
                }
            }
        }

        return $map;
    }
}
