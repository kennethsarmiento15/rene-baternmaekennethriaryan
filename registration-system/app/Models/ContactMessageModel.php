<?php

namespace App\Models;

class ContactMessageModel
{
    public function save(array $message): void
    {
        db_connect()->table('contact_messages')->insert([
            'name'        => $message['name'],
            'email'       => $message['email'],
            'message'     => $message['message'],
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
