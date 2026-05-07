<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Nom comercial al correu (doble "p"). Es fix aquí perquè config('app.name') o APP_NAME
     * poden quedar en caché com "Loopy" dins Docker fins fer config:clear.
     */
    private const MARCA_CORREU = 'Looppy';

    public function __construct(
        public User $usuari
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                (string) config('mail.from.address', 'hello@example.com'),
                self::MARCA_CORREU
            ),
            subject: 'Benvingut/da a '.self::MARCA_CORREU,
        );
    }

    public function content(): Content
    {
        $appPublicUrl = $this->resolveFrontendBaseUrl();

        return new Content(
            view: 'emails.welcome-user',
            with: [
                'usuari' => $this->usuari,
                'nomApp' => self::MARCA_CORREU,
                'appPublicUrl' => $appPublicUrl,
            ],
        );
    }

    /**
     * URL base del frontend (Nuxt) per al botó del correu.
     */
    private function resolveFrontendBaseUrl(): string
    {
        $explicit = env('FRONTEND_URL');
        if (is_string($explicit) && $explicit !== '') {
            return rtrim($explicit, '/');
        }

        $googleRedirect = (string) env('GOOGLE_FRONTEND_REDIRECT', '');
        if ($googleRedirect !== '') {
            $stripped = preg_replace('#/auth/google/redirect/?$#i', '', $googleRedirect);

            return rtrim((string) $stripped, '/');
        }

        return rtrim((string) config('app.url', 'http://localhost:3000'), '/');
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
