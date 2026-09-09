<?php
    use function Laravel\Folio\name;
    name('privacy');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Política de Privacidad — Yammbo Tv',
        'description' => 'Qué datos trata Yammbo Tv, para qué y con quién se comparten.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-16 sm:py-24">
    <div class="prose-content">
        <h1>Política de Privacidad</h1>
        <p class="text-[color:var(--color-ink-dim)]"><em>Última actualización: 26 de agosto de 2026</em></p>

        <p>
            Esta política explica qué datos tratamos cuando usas Yammbo Tv, con qué finalidad y con quién se
            comparten. Intentamos recoger lo mínimo para que el servicio funcione.
        </p>

        <h2>1. Quién es responsable</h2>
        <p>
            Yammbo es responsable del tratamiento de los datos descritos aquí. Puedes contactarnos en
            <a href="mailto:support@yammbo.com">support@yammbo.com</a>.
        </p>

        <h2>2. Qué datos tratamos</h2>
        <ul>
            <li><strong>Cuenta:</strong> tu dirección de correo y una contraseña, que guardamos siempre cifrada (nunca en texto legible). Opcionalmente, el nombre que indiques.</li>
            <li><strong>Suscripción:</strong> el plan contratado, su estado y sus fechas. Los pagos los procesa Stripe: <strong>los datos de tu tarjeta no pasan por nuestros servidores ni los almacenamos</strong>.</li>
            <li><strong>Dispositivos:</strong> un identificador del dispositivo y su tipo (móvil o televisor), para aplicar el límite de dispositivos simultáneos de tu plan y para que puedas cerrar sesión a distancia.</li>
            <li><strong>Datos técnicos:</strong> dirección IP, tipo de dispositivo, sistema operativo y versión de la aplicación, junto con registros del servidor. Se usan para seguridad, diagnóstico y prevención de abusos.</li>
            <li><strong>Soporte:</strong> lo que nos escribas cuando contactes con nosotros.</li>
        </ul>

        <h2>3. Qué NO hacemos</h2>
        <ul>
            <li><strong>No alojamos ni almacenamos contenido</strong>: ni vídeos, ni películas, ni series, ni canales, ni portadas. Todo eso lo sirven terceros directamente a tu dispositivo.</li>
            <li><strong>No vendemos tus datos</strong> ni los cedemos con fines publicitarios.</li>
            <li><strong>No mostramos publicidad</strong> dentro del servicio.</li>
            <li>No guardamos los datos de tu tarjeta.</li>
        </ul>

        <h2>4. Para qué usamos los datos</h2>
        <ul>
            <li>Crear y mantener tu cuenta y darte acceso al servicio.</li>
            <li>Gestionar la suscripción, los cobros y las renovaciones.</li>
            <li>Aplicar los límites de dispositivos de tu plan.</li>
            <li>Mantener la seguridad, prevenir fraude y abusos, y diagnosticar fallos.</li>
            <li>Enviarte comunicaciones necesarias (verificación de correo, restablecimiento de contraseña, avisos del servicio).</li>
            <li>Entender de forma agregada cómo se usa el servicio para mejorarlo.</li>
        </ul>

        <h2>5. Terceros con los que se comparten datos</h2>
        <p>Solo trabajamos con proveedores necesarios para prestar el servicio:</p>
        <ul>
            <li><strong>Stripe</strong> — procesamiento de pagos y gestión de suscripciones.</li>
            <li><strong>Google Analytics</strong> — estadísticas de uso del sitio web, de forma agregada.</li>
            <li><strong>Firebase (Google)</strong> — envío de notificaciones a las aplicaciones.</li>
            <li><strong>Cloudflare</strong> — entrega del sitio, protección y seguridad.</li>
            <li><strong>Proveedor de correo</strong> — envío de correos transaccionales.</li>
        </ul>
        <p>
            Aparte de estos, cuando reproduces algo tu dispositivo se conecta <strong>directamente</strong> a las
            fuentes de terceros que sirven el material. Esas conexiones no pasan por nosotros, y esos terceros
            pueden ver tu dirección IP y aplicar sus propias políticas, que no controlamos.
        </p>

        <h2>6. Cookies y almacenamiento local</h2>
        <p>
            Usamos cookies y almacenamiento local para mantener tu sesión iniciada, recordar preferencias como
            el idioma y obtener estadísticas de uso. Puedes borrarlas desde tu navegador, aunque si eliminas
            las de sesión tendrás que volver a iniciarla.
        </p>

        <h2>7. Cuánto tiempo conservamos los datos</h2>
        <p>
            Mantenemos los datos de tu cuenta mientras esta exista. Si la eliminas, borramos o anonimizamos tus
            datos personales, salvo lo que debamos conservar por obligaciones legales o contables (por ejemplo,
            los registros de facturación). Los registros técnicos se conservan durante un periodo limitado.
        </p>

        <h2>8. Tus derechos</h2>
        <p>
            Puedes solicitar acceso a tus datos, su corrección, su eliminación, una copia portable o la
            limitación de su tratamiento, así como retirar tu consentimiento cuando el tratamiento se base en él.
            Escríbenos a <a href="mailto:support@yammbo.com">support@yammbo.com</a> y atenderemos tu solicitud.
            También puedes reclamar ante la autoridad de protección de datos de tu país.
        </p>

        <h2>9. Seguridad</h2>
        <p>
            Ciframos el tráfico con HTTPS, guardamos las contraseñas con funciones de hash y limitamos el acceso
            interno a los datos. Ningún sistema es infalible, pero trabajamos para reducir los riesgos y, si
            ocurriera una brecha que te afecte, te informaríamos.
        </p>

        <h2>10. Menores</h2>
        <p>
            El servicio no está dirigido a menores de la edad mínima legal de su país. Si detectamos una cuenta
            de un menor sin autorización de su tutor, la eliminaremos.
        </p>

        <h2>11. Cambios en esta política</h2>
        <p>
            Si actualizamos esta política, publicaremos la nueva versión con su fecha y, cuando el cambio sea
            relevante, te avisaremos.
        </p>

        <h2>12. Contacto</h2>
        <p>
            Para cualquier cuestión de privacidad: <a href="mailto:support@yammbo.com">support@yammbo.com</a>.
        </p>
    </div>
    </x-container>
</x-layouts.marketing>
