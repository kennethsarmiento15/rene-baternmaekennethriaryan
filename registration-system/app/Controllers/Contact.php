<?php

namespace App\Controllers;

use App\Models\ContactMessageModel;
use Throwable;

class Contact extends BaseController
{
    public function send()
    {
        $data = [
            'name'    => trim((string) $this->request->getPost('name')),
            'email'   => trim((string) $this->request->getPost('email')),
            'message' => trim((string) $this->request->getPost('message')),
        ];
        $errors = [];

        if ($data['name'] === '' || mb_strlen($data['name']) > 150) {
            $errors['name'] = 'Enter your name (150 characters or fewer).';
        }
        if (! filter_var($data['email'], FILTER_VALIDATE_EMAIL) || mb_strlen($data['email']) > 150) {
            $errors['email'] = 'Enter a valid email address.';
        }
        if ($data['message'] === '' || mb_strlen($data['message']) > 5000) {
            $errors['message'] = 'Write a message (up to 5,000 characters).';
        }

        if ($errors !== []) {
            return redirect()->to(site_url('/#contact'))
                ->with('contactNotice', 'Please check the highlighted fields.')
                ->with('contactErrors', $errors)
                ->with('contactData', $data)
                ->with('openPanel', 'contact');
        }

        try {
            (new ContactMessageModel())->save($data);
        } catch (Throwable $exception) {
            log_message('error', 'Contact message save failed: {message}', ['message' => $exception->getMessage()]);
            return redirect()->to(site_url('/#contact'))
                ->with('contactNotice', 'We could not send your message. Please try again.')
                ->with('contactFailed', true)
                ->with('contactData', $data)
                ->with('openPanel', 'contact');
        }

        return redirect()->to(site_url('/#contact'))
            ->with('contactNotice', 'Your message has been sent. Thank you for contacting Sun Son Solar!')
            ->with('openPanel', 'contact');
    }
}
