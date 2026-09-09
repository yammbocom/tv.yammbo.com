<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Correos de Yambo TV con una plantilla comun (resources/views/emails/layout).
 *
 * Todos comparten cabecera, tipografia, boton y pie; cada tipo solo aporta
 * titulo, texto, accion y (opcional) una tabla de datos.
 *
 * Uso:
 *   YamboMail::verifyEmail($user, $url);
 *   YamboMail::resetPassword($user, $url);
 *   YamboMail::paymentFailed($user, $url, ['Plan' => 'Premium', ...]);
 *   YamboMail::upcomingInvoice($user, $url, [...]);
 *   YamboMail::welcome($user);
 *   YamboMail::receipt($user, [...]);
 */
class YamboMail
{
    private const RED    = '#e50914';
    private const AMBER  = '#f59e0b';
    private const GREEN  = '#22c55e';

    /** Envia usando la plantilla base. Devuelve true si no lanzo excepcion. */
    public static function send(string $to, string $subject, array $data): bool
    {
        try {
            $html = view('emails.layout', $data + ['subject' => $subject])->render();
            Mail::html($html, function ($m) use ($to, $subject) {
                $m->to($to)->subject($subject);
            });
            return true;
        } catch (\Throwable $e) {
            Log::warning('YamboMail fallo (' . $subject . '): ' . $e->getMessage());
            return false;
        }
    }

    private static function hello(?User $user): string
    {
        $name = $user ? trim(explode(' ', trim((string) $user->name))[0]) : '';
        return $name !== '' ? ('Hola ' . e($name) . ',') : 'Hola,';
    }

