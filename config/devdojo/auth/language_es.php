<?php

/*
 * Yammbo Tv: textos en español de las pantallas de devdojo/auth.
 * DetectLocale los mezcla sobre devdojo.auth.language cuando el idioma es "es";
 * language.php se queda en inglés como base.
 */
return [
    'login' => [
        'page_title' => 'Iniciar sesión',
        'headline' => 'Inicia sesión',
        'subheadline' => 'Entra con tu cuenta de Yammbo Tv',
        'email_address' => 'Correo electrónico',
        'password' => 'Contraseña',
        'remember_me' => 'Recordarme',
        'edit' => 'Cambiar',
        'button' => 'Continuar',
        'forget_password' => '¿Olvidaste tu contraseña?',
        'dont_have_an_account' => '¿No tienes cuenta?',
        'sign_up' => 'Regístrate',
        'social_auth_authenticated_message' => 'Te registraste con __social_providers_list__. Inicia sesión con esa red abajo.',
        'change_email' => 'Cambiar correo',
        'couldnt_find_your_account' => 'No encontramos tu cuenta',
    ],
    'register' => [
        'page_title' => 'Crear cuenta',
        'headline' => 'Crea tu cuenta',
        'subheadline' => 'Regístrate gratis en Yammbo Tv',
        'name' => 'Nombre',
        'email_address' => 'Correo electrónico',
        'password' => 'Contraseña',
        'password_confirmation' => 'Confirmar contraseña',
        'already_have_an_account' => '¿Ya tienes cuenta?',
        'sign_in' => 'Inicia sesión',
        'button' => 'Continuar',
        'email_registration_disabled' => 'El registro con correo está desactivado. Usa el acceso con redes sociales.',
    ],
    'verify' => [
        'page_title' => 'Verifica tu cuenta',
        'headline' => 'Verifica tu correo',
        'subheadline' => 'Antes de continuar tienes que verificar tu correo.',
        'description' => 'Antes de continuar, revisa tu correo y abre el enlace de verificación. Si no te ha llegado,',
        'new_request_link' => 'pulsa aquí para pedir otro',
        'new_link_sent' => 'Te hemos enviado un enlace nuevo a tu correo.',
        'or' => 'O',
        'logout' => 'pulsa aquí para cerrar sesión',
    ],
    'passwordConfirm' => [
        'page_title' => 'Confirma tu contraseña',
        'headline' => 'Confirmar contraseña',
        'subheadline' => 'Confirma tu contraseña para continuar',
        'password' => 'Contraseña',
        'button' => 'Confirmar contraseña',
    ],
    'passwordResetRequest' => [
        'page_title' => 'Recuperar contraseña',
        'headline' => 'Recuperar contraseña',
        'subheadline' => 'Escribe tu correo y te enviaremos un enlace',
        'email' => 'Correo electrónico',
        'button' => 'Enviar enlace',
        'or' => 'o',
        'return_to_login' => 'volver a iniciar sesión',
    ],
    'passwordReset' => [
        'page_title' => 'Nueva contraseña',
        'headline' => 'Nueva contraseña',
        'subheadline' => 'Elige tu nueva contraseña',
        'email' => 'Correo electrónico',
        'password' => 'Contraseña',
        'password_confirm' => 'Confirmar contraseña',
        'button' => 'Guardar contraseña',
    ],
    'twoFactorChallenge' => [
        'page_title' => 'Verificación en dos pasos',
        'headline_auth' => 'Código de verificación',
        'subheadline_auth' => 'Escribe el código de tu app de autenticación.',
        'headline_recovery' => 'Código de recuperación',
        'subheadline_recovery' => 'Confirma el acceso con uno de tus códigos de recuperación.',
    ],
];
