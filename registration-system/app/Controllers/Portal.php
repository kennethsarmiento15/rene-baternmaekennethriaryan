<?php

namespace App\Controllers;

use App\Models\PortalModel;
use DateTimeImmutable;
use Throwable;

class Portal extends BaseController
{
    private const DEPARTMENTS = ['Administration', 'IT', 'Dispatch', 'Accounting', 'HR', 'Marketing', 'Sales', 'Customer Service'];
    private const EMPLOYEE_ROLES = ['employee', 'dispatcher', 'technician', 'hr'];

    public function index()
    {
        $user = session()->get('authUser');
        if (! is_array($user)) {
            return redirect()->to(site_url('/#login'))->with('openPanel', 'login')->with('loginNotice', 'Log in to open your Sun Son Solar workspace.');
        }

        try {
            $dashboard = (new PortalModel())->dashboard($user);
            $requestedMonth = trim((string) $this->request->getGet('month'));
            $monthDate = \DateTimeImmutable::createFromFormat('!Y-m', $requestedMonth);
            $dateIssues = \DateTimeImmutable::getLastErrors();
            if (! $monthDate || $monthDate->format('Y-m') !== $requestedMonth || ($dateIssues && ($dateIssues['warning_count'] > 0 || $dateIssues['error_count'] > 0))) {
                $monthDate = new \DateTimeImmutable('first day of this month');
            }
            $dashboard['calendarMonth'] = $monthDate->format('Y-m');

            return view('portal/index', $dashboard + [
                'user' => $user,
                'notice' => session()->getFlashdata('portalNotice'),
                'noticeType' => session()->getFlashdata('portalNoticeType') ?? 'success',
                'departments' => self::DEPARTMENTS,
                'employeeRoles' => self::EMPLOYEE_ROLES,
            ]);
        } catch (Throwable $exception) {
            log_message('error', 'Portal dashboard load failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->to(site_url('/'))->with('actionNotice', 'Your workspace is not ready yet. Run the CodeIgniter migration described in INSTALL.md.')->with('actionNoticeType', 'error');
        }
    }

    public function profile()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        $data = $this->profileInput();
        $errors = $this->profileErrors($data);
        if ($errors !== []) return $this->fail('Please correct the profile details.', $errors, '#profile');
        return $this->perform(fn () => (new PortalModel())->updateProfile($user, $data), 'Profile updated successfully.', '#profile');
    }