    /**
     * Enlace YA AUTENTICADO (magic-link). Sin esto, el boton del correo acaba en
     * la pantalla de login o en /pricing en vez de donde dice el correo.
     */
    private static function signed(User $user, string $path, int $days = 14): string
    {
        try {
            \Tymon\JWTAuth\Facades\JWTAuth::factory()->setTTL($days * 24 * 60);
            $t = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);
            return url($path) . (strpos($path, '?') === false ? '?' : '&') . 't=' . urlencode($t);
        } catch (\Throwable $e) {
            return url($path);
        }
    }

    /** Su cuenta / suscripcion, ya dentro. */
    private static function accountUrl(User $user): string
    {
        return self::signed($user, '/mi-suscripcion');
    }

    /** Pagina de planes para suscribirse o renovar, ya identificado. */
    private static function plansUrl(User $user): string
    {
        return self::signed($user, '/precios-tv');
    }

    // ---------------------------------------------------------------- cuentas

    public static function verifyEmail(User $user, string $url): bool
    {
        return self::send($user->email, 'Confirma tu correo - Yambo TV', [
            'accent'     => self::RED,
            'title'      => 'Confirma tu correo',
            'body'       => '<p style="margin:0 0 10px 0;">' . self::hello($user) . '</p>'
                          . '<p style="margin:0;">Solo falta un paso para empezar a ver Yambo TV. '
                          . 'Confirma que este correo es tuyo y podras iniciar sesion en tu televisor.</p>',
            'actionText' => 'Confirmar mi correo',
            'actionUrl'  => $url,
            'note'       => 'El enlace caduca en 48 horas. Si no creaste esta cuenta, ignora este mensaje.',
        ]);
    }

    public static function welcome(User $user, ?string $trialEnds = null): bool
    {
        $rows = [];
        if ($trialEnds) {
            $rows['Prueba gratis hasta'] = $trialEnds;
        }
        return self::send($user->email, 'Bienvenido a Yambo TV', [
            'accent'     => self::RED,
            'title'      => 'Bienvenido a Yambo TV',
            'body'       => '<p style="margin:0 0 10px 0;">' . self::hello($user) . '</p>'
                          . '<p style="margin:0;">Tu cuenta ya esta lista. Abre la app en tu televisor y '
                          . 'vincula la sesion escaneando el codigo QR: no hace falta escribir contrasenas con el mando.</p>',
            'actionText' => 'Ver mi cuenta',
            'actionUrl'  => self::accountUrl($user),
            'rows'       => $rows,
        ]);
    }

    public static function resetPassword(User $user, string $url): bool
    {
        return self::send($user->email, 'Restablece tu contrasena - Yambo TV', [
            'accent'     => self::RED,
            'title'      => 'Restablece tu contrasena',
            'body'       => '<p style="margin:0 0 10px 0;">' . self::hello($user) . '</p>'
                          . '<p style="margin:0;">Recibimos una solicitud para cambiar la contrasena de tu cuenta. '
                          . 'Pulsa el boton para elegir una nueva.</p>',
            'actionText' => 'Cambiar contrasena',
            'actionUrl'  => $url,
            'note'       => 'Si no fuiste tu, puedes ignorar este correo: tu contrasena actual seguira funcionando.',
        ]);
    }

    // ------------------------------------------------------------------ pagos

    public static function paymentFailed(User $user, array $rows = []): bool
    {
        return self::send($user->email, 'No pudimos cobrar tu suscripcion - Yambo TV', [
            'accent'     => self::AMBER,
            'title'      => 'Hubo un problema con tu pago',
            'body'       => '<p style="margin:0 0 10px 0;">' . self::hello($user) . '</p>'
                          . '<p style="margin:0;">No pudimos cobrar la renovacion de tu suscripcion. '
                          . 'Suele ser por una tarjeta caducada o sin fondos. Actualiza tu metodo de pago '
                          . 'para no perder el acceso.</p>',
            'actionText' => 'Actualizar metodo de pago',
            'actionUrl'  => self::accountUrl($user),
            'rows'       => $rows,
            'note'       => 'Volveremos a intentarlo automaticamente. Si el pago no entra, la cuenta pasara a inactiva.',
        ]);
    }

    public static function subscriptionExpired(User $user, array $rows = []): bool
    {
        return self::send($user->email, 'Tu suscripcion ha vencido - Yambo TV', [
            'accent'     => self::RED,
            'title'      => 'Tu suscripcion ha vencido',
            'body'       => '<p style="margin:0 0 10px 0;">' . self::hello($user) . '</p>'
                          . '<p style="margin:0;">Tu acceso a Yambo TV esta pausado. Renueva cuando quieras '
                          . 'y sigue viendo peliculas, series, anime y TV en vivo desde donde lo dejaste.</p>',
            'actionText' => 'Renovar suscripcion',
            'actionUrl'  => self::plansUrl($user),
            'rows'       => $rows,
        ]);
    }

    public static function upcomingInvoice(User $user, array $rows = []): bool
    {
        return self::send($user->email, 'Tu proxima factura de Yambo TV', [
            'accent'     => self::RED,
            'title'      => 'Tu proxima renovacion',
            'body'       => '<p style="margin:0 0 10px 0;">' . self::hello($user) . '</p>'
                          . '<p style="margin:0;">Te avisamos de la proxima renovacion de tu suscripcion. '
                          . 'No tienes que hacer nada: el cobro es automatico.</p>',
            'actionText' => 'Ver o cambiar mi plan',
            'actionUrl'  => self::accountUrl($user),
            'rows'       => $rows,
            'note'       => 'Puedes cancelar cuando quieras; mantendras el acceso hasta el final del periodo pagado.',
        ]);
    }

    public static function receipt(User $user, array $rows = []): bool
    {
        return self::send($user->email, 'Pago recibido - Yambo TV', [
            'accent'     => self::GREEN,
            'title'      => 'Pago recibido',
            'body'       => '<p style="margin:0 0 10px 0;">' . self::hello($user) . '</p>'
                          . '<p style="margin:0;">Gracias. Confirmamos tu pago y tu suscripcion sigue activa.</p>',
            'actionText' => 'Ver mi suscripcion',
            'actionUrl'  => self::accountUrl($user),
            'rows'       => $rows,
        ]);
    }
}
