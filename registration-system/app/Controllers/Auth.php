<?php

namespace App\Controllers;

use App\Models\AccountModel;
use Throwable;

class Auth extends BaseController
{
    public function register()
    {
        $data = [
            'role'        => trim((string) $this->request->getPost('role')),
            'firstName'   => trim((string) $this->request->getPost('firstName')),
            'lastName'    => trim((string) $this->request->getPost('lastName')),
            'middleName'  => trim((string) $this->request->getPost('middleName')),
            'birthdate'   => trim((string) $this->request->getPost('birthdate')),
            'gender'      => trim((string) $this->request->getPost('gender')),
            'email'       => trim((string) $this->request->getPost('email')),
            'phone'       => trim((string) $this->request->getPost('phone')),
            'address'     => trim((string) $this->request->getPost('address')),
            'department'  => trim((string) $this->request->getPost('department')),
            'username'    => trim((string) $this->request->getPost('username')),
            'password'    => (string) $this->request->getPost('password'),
            'confirmPassword' => (string) $this->request->getPost('confirmPassword'),
        ];

        $errors = $this->registrationErrors($data);
        $safeOldInput = $data;
        unset($safeOldInput['password'], $safeOldInput['confirmPassword']);

        if ($errors !== []) {
            return redirect()->to(site_url('/'))
                ->with('registerNotice', 'Please check the highlighted fields and try again.')
                ->with('registerErrors', $errors)
                ->with('registrationData', $safeOldInput);
        }

        try {
            $accounts = new AccountModel();
            if ($accounts->usernameExists($data['username'])) {
                return redirect()->to(site_url('/'))
                    ->with('registerNotice', 'That username is already in use.')
                    ->with('registerErrors', ['username' => 'Choose a different username.'])
                    ->with('registrationData', $safeOldInput);
            }

            $accounts->create($data['role'], $data);
        } catch (Throwable $exception) {
            log_message('error', 'Registration failed: {message}', ['message' => $exception->getMessage()]);

            return redirect()->to(site_url('/'))
                ->with('registerNotice', 'We could not save the account. Check that the customers and employees tables are set up, then try again.')
                ->with('registerFailed', true)
                ->with('registrationData', $safeOldInput);
        }

        return redirect()->to(site_url('/'))
            ->with('registerNotice', 'Registration successful. Your account was saved to the database. You can log in now.')
            ->with('actionNotice', 'Registration successful. Your account was saved to the database. Please log in.')
            ->with('actionNoticeType', 'success')
            ->with('openPanel', 'login');
    }

    public function login()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = (string) $this->request->getPost('password');

        if ($username === '' || $password === '') {
            return redirect()->to(site_url('/#login'))
                ->with('loginNotice', 'Enter your username and password.')
                ->with('openPanel', 'login');
        }

        try {
            $accounts = new AccountModel();
            $user = $accounts->authenticate($username, $password);
            if ($user !== null) {
                $accounts->recordLogin($user);
            }
        } catch (Throwable $exception) {
            log_message('error', 'Login failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->to(site_url('/#login'))
                ->with('loginNotice', 'We could not finish recording this login. Check the MySQL connection and database setup, then try again.')
                ->with('openPanel', 'login');
        }

        if ($user === null) {
            return redirect()->to(site_url('/#login'))
                ->with('loginNotice', 'Invalid username or password.')
                ->with('openPanel', 'login');
        }

        session()->regenerate(true);
        session()->set('authUser', $user);

        return redirect()->to(site_url('/'))
            ->with('actionNotice', 'Login successful. This login was recorded in the database. Welcome back, ' . ($user['firstName'] ?: 'to Sun Son Solar') . '!')
            ->with('actionNoticeType', 'success');
    }

    public function logout()
    {
        session()->remove('authUser');
        session()->regenerate(true);

        return redirect()->to(site_url('/'));
    }

    private function registrationErrors(array $data): array
    {
        $errors = [];

        if (! in_array($data['role'], ['customer', 'employee'], true)) {
            $errors['role'] = 'Choose customer or employee registration.';
        }
        foreach (['firstName' => 'First name', 'lastName' => 'Last name'] as $field => $label) {
            if ($data[$field] === '') {
                $errors[$field] = $label . ' is required.';
            } elseif (mb_strlen($data[$field]) > 100) {
                $errors[$field] = $label . ' must be 100 characters or fewer.';
            }
        }
        if ($data['middleName'] !== '' && mb_strlen($data['middleName']) > 100) {
            $errors['middleName'] = 'Middle name must be 100 characters or fewer.';
        }
        if ($data['birthdate'] !== '') {
            $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $data['birthdate']);
            $dateErrors = \DateTimeImmutable::getLastErrors();
            if (! $date || $date->format('Y-m-d') !== $data['birthdate'] || ($dateErrors && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))) {
                $errors['birthdate'] = 'Enter a valid birthdate.';
            } elseif ($data['birthdate'] > date('Y-m-d')) {
                $errors['birthdate'] = 'Birthdate cannot be in the future.';
            }
        }
        if (! in_array($data['gender'], ['', 'Male', 'Female', 'Prefer not to say'], true)) {
            $errors['gender'] = 'Choose a valid gender option.';
        }
        if ($data['email'] === '' || ! filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        } elseif (mb_strlen($data['email']) > 150) {
            $errors['email'] = 'Email must be 150 characters or fewer.';
        }
        if ($data['phone'] === '' || ! preg_match('/^[0-9+()\-\s]{7,20}$/', $data['phone'])) {
            $errors['phone'] = 'Enter a valid phone number.';
        }
        if (mb_strlen($data['address']) > 2000) {
            $errors['address'] = 'Address must be 2,000 characters or fewer.';
        }
        if (! preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $data['username'])) {
            $errors['username'] = 'Use 3-50 letters, numbers, dots, dashes, or underscores.';
        }
        if ($data['role'] === 'employee' && $data['department'] === '') {
            $errors['department'] = 'Department is required for employee accounts.';
        } elseif (mb_strlen($data['department']) > 100) {
            $errors['department'] = 'Department must be 100 characters or fewer.';
        }
        if (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        } elseif (strlen($data['password']) > 72) {
            $errors['password'] = 'Password must be no longer than 72 bytes.';
        }
        if ($data['confirmPassword'] === '' || ! hash_equals($data['password'], $data['confirmPassword'])) {
            $errors['confirmPassword'] = 'Passwords do not match.';
        }

        return $errors;
    }
}
