<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotifyUser extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $name;
    public $model;

    public $type;
    /**
     * Create a new message instance.
     */
    public function __construct($data, $name, $model, $type)
    {
        $this->data = $data;
        $this->name = $name;
        $this->model = $model;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address('hr@ycplwb.com', 'Yingjia Communication Pvt Ltd'),
            replyTo: [
                new Address('hr@ycplwb.com', 'Yingjia Communication Pvt Ltd'),
            ],
            subject: 'New Lead Generated.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'notifyUser',
            with: [
                'data' => $this->data,
                'name' => $this->name,
                'model' => $this->model,
                'type' => $this->type
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