    public function password()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        $current = (string) $this->request->getPost('current_password');
        $next = (string) $this->request->getPost('new_password');
        $confirm = (string) $this->request->getPost('confirm_password');
        if (strlen($next) < 12 || strlen($next) > 72) return $this->fail('Use a password between 12 and 72 characters.', [], '#security');
        if (! hash_equals($next, $confirm)) return $this->fail('The new passwords do not match.', [], '#security');
        return $this->perform(fn () => (new PortalModel())->changePassword($user, $current, $next), 'Password changed. Use the new password next time you log in.', '#security');
    }

    public function book()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'customer') return $this->denied();
        $serviceId = (int) $this->request->getPost('service_category_id');
        $date = trim((string) $this->request->getPost('preferred_date'));
        $address = trim((string) $this->request->getPost('service_address'));
        $notes = trim((string) $this->request->getPost('notes'));
        $picked = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $dateIssues = DateTimeImmutable::getLastErrors();
        if ($serviceId < 1 || ! $picked || $picked->format('Y-m-d') !== $date || ($dateIssues && ($dateIssues['warning_count'] || $dateIssues['error_count'])) || $date < date('Y-m-d')) {
            return $this->fail('Choose a service and a valid date from today onward.', [], '#schedule');
        }
        if ($address === '' || mb_strlen($address) > 1000 || mb_strlen($notes) > 3000) return $this->fail('Enter a service address and keep notes under 3,000 characters.', [], '#schedule');
        return $this->perform(fn () => (new PortalModel())->createBooking($user, $serviceId, $date, $address, $notes), 'Your service request was sent to the Sun Son Solar team.', '#schedule');
    }

    public function cancelBooking()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'customer') return $this->denied();
        $id = (int) $this->request->getPost('booking_id');
        return $this->perform(fn () => (new PortalModel())->cancelBooking($user, $id), 'Pending request cancelled.', '#schedule');
    }

    public function ticket()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'customer') return $this->denied();
        $subject = trim((string) $this->request->getPost('subject'));
        $message = trim((string) $this->request->getPost('message'));
        if ($subject === '' || mb_strlen($subject) > 180 || $message === '' || mb_strlen($message) > 5000) return $this->fail('Add a short subject and a message (up to 5,000 characters).', [], '#support');
        return $this->perform(fn () => (new PortalModel())->createTicket($user, $subject, $message), 'Your support request has been sent to the team.', '#support');
    }

    public function ticketReply()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        $message = trim((string) $this->request->getPost('message'));
        $ticketId = (int) $this->request->getPost('ticket_id');
        if ($ticketId < 1 || $message === '' || mb_strlen($message) > 5000) return $this->fail('Write a reply of up to 5,000 characters.', [], '#support');
        return $this->perform(fn () => (new PortalModel())->replyToTicket($user, $ticketId, $message), 'Reply sent.', '#support');
    }

    public function createEmployee()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (! in_array($user['role'] ?? '', ['admin', 'hr'], true)) return $this->denied();
        $data = [
            'first_name' => trim((string) $this->request->getPost('first_name')),
            'middle_name' => trim((string) $this->request->getPost('middle_name')),
            'last_name' => trim((string) $this->request->getPost('last_name')),
            'birthdate' => trim((string) $this->request->getPost('birthdate')),
            'gender' => trim((string) $this->request->getPost('gender')),
            'email' => trim((string) $this->request->getPost('email')),
            'phone_number' => trim((string) $this->request->getPost('phone_number')),
            'address' => trim((string) $this->request->getPost('address')),
            'department' => trim((string) $this->request->getPost('department')),
            'role' => trim((string) $this->request->getPost('role')),
            'username' => trim((string) $this->request->getPost('username')),
            'password' => (string) $this->request->getPost('password'),
        ];
        $errors = $this->accountInputErrors($data, true);
        if ($data['birthdate'] === '') $errors['birthdate'] = 'Birthdate is required.';
        if (! in_array($data['gender'], ['Male', 'Female', 'Prefer not to say'], true)) $errors['gender'] = 'Choose a gender option.';
        if ($data['address'] === '') $errors['address'] = 'Enter the employee address.';
        if (! in_array($data['department'], self::DEPARTMENTS, true)) $errors['department'] = 'Choose a department from the list.';
        if (! in_array($data['role'], self::EMPLOYEE_ROLES, true)) $errors['role'] = 'Choose a standard employee role.';
        if (($user['role'] ?? '') === 'hr' && $data['role'] !== 'employee') $errors['role'] = 'HR can create standard employee accounts. Ask an administrator to assign elevated access.';
        if ($errors !== []) return $this->fail('Check the employee details and try again.', $errors, '#users');
        return $this->perform(fn () => (new PortalModel())->createEmployee($data), 'Employee account created.', '#users');
    }

    public function updateUser()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (! in_array($user['role'] ?? '', ['admin', 'hr'], true)) return $this->denied();
        $id = (int) $this->request->getPost('id');
        $kind = (string) $this->request->getPost('account_type');
        if (($user['role'] ?? '') === 'hr' && $kind !== 'employee') return $this->denied();
        $data = [
            'first_name' => trim((string) $this->request->getPost('first_name')),
            'middle_name' => trim((string) $this->request->getPost('middle_name')),
            'last_name' => trim((string) $this->request->getPost('last_name')),
            'email' => trim((string) $this->request->getPost('email')),
            'phone_number' => trim((string) $this->request->getPost('phone_number')),
            'address' => trim((string) $this->request->getPost('address')),
            'department' => trim((string) $this->request->getPost('department')),
            'role' => trim((string) $this->request->getPost('role')),
        ];
        if (($user['role'] ?? '') === 'hr') {
            $target = db_connect()->table('employees')->select('role')->where('id', $id)->get(1)->getRowArray();
            if ($target === null || $target['role'] === 'admin' || ($data['role'] ?? '') !== $target['role']) return $this->denied();
        }
        if ($id < 1 || ! in_array($kind, ['customer', 'employee'], true)) return $this->fail('Choose a valid account.', [], '#users');
        $errors = $this->profileErrors($data);
        if ($kind === 'employee' && (! in_array($data['department'], self::DEPARTMENTS, true) || ! in_array($data['role'], self::EMPLOYEE_ROLES, true))) $errors['role'] = 'Choose a valid department and role.';
        if ($errors !== []) return $this->fail('Check the account details and try again.', $errors, '#users');
        return $this->perform(fn () => (new PortalModel())->updateUser($id, $kind, $data), 'Account details updated.', '#users');
    }

    public function userStatus()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (! in_array($user['role'] ?? '', ['admin', 'hr'], true)) return $this->denied();
        $id = (int) $this->request->getPost('id');
        $kind = (string) $this->request->getPost('account_type');
        if (($user['role'] ?? '') === 'hr' && $kind !== 'employee') return $this->denied();
        if ($id === (int) $user['id'] && $kind === 'employee') return $this->fail('You cannot deactivate the account you are using.', [], '#users');
        if ($id < 1 || ! in_array($kind, ['customer', 'employee'], true)) return $this->fail('Choose a valid account.', [], '#users');
        $active = $this->request->getPost('active') === '1';
        return $this->perform(fn () => (new PortalModel())->setUserActive($id, $kind, $active), $active ? 'Account reactivated.' : 'Account deactivated.', '#users');
    }

    public function scheduleUpdate()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (! in_array($user['role'] ?? '', ['admin', 'dispatcher'], true)) return $this->denied();
        $bookingId = (int) $this->request->getPost('booking_id');
        $status = trim((string) $this->request->getPost('status'));
        $employeeId = (int) $this->request->getPost('assigned_employee_id') ?: null;
        return $this->perform(fn () => (new PortalModel())->updateBooking($bookingId, $status, $employeeId, (int) $user['id']), 'Schedule updated and the customer has been notified.', '#schedule-board');
    }

    public function checkIn()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'technician') return $this->denied();
        $lat = filter_var($this->request->getPost('latitude'), FILTER_VALIDATE_FLOAT);
        $lon = filter_var($this->request->getPost('longitude'), FILTER_VALIDATE_FLOAT);
        if ($lat === false || $lon === false || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) return $this->fail('Allow location access on your device, then tap Check in again.', [], '#field-jobs');
        $bookingId = (int) $this->request->getPost('booking_id');
        return $this->perform(fn () => (new PortalModel())->checkIn($user, $bookingId, $lat, $lon), 'Your arrival time and coordinates have been recorded.', '#field-jobs');
    }

    public function workReport()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'technician') return $this->denied();
        $bookingId = (int) $this->request->getPost('booking_id');
        $type = trim((string) $this->request->getPost('report_type'));
        $details = trim((string) $this->request->getPost('details'));
        if (! in_array($type, ['equipment', 'customer', 'safety', 'other'], true) || $details === '' || mb_strlen($details) > 5000) return $this->fail('Choose a report type and add details (up to 5,000 characters).', [], '#field-jobs');
        return $this->perform(fn () => (new PortalModel())->submitWorkReport($user, $bookingId, $type, $details), 'Field report sent to the admin team.', '#field-jobs');
    }

    public function announcement()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'admin') return $this->denied();
        $title = trim((string) $this->request->getPost('title'));
        $body = trim((string) $this->request->getPost('body'));
        $target = trim((string) $this->request->getPost('target_role'));
        $category = trim((string) $this->request->getPost('category'));
        if ($title === '' || mb_strlen($title) > 160 || $body === '' || mb_strlen($body) > 3000 || ! in_array($target, ['all', 'customer', 'employee'], true) || ! in_array($category, ['update', 'promotion'], true)) return $this->fail('Add a title, message, valid audience, and update type.', [], '#announcements');
        return $this->perform(fn () => (new PortalModel())->publishAnnouncement($title, $body, $target, $category), 'Announcement published and notification sent.', '#announcements');
    }

    public function service()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'admin') return $this->denied();
        $id = (int) $this->request->getPost('id') ?: null;
        $name = trim((string) $this->request->getPost('name'));
        $description = trim((string) $this->request->getPost('description'));
        $price = filter_var($this->request->getPost('base_price'), FILTER_VALIDATE_FLOAT);
        if ($name === '' || mb_strlen($name) > 100 || mb_strlen($description) > 3000 || $price === false || $price < 0) return $this->fail('Enter a service name and a valid non-negative price.', [], '#settings');
        return $this->perform(fn () => (new PortalModel())->saveService($id, $name, $description, (float) $price, $this->request->getPost('is_available') === '1'), 'Service settings saved.', '#settings');
    }

    public function product()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'admin') return $this->denied();
        $id = (int) $this->request->getPost('id') ?: null;
        $name = trim((string) $this->request->getPost('name'));
        $category = trim((string) $this->request->getPost('category'));
        $description = trim((string) $this->request->getPost('description'));
        $rawPrice = trim((string) $this->request->getPost('price'));
        $price = $rawPrice === '' ? null : filter_var($rawPrice, FILTER_VALIDATE_FLOAT);
        if ($name === '' || mb_strlen($name) > 120 || $category === '' || mb_strlen($category) > 60 || mb_strlen($description) > 3000 || ($price !== null && ($price === false || $price < 0))) return $this->fail('Enter valid product details and a non-negative price (or leave price blank).', [], '#settings');
        return $this->perform(fn () => (new PortalModel())->saveProduct($id, $name, $category, $description, $price === false ? null : $price, $this->request->getPost('is_available') === '1'), 'Product catalog updated.', '#settings');
    }

    public function payment()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        if (($user['role'] ?? '') !== 'admin') return $this->denied();
        $bookingId = (int) $this->request->getPost('booking_id');
        $amount = filter_var($this->request->getPost('amount'), FILTER_VALIDATE_FLOAT);
        $reference = trim((string) $this->request->getPost('reference'));
        if ($bookingId < 1 || $amount === false || $amount <= 0 || mb_strlen($reference) > 100) return $this->fail('Choose a booking and enter a valid amount.', [], '#schedule-board');
        return $this->perform(fn () => (new PortalModel())->recordPayment($bookingId, (float) $amount, $reference), 'Payment recorded in the service history.', '#schedule-board');
    }

    public function notificationsRead()
    {
        $user = $this->signedIn();
        if ($user === null) return $this->loginRequired();
        return $this->perform(fn () => (new PortalModel())->markNotificationsRead($user), 'Notifications marked as read.', '#notifications');
    }

    private function signedIn(): ?array
    {
        $user = session()->get('authUser');
        if (! is_array($user) || empty($user['id'])) return null;
        $table = ($user['role'] ?? '') === 'customer' ? 'customers' : 'employees';
        $active = db_connect()->table($table)->select('is_active')->where('id', (int) $user['id'])->get(1)->getRowArray();
        if ($active === null || ! (bool) $active['is_active']) {
            session()->remove('authUser');
            return null;
        }
        return $user;
    }

    private function profileInput(): array
    {
        return [
            'first_name' => trim((string) $this->request->getPost('first_name')),
            'middle_name' => trim((string) $this->request->getPost('middle_name')),
            'last_name' => trim((string) $this->request->getPost('last_name')),
            'birthdate' => trim((string) $this->request->getPost('birthdate')),
            'gender' => trim((string) $this->request->getPost('gender')),
            'email' => trim((string) $this->request->getPost('email')),
            'phone_number' => trim((string) $this->request->getPost('phone_number')),
            'address' => trim((string) $this->request->getPost('address')),
            'service_updates' => $this->request->getPost('service_updates') === '1',
            'marketing_updates' => $this->request->getPost('marketing_updates') === '1',
        ];
    }

    private function profileErrors(array $data): array
    {
        $errors = [];
        foreach (['first_name' => 'First name', 'last_name' => 'Last name'] as $field => $label) {
            if ($data[$field] === '' || mb_strlen($data[$field]) > 100) $errors[$field] = $label . ' is required (up to 100 characters).';
        }
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) $errors['email'] = 'Enter a valid email address.';
        if (! preg_match('/^[0-9+()\-\s]{7,20}$/', $data['phone_number'])) $errors['phone_number'] = 'Enter a valid phone number.';
        if (mb_strlen($data['middle_name']) > 100 || mb_strlen($data['address']) > 2000) $errors['address'] = 'Check the length of your middle name and address.';
        $gender = $data['gender'] ?? '';
        $birthdateValue = $data['birthdate'] ?? '';
        if ($gender !== '' && ! in_array($gender, ['Male', 'Female', 'Prefer not to say'], true)) $errors['gender'] = 'Choose a valid gender option.';
        if ($birthdateValue !== '') {
            $birthdate = DateTimeImmutable::createFromFormat('!Y-m-d', $birthdateValue);
            $issues = DateTimeImmutable::getLastErrors();
            if (! $birthdate || $birthdate->format('Y-m-d') !== $birthdateValue || ($issues && ($issues['warning_count'] || $issues['error_count'])) || $birthdateValue > date('Y-m-d')) $errors['birthdate'] = 'Enter a valid birthdate.';
        }
        return $errors;
    }

    private function accountInputErrors(array $data, bool $withPassword): array
    {
        $errors = $this->profileErrors($data);
        if (! preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $data['username'])) $errors['username'] = 'Username must be 3-50 letters, numbers, dots, dashes, or underscores.';
        if ($withPassword && (strlen($data['password']) < 12 || strlen($data['password']) > 72)) $errors['password'] = 'Use a password between 12 and 72 characters.';
        return $errors;
    }

    private function perform(callable $action, string $success, string $anchor)
    {
        try {
            $action();
            return redirect()->to(site_url('portal' . $anchor))->with('portalNotice', $success)->with('portalNoticeType', 'success');
        } catch (Throwable $exception) {
            log_message('error', 'Portal action failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->to(site_url('portal' . $anchor))->with('portalNotice', $exception->getMessage())->with('portalNoticeType', 'error');
        }
    }

    private function fail(string $message, array $errors, string $anchor)
    {
        return redirect()->to(site_url('portal' . $anchor))->with('portalNotice', $message)->with('portalNoticeType', 'error')->with('portalErrors', $errors);
    }

    private function loginRequired()
    {
        return redirect()->to(site_url('/#login'))->with('openPanel', 'login')->with('loginNotice', 'Log in to continue.');
    }

    private function denied()
    {
        return redirect()->to(site_url('portal'))->with('portalNotice', 'Your account does not have permission to do that.')->with('portalNoticeType', 'error');
    }
}
