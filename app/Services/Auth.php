<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Session;

final class Auth
{
    private const IDLE_TIMEOUT = 1800;       // 30 menit tanpa aktivitas
    private const ABSOLUTE_TIMEOUT = 43200;  // 12 jam sejak login
    private const WINDOW = 900;              // jendela hitung gagal login: 15 menit
    private const MAX_PER_ACCOUNT = 5;       // gagal per kombinasi IP + email
    private const MAX_PER_IP = 20;           // gagal per IP

    private static ?array $user = null;
    private static bool $loaded = false;

    /** User yang sedang login (dibaca ulang dari DB, hanya yang aktif). */
    public static function user(): ?array
    {
        if (self::$loaded) {
            return self::$user;
        }

        self::$loaded = true;

        $id = Session::get('user_id');
        if (!is_int($id)) {
            return self::$user = null;
        }

        $stmt = Database::connection()->prepare(
            'SELECT u.id, u.name, u.email, r.name AS role, r.label AS role_label
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? AND u.is_active = 1 LIMIT 1'
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return self::$user = null;
        }

        $row['id'] = (int) $row['id'];

        return self::$user = $row;
    }

    /** Perbarui waktu aktivitas; false jika session sudah melewati batas waktu. */
    public static function touch(): bool
    {
        $now = time();
        $last = (int) Session::get('last_activity', 0);
        $start = (int) Session::get('login_at', 0);

        if ($now - $last > self::IDLE_TIMEOUT || $now - $start > self::ABSOLUTE_TIMEOUT) {
            return false;
        }

        Session::put('last_activity', $now);

        return true;
    }

    public static function can(string $permission): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        $granted = (array) config('permissions.' . $user['role'], []);

        return in_array('*', $granted, true) || in_array($permission, $granted, true);
    }

    public static function isThrottled(string $email, string $ip): bool
    {
        $pdo = Database::connection();
        $since = date('Y-m-d H:i:s', time() - self::WINDOW);

        $account = $pdo->prepare(
            'SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND email = ? AND attempted_at > ?'
        );
        $account->execute([$ip, self::normalize($email), $since]);
        if ((int) $account->fetchColumn() >= self::MAX_PER_ACCOUNT) {
            return true;
        }

        $perIp = $pdo->prepare('SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > ?');
        $perIp->execute([$ip, $since]);

        return (int) $perIp->fetchColumn() >= self::MAX_PER_IP;
    }

    /** Verifikasi kredensial. Mengembalikan data user (tanpa hash) atau null. */
    public static function attempt(string $email, string $password, string $ip): ?array
    {
        $pdo = Database::connection();

        $stmt = $pdo->prepare(
            'SELECT u.id, u.name, u.email, u.password_hash, u.is_active, r.name AS role
             FROM users u JOIN roles r ON r.id = u.role_id
             WHERE u.email = ? LIMIT 1'
        );
        $stmt->execute([self::normalize($email)]);
        $row = $stmt->fetch();

        if ($row === false) {
            // Samakan waktu proses dengan email yang ada (anti enumerasi akun)
            password_hash($password, PASSWORD_DEFAULT);
            self::recordFailure($email, $ip);

            return null;
        }

        $valid = password_verify($password, (string) $row['password_hash']);

        if (!$valid || (int) $row['is_active'] !== 1) {
            self::recordFailure($email, $ip);

            return null;
        }

        if (password_needs_rehash((string) $row['password_hash'], PASSWORD_DEFAULT)) {
            $update = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $row['id']]);
        }

        $clear = $pdo->prepare('DELETE FROM login_attempts WHERE ip = ? AND email = ?');
        $clear->execute([$ip, self::normalize($email)]);

        unset($row['password_hash']);
        $row['id'] = (int) $row['id'];

        return $row;
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::forget('_csrf');
        Session::put('user_id', (int) $user['id']);
        Session::put('login_at', time());
        Session::put('last_activity', time());

        $update = Database::connection()->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?');
        $update->execute([(int) $user['id']]);

        self::reset();
    }

    public static function logout(): void
    {
        Session::destroy();
        self::reset();
    }

    private static function reset(): void
    {
        self::$user = null;
        self::$loaded = false;
    }

    private static function recordFailure(string $email, string $ip): void
    {
        $pdo = Database::connection();

        $insert = $pdo->prepare('INSERT INTO login_attempts (ip, email) VALUES (?, ?)');
        $insert->execute([$ip, self::normalize($email)]);

        // Bersihkan catatan lama sesekali
        if (random_int(1, 50) === 1) {
            $pdo->prepare('DELETE FROM login_attempts WHERE attempted_at < ?')
                ->execute([date('Y-m-d H:i:s', time() - 86400)]);
        }
    }

    private static function normalize(string $email): string
    {
        return substr(strtolower(trim($email)), 0, 190);
    }
}