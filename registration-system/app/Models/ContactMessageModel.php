<?php

namespace App\Models;

use RuntimeException;

class ContactMessageModel
{
    public function save(array $message): void
    {
        $saved = db_connect()->table('contact_messages')->insert([
            'name'        => $message['name'],
            'email'       => $message['email'],
            'message'     => $message['message'],
            'created_at'  => date('Y-m-d H:i:s'),
        ]);

        if ($saved === false) {
            throw new RuntimeException('The contact message could not be saved.');
        }
    }
}
