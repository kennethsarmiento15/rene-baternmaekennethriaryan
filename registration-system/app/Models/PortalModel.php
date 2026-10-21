<?php

namespace App\Models;

use CodeIgniter\Database\BaseConnection;
use RuntimeException;

class PortalModel
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function dashboard(array $user): array
    {
        $role = (string) ($user['role'] ?? 'customer');
        $id = (int) ($user['id'] ?? 0);
        $preferences = $this->preferences($user);
        $data = [
            'role' => $role,
            'profile' => $this->profile($user),
            'preferences' => $preferences,
            'notifications' => $this->notifications($user, $preferences),
            'announcements' => $this->announcements($role, $preferences),
        ];

        if ($role === 'customer') {
            $data['services'] = $this->db->table('service_categories')->where('is_available', 1)->orderBy('name')->get()->getResultArray();
            $data['bookings'] = $this->db->table('service_bookings b')
                ->select('b.*, s.name AS service_name, e.first_name AS tech_first_name, e.last_name AS tech_last_name')
                ->join('service_categories s', 's.id = b.service_category_id')
                ->join('employees e', 'e.id = b.assigned_employee_id', 'left')
                ->where('b.customer_id', $id)->orderBy('b.preferred_date', 'DESC')->get()->getResultArray();
            $data['tickets'] = $this->db->table('support_tickets')->where('customer_id', $id)->orderBy('updated_at', 'DESC')->get()->getResultArray();
            $data['ticketThreads'] = $this->ticketThreads($data['tickets']);
            $data['completedServices'] = $this->db->table('service_bookings b')->select('b.*, s.name AS service_name')
                ->join('service_categories s', 's.id = b.service_category_id')->where('b.customer_id', $id)->whereIn('b.status', ['completed', 'declined', 'cancelled'])->orderBy('b.updated_at', 'DESC')->get()->getResultArray();
            $data['history'] = $this->db->table('payments p')
                ->select('p.*, s.name AS service_name, b.preferred_date')
                ->join('service_bookings b', 'b.id = p.booking_id')
                ->join('service_categories s', 's.id = b.service_category_id')
                ->where('p.customer_id', $id)->orderBy('p.paid_at', 'DESC')->get()->getResultArray();
        } elseif ($role === 'technician') {
            $data['assignedJobs'] = $this->assignedJobs($id);
            $data['myReports'] = $this->db->table('work_reports')->where('employee_id', $id)->orderBy('created_at', 'DESC')->limit(10)->get()->getResultArray();
        } elseif (in_array($role, ['admin', 'dispatcher'], true)) {
            $data['bookings'] = $this->bookingOverview();
            $data['employees'] = $this->employees();
            if ($role === 'admin') {
                $data['tickets'] = $this->db->table('support_tickets t')->select('t.*, c.first_name, c.last_name, c.email')
                    ->join('customers c', 'c.id = t.customer_id')->orderBy('t.updated_at', 'DESC')->get()->getResultArray();
                $data['ticketThreads'] = $this->ticketThreads($data['tickets']);
                $data['serviceCategories'] = $this->db->table('service_categories')->orderBy('name')->get()->getResultArray();
                $data['products'] = $this->db->table('products')->orderBy('category')->orderBy('name')->get()->getResultArray();
                $data['reports'] = $this->reports();
                $data['stats'] = $this->stats();
                $data['checkins'] = $this->db->table('work_checkins w')->select('w.*, e.first_name, e.last_name, b.service_address, s.name AS service_name')
                    ->join('employees e', 'e.id = w.employee_id')->join('service_bookings b', 'b.id = w.booking_id')
                    ->join('service_categories s', 's.id = b.service_category_id')->orderBy('w.recorded_at', 'DESC')->limit(30)->get()->getResultArray();
                $data['workReports'] = $this->db->table('work_reports w')->select('w.*, e.first_name, e.last_name, s.name AS service_name')
                    ->join('employees e', 'e.id = w.employee_id')->join('service_bookings b', 'b.id = w.booking_id')
                    ->join('service_categories s', 's.id = b.service_category_id')->orderBy('w.created_at', 'DESC')->limit(30)->get()->getResultArray();
                $data['users'] = $this->users();
            }
        } elseif ($role === 'hr') {
            $data['users'] = $this->users(false);
        } else {
            $data['assignedJobs'] = $this->assignedJobs($id);
            $data['announcements'] = $this->announcements('employee');
        }

        return $data;
    }

    public function profile(array $user): array
    {
        $role = (string) ($user['role'] ?? 'customer');
        $table = $role === 'customer' ? 'customers' : 'employees';
        $row = $this->db->table($table)->where('id', (int) ($user['id'] ?? 0))->get(1)->getRowArray();
        if ($row === null) {
            throw new RuntimeException('Your account could not be found. Please sign in again.');
        }
        unset($row['password']);
        return $row;
    }

    public function updateProfile(array $user, array $data): void
    {
        $table = ($user['role'] ?? '') === 'customer' ? 'customers' : 'employees';
        $this->db->table($table)->where('id', (int) $user['id'])->update([
            'first_name' => $data['first_name'], 'middle_name' => $data['middle_name'] ?: null,
            'last_name' => $data['last_name'], 'birthdate' => $data['birthdate'] ?: null,
            'gender' => $data['gender'] ?: null, 'email' => $data['email'],
            'phone_number' => $data['phone_number'], 'address' => $data['address'] ?: null,
        ]);
        $this->savePreferences($user, $data['service_updates'], $data['marketing_updates']);
        $this->syncSessionName($user, $data['first_name'], $data['last_name']);
    }

    public function changePassword(array $user, string $current, string $next): void
    {
        $table = ($user['role'] ?? '') === 'customer' ? 'customers' : 'employees';
        $row = $this->db->table($table)->select('password')->where('id', (int) $user['id'])->get(1)->getRowArray();
        if ($row === null || ! password_verify($current, (string) $row['password'])) {
            throw new RuntimeException('Your current password does not match.');
        }
        $this->db->table($table)->where('id', (int) $user['id'])->update(['password' => password_hash($next, PASSWORD_DEFAULT)]);
    }

    public function createBooking(array $user, int $serviceId, string $date, string $address, string $notes): void
    {
        $service = $this->db->table('service_categories')->where('id', $serviceId)->where('is_available', 1)->get(1)->getRowArray();
        if ($service === null) {
            throw new RuntimeException('Choose an available service.');
        }
        $this->db->table('service_bookings')->insert([
            'customer_id' => (int) $user['id'], 'service_category_id' => $serviceId,
            'preferred_date' => $date, 'service_address' => $address,
            'notes' => $notes ?: null, 'status' => 'pending', 'estimated_cost' => $service['base_price'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        $bookingId = (int) $this->db->insertID();
        $this->notify('admin', null, 'New service request', $service['name'] . ' request from ' . $user['firstName'] . ' is waiting for review.');
        $this->notify('customer', (int) $user['id'], 'Request received', 'Your ' . $service['name'] . ' request is on the schedule for review. Reference #' . $bookingId . '.');
    }

    public function cancelBooking(array $user, int $bookingId): void
    {
        $booking = $this->db->table('service_bookings')->where('id', $bookingId)->where('customer_id', (int) $user['id'])->get(1)->getRowArray();
        if ($booking === null || $booking['status'] !== 'pending') {
            throw new RuntimeException('Only a pending request can be cancelled. Contact support if your schedule has been approved.');
        }
        $this->db->table('service_bookings')->where('id', $bookingId)->update(['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
        $this->notify('admin', null, 'Request cancelled', 'A customer cancelled pending service request #' . $bookingId . '.');
    }

    public function createTicket(array $user, string $subject, string $message): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->transStart();
        $this->db->table('support_tickets')->insert(['customer_id' => (int) $user['id'], 'subject' => $subject, 'status' => 'open', 'created_at' => $now, 'updated_at' => $now]);
        $ticketId = (int) $this->db->insertID();
        $this->db->table('support_ticket_messages')->insert(['ticket_id' => $ticketId, 'sender_role' => 'customer', 'sender_id' => (int) $user['id'], 'message' => $message, 'created_at' => $now]);
        $this->notify('admin', null, 'New support request', $subject);
        $this->db->transComplete();
        if (! $this->db->transStatus()) {
            throw new RuntimeException('The support request could not be saved.');
        }
    }

    public function replyToTicket(array $user, int $ticketId, string $message): void
    {
        if (($user['role'] ?? '') !== 'customer' && ! in_array($user['role'] ?? '', ['admin', 'dispatcher', 'hr'], true)) {
            throw new RuntimeException('Your account cannot reply to support conversations.');
        }
        $ticket = $this->db->table('support_tickets')->where('id', $ticketId)->get(1)->getRowArray();
        if ($ticket === null || (($user['role'] ?? '') === 'customer' && (int) $ticket['customer_id'] !== (int) $user['id'])) {
            throw new RuntimeException('This support thread is not available to your account.');
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('support_ticket_messages')->insert(['ticket_id' => $ticketId, 'sender_role' => (string) $user['role'], 'sender_id' => (int) $user['id'], 'message' => $message, 'created_at' => $now]);
        $this->db->table('support_tickets')->where('id', $ticketId)->update(['status' => 'open', 'updated_at' => $now]);
        $recipientRole = ($user['role'] ?? '') === 'customer' ? 'admin' : 'customer';
        $recipientId = $recipientRole === 'customer' ? (int) $ticket['customer_id'] : null;
        $this->notify($recipientRole, $recipientId, 'Support thread updated', 'There is a new reply on support request #' . $ticketId . '.');
    }

    public function createEmployee(array $data): void
    {
        if ((new AccountModel())->usernameExists($data['username'])) {
            throw new RuntimeException('That username is already in use.');
        }
        $accounts = new AccountModel($this->db);
        $accounts->create('employee', [
            'firstName' => $data['first_name'], 'lastName' => $data['last_name'], 'middleName' => $data['middle_name'],
            'birthdate' => $data['birthdate'], 'gender' => $data['gender'], 'email' => $data['email'],
            'phone' => $data['phone_number'], 'address' => $data['address'], 'department' => $data['department'],
            'username' => $data['username'], 'password' => $data['password'], 'accountRole' => $data['role'],
        ]);
    }

    public function updateUser(int $id, string $kind, array $data): void
    {
        $table = $kind === 'customer' ? 'customers' : 'employees';
        if ($kind === 'employee') {
            $target = $this->db->table('employees')->select('role')->where('id', $id)->get(1)->getRowArray();
            if ($target === null || $target['role'] === 'admin') throw new RuntimeException('Owner administrator accounts are managed from their own profile.');
        }
        $values = [
            'first_name' => $data['first_name'], 'middle_name' => $data['middle_name'] ?: null,
            'last_name' => $data['last_name'], 'email' => $data['email'], 'phone_number' => $data['phone_number'],
            'address' => $data['address'] ?: null,
        ];
        if ($kind === 'employee') {
            $values['department'] = $data['department'];
            $values['role'] = $data['role'];
        }
        $this->db->table($table)->where('id', $id)->update($values);
    }

    public function setUserActive(int $id, string $kind, bool $active): void
    {
        $table = $kind === 'customer' ? 'customers' : 'employees';
        if ($kind === 'employee') {
            $target = $this->db->table('employees')->select('role')->where('id', $id)->get(1)->getRowArray();
            if ($target === null || $target['role'] === 'admin') throw new RuntimeException('Owner administrator accounts cannot be deactivated here.');
        }
        $this->db->table($table)->where('id', $id)->update(['is_active' => $active ? 1 : 0]);
    }

    public function updateBooking(int $bookingId, string $status, ?int $employeeId, int $actorId): void
    {
        $booking = $this->db->table('service_bookings')->where('id', $bookingId)->get(1)->getRowArray();
        if ($booking === null) {
            throw new RuntimeException('Service request not found.');
        }
        $allowed = ['approved', 'declined', 'scheduled', 'in_progress', 'completed'];
        if (! in_array($status, $allowed, true)) {
            throw new RuntimeException('Choose a valid schedule status.');
        }
        if ($employeeId !== null) {
            $assignee = $this->db->table('employees')->where('id', $employeeId)->where('is_active', 1)->get(1)->getRowArray();
            if ($assignee === null || ! in_array($assignee['role'], ['technician', 'employee'], true)) {
                throw new RuntimeException('Choose an active technician or employee.');
            }
        }
        $this->db->table('service_bookings')->where('id', $bookingId)->update([
            'status' => $status, 'assigned_employee_id' => $employeeId,
            'assigned_by' => $actorId, 'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->notify('customer', (int) $booking['customer_id'], 'Schedule updated', 'Your service request #' . $bookingId . ' is now ' . str_replace('_', ' ', $status) . '.');
        if ($employeeId !== null) {
            $this->notify('technician', $employeeId, 'New site assignment', 'You have been assigned to service request #' . $bookingId . '.');
        }
    }

    public function checkIn(array $user, int $bookingId, ?float $latitude, ?float $longitude): void
    {
        $booking = $this->db->table('service_bookings')->where('id', $bookingId)->where('assigned_employee_id', (int) $user['id'])->get(1)->getRowArray();
        if ($booking === null) {
            throw new RuntimeException('You can only check in to an assigned job.');
        }
        $existing = $this->db->table('work_checkins')->where('booking_id', $bookingId)->where('employee_id', (int) $user['id'])->countAllResults();
        if ($existing > 0) {
            throw new RuntimeException('Your arrival has already been recorded for this job.');
        }
        $this->db->table('work_checkins')->insert([
            'booking_id' => $bookingId, 'employee_id' => (int) $user['id'], 'latitude' => $latitude,
            'longitude' => $longitude, 'recorded_at' => date('Y-m-d H:i:s'),
        ]);
        $this->notify('admin', null, 'Technician checked in', $user['firstName'] . ' checked in to service request #' . $bookingId . '.');
    }

    public function submitWorkReport(array $user, int $bookingId, string $type, string $details): void
    {
        $booking = $this->db->table('service_bookings')->where('id', $bookingId)->where('assigned_employee_id', (int) $user['id'])->get(1)->getRowArray();
        if ($booking === null) {
            throw new RuntimeException('You can only report on an assigned job.');
        }
        $this->db->table('work_reports')->insert(['booking_id' => $bookingId, 'employee_id' => (int) $user['id'], 'report_type' => $type, 'details' => $details, 'created_at' => date('Y-m-d H:i:s')]);
        $this->notify('admin', null, 'Technician field report', $type . ' reported for service request #' . $bookingId . '.');
    }

    public function publishAnnouncement(string $title, string $body, string $target, string $category): void
    {
        $this->db->table('announcements')->insert(['title' => $title, 'body' => $body, 'target_role' => $target, 'category' => $category, 'is_active' => 1, 'created_at' => date('Y-m-d H:i:s')]);
        $audiences = $target === 'all' ? ['customer', 'employee', 'technician', 'dispatcher'] : [$target];
        foreach ($audiences as $role) {
            $this->notify($role, null, $title, $body, $category);
        }
    }

    public function saveService(?int $id, string $name, string $description, float $price, bool $available): void
    {
        $values = ['name' => $name, 'description' => $description, 'base_price' => $price, 'is_available' => $available ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')];
        if ($id) {
            $this->db->table('service_categories')->where('id', $id)->update($values);
        } else {
            $values['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('service_categories')->insert($values);
        }
    }

    public function saveProduct(?int $id, string $name, string $category, string $description, ?float $price, bool $available): void
    {
        $values = ['name' => $name, 'category' => $category, 'description' => $description, 'price' => $price, 'is_available' => $available ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')];
        if ($id) {
            $this->db->table('products')->where('id', $id)->update($values);
        } else {
            $values['created_at'] = date('Y-m-d H:i:s');
            $this->db->table('products')->insert($values);
        }
    }

    public function recordPayment(int $bookingId, float $amount, string $reference): void
    {
        $booking = $this->db->table('service_bookings')->where('id', $bookingId)->get(1)->getRowArray();
        if ($booking === null || $amount <= 0) {
            throw new RuntimeException('Choose a valid booking and payment amount.');
        }
        $this->db->table('payments')->insert(['booking_id' => $bookingId, 'customer_id' => (int) $booking['customer_id'], 'amount' => $amount, 'reference' => $reference ?: null, 'status' => 'recorded', 'paid_at' => date('Y-m-d H:i:s')]);
        $this->notify('customer', (int) $booking['customer_id'], 'Payment recorded', 'A payment of ₱' . number_format($amount, 2) . ' has been recorded for request #' . $bookingId . '.');
    }

    public function markNotificationsRead(array $user): void
    {
        $this->db->table('notifications')->where('recipient_role', (string) $user['role'])
            ->groupStart()->where('recipient_id', (int) $user['id'])->orWhere('recipient_id', null)->groupEnd()
            ->update(['is_read' => 1]);
    }

    private function users(bool $includeCustomers = true): array
    {
        $customers = $includeCustomers ? $this->db->table('customers')->select("id, 'customer' AS account_type, first_name, middle_name, last_name, birthdate, gender, address, email, phone_number, username, '' AS department, 'customer' AS role, is_active")->get()->getResultArray() : [];
        $employees = $this->db->table('employees')->select("id, 'employee' AS account_type, first_name, middle_name, last_name, birthdate, gender, address, email, phone_number, username, department, role, is_active")->get()->getResultArray();
        if (! $includeCustomers) {
            $employees = array_values(array_filter($employees, static fn ($employee) => $employee['role'] !== 'admin'));
        }
        return array_merge($customers, $employees);
    }

    private function employees(): array
    {
        return $this->db->table('employees')->select('id, first_name, last_name, department, role')->where('is_active', 1)->orderBy('first_name')->get()->getResultArray();
    }

    private function bookingOverview(): array
    {
        return $this->db->table('service_bookings b')
            ->select('b.*, c.first_name, c.last_name, c.phone_number, s.name AS service_name, e.first_name AS tech_first_name, e.last_name AS tech_last_name')
            ->join('customers c', 'c.id = b.customer_id')->join('service_categories s', 's.id = b.service_category_id')
            ->join('employees e', 'e.id = b.assigned_employee_id', 'left')->orderBy('b.preferred_date', 'DESC')->get()->getResultArray();
    }

    private function assignedJobs(int $employeeId): array
    {
        return $this->db->table('service_bookings b')
            ->select('b.id, b.preferred_date, b.service_address, b.notes, b.status, s.name AS service_name, c.first_name, c.last_name')
            ->join('customers c', 'c.id = b.customer_id')->join('service_categories s', 's.id = b.service_category_id')
            ->where('b.assigned_employee_id', $employeeId)->orderBy('b.preferred_date')->get()->getResultArray();
    }

    private function notifications(array $user, array $preferences): array
    {
        $items = $this->db->table('notifications')->groupStart()
            ->where('recipient_role', (string) $user['role'])->groupStart()->where('recipient_id', (int) $user['id'])->orWhere('recipient_id', null)->groupEnd()
            ->orGroupStart()->where('recipient_role', 'employee')->where('recipient_id', null)->groupEnd()
            ->groupEnd()->orderBy('created_at', 'DESC')->limit(20)->get()->getResultArray();
        return array_values(array_filter($items, static function ($item) use ($preferences): bool {
            if ($item['category'] === 'promotion' && ! $preferences['marketing_updates']) return false;
            if (in_array($item['category'], ['service', 'update'], true) && ! $preferences['service_updates']) return false;
            return true;
        }));
    }

    private function announcements(string $role, array $preferences): array
    {
        $items = $this->db->table('announcements')->where('is_active', 1)->groupStart()->where('target_role', 'all')->orWhere('target_role', $role)->orWhere('target_role', 'employee')->groupEnd()->orderBy('created_at', 'DESC')->limit(10)->get()->getResultArray();
        return array_values(array_filter($items, static fn ($item): bool => $item['category'] !== 'promotion' || $preferences['marketing_updates']));
    }

    private function reports(): array
    {
        $statuses = $this->db->table('service_bookings')->select('status, COUNT(*) AS total')->groupBy('status')->get()->getResultArray();
        $serviceUsage = $this->db->table('service_bookings b')->select('s.name, COUNT(b.id) AS total')
            ->join('service_categories s', 's.id = b.service_category_id')->groupBy('s.id, s.name')->orderBy('total', 'DESC')->get()->getResultArray();
        return [
            'recentBookings' => $this->db->table('service_bookings')->countAllResults(),
            'activeCustomers' => $this->db->table('customers')->where('is_active', 1)->countAllResults(),
            'openTickets' => $this->db->table('support_tickets')->where('status', 'open')->countAllResults(),
            'fieldReports' => $this->db->table('work_reports')->countAllResults(),
            'revenue' => (float) ($this->db->table('payments')->selectSum('amount')->get()->getRowArray()['amount'] ?? 0),
            'bookingStatuses' => $statuses,
            'serviceUsage' => $serviceUsage,
        ];
    }

    private function stats(): array
    {
        return [
            'pending' => $this->db->table('service_bookings')->where('status', 'pending')->countAllResults(),
            'scheduled' => $this->db->table('service_bookings')->whereIn('status', ['approved', 'scheduled', 'in_progress'])->countAllResults(),
            'customers' => $this->db->table('customers')->where('is_active', 1)->countAllResults(),
            'revenue' => (float) ($this->db->table('payments')->selectSum('amount')->get()->getRowArray()['amount'] ?? 0),
        ];
    }

    private function notify(string $role, ?int $id, string $title, string $body, string $category = 'service'): void
    {
        $this->db->table('notifications')->insert(['recipient_role' => $role, 'recipient_id' => $id, 'title' => $title, 'body' => $body, 'category' => $category, 'is_read' => 0, 'created_at' => date('Y-m-d H:i:s')]);
    }

    private function preferences(array $user): array
    {
        $row = $this->db->table('account_notification_preferences')->where('account_role', (string) $user['role'])->where('account_id', (int) $user['id'])->get(1)->getRowArray();
        return ['service_updates' => (bool) ($row['service_updates'] ?? true), 'marketing_updates' => (bool) ($row['marketing_updates'] ?? true)];
    }

    private function savePreferences(array $user, bool $serviceUpdates, bool $marketingUpdates): void
    {
        $key = ['account_role' => (string) $user['role'], 'account_id' => (int) $user['id']];
        $values = $key + ['service_updates' => $serviceUpdates ? 1 : 0, 'marketing_updates' => $marketingUpdates ? 1 : 0, 'updated_at' => date('Y-m-d H:i:s')];
        $existing = $this->db->table('account_notification_preferences')->where($key)->get(1)->getRowArray();
        if ($existing === null) $this->db->table('account_notification_preferences')->insert($values);
        else $this->db->table('account_notification_preferences')->where('id', $existing['id'])->update($values);
    }

    private function ticketThreads(array $tickets): array
    {
        if ($tickets === []) return [];
        $threads = [];
        foreach ($this->db->table('support_ticket_messages')->whereIn('ticket_id', array_column($tickets, 'id'))->orderBy('created_at')->get()->getResultArray() as $message) {
            $threads[(int) $message['ticket_id']][] = $message;
        }
        return $threads;
    }

    private function syncSessionName(array $user, string $first, string $last): void
    {
        $sessionUser = session()->get('authUser') ?? $user;
        $sessionUser['firstName'] = $first;
        $sessionUser['lastName'] = $last;
        session()->set('authUser', $sessionUser);
    }
}
