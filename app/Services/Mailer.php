<?php

declare(strict_types=1);

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use Throwable;

/** Pengiriman email lewat SMTP (PHPMailer). Tidak pernah melempar error ke pemanggil. */
final class Mailer
{
    private const MAX_RECIPIENTS = 5;

    public static function enabled(): bool
    {
        return class_exists(PHPMailer::class)
            && trim((string) config('mail.host', '')) !== ''
            && filter_var(self::fromAddress(), FILTER_VALIDATE_EMAIL) !== false;
    }

    /** @return string[] alamat penerima yang valid */
    public static function recipients(): array
    {
        $raw = trim((string) config('mail.to', ''));
        if ($raw === '') {
            $raw = trim((string) setting('email', ''));
        }

        $out = [];
        foreach (explode(',', $raw) as $part) {
            $email = strtolower(trim($part));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) !== false && !in_array($email, $out, true)) {
                $out[] = $email;
            }
        }

        return array_slice($out, 0, self::MAX_RECIPIENTS);
    }

    /**
     * Kirim email teks biasa.
     *
     * @param string[]                         $to
     * @param array{email: string, name: string}|null $replyTo
     * @return array{0: bool, 1: ?string} [berhasil, pesan error]
     */
    public static function send(array $to, string $subject, string $body, ?array $replyTo = null): array
    {
        if (!class_exists(PHPMailer::class)) {
            return [false, "PHPMailer belum terpasang. Jalankan 'composer require phpmailer/phpmailer'."];
        }
        if (!self::enabled()) {
            return [false, 'Email belum dikonfigurasi (MAIL_HOST atau MAIL_FROM_ADDRESS kosong).'];
        }
        if ($to === []) {
            return [false, 'Penerima kosong. Isi MAIL_TO atau email di Pengaturan website.'];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = (string) config('mail.host');
            $mail->Port = (int) config('mail.port', 587);
            $mail->Timeout = 8;
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(false);

            $username = (string) config('mail.username', '');
            $mail->SMTPAuth = $username !== '';
            if ($mail->SMTPAuth) {
                $mail->Username = $username;
                $mail->Password = (string) config('mail.password', '');
            }

            switch ((string) config('mail.encryption', 'tls')) {
                case 'ssl':
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    break;
                case 'none':
                    $mail->SMTPSecure = '';
                    $mail->SMTPAutoTLS = false;
                    break;
                default:
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            }

            $mail->setFrom(self::fromAddress(), (string) config('mail.from_name', ''));
            foreach ($to as $address) {
                $mail->addAddress($address);
            }
            if ($replyTo !== null && filter_var($replyTo['email'], FILTER_VALIDATE_EMAIL) !== false) {
                $mail->addReplyTo($replyTo['email'], $replyTo['name']);
            }

            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();

            return [true, null];
        } catch (Throwable $e) {
            return [false, $e->getMessage()];
        }
    }

    private static function fromAddress(): string
    {
        return trim((string) config('mail.from_address', ''));
    }
}