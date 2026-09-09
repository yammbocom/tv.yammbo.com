<?php
    use function Laravel\Folio\name;
    name('help');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Centro de ayuda — Yammbo Tv',
        'description' => 'Cómo instalar Yammbo Tv, gestionar tu suscripción y resolver los problemas más comunes.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-16 sm:py-24">
    <div class="prose-content">
        <h1>Centro de ayuda</h1>
        <p class="text-[color:var(--color-ink-dim)]"><em>Última actualización: 26 de agosto de 2026</em></p>

        <p>
            Aquí tienes lo esencial para empezar y las soluciones a lo que más nos preguntan. Si no encuentras
            lo que buscas, escríbenos a <a href="mailto:support@yammbo.com">support@yammbo.com</a>.
        </p>

        <h2>Empezar</h2>

        <h3>¿Cómo instalo Yammbo Tv?</h3>
        <p>
            Entra en <a href="/install">tv.yammbo.com/install</a>. Ahí encontrarás la aplicación para
            <strong>Android</strong> y las instrucciones para instalarla en tu <strong>televisor</strong>
            (Fire TV Stick, TV Box o Smart TV con Android), incluido el código para el instalador Downloader.
        </p>

        <h3>¿Necesito cuenta?</h3>
        <p>
            Sí. Crea una cuenta con tu correo desde la propia aplicación o en la web. Las cuentas nuevas incluyen
            <strong>7 días de prueba</strong>; después hace falta una suscripción activa.
        </p>

        <h3>¿En qué se diferencian los planes?</h3>
        <ul>
            <li><strong>Basic</strong> — 1 dispositivo a la vez. Películas y series, sin TV en vivo.</li>
            <li><strong>Standard</strong> — 2 dispositivos a la vez, con TV en vivo.</li>
            <li><strong>Premium</strong> — 3 dispositivos a la vez, con TV en vivo.</li>
        </ul>
        <p>Puedes verlos y cambiar de plan en <a href="/pricing">la página de planes</a>.</p>

        <h2>Cuenta y suscripción</h2>

        <h3>¿Cómo cancelo?</h3>
        <p>
            Desde tu cuenta, en la sección de suscripción. La cancelación es inmediata y conservas el acceso
            hasta el final del periodo que ya has pagado. No hay permanencia.
        </p>

        <h3>He olvidado mi contraseña</h3>
        <p>
            Usa la opción de recuperar contraseña en la pantalla de inicio de sesión. Te llegará un correo con
            un enlace válido durante 60 minutos; si expira, pide otro. Revisa la carpeta de spam.
        </p>

        <h3>¿Cuántos dispositivos puedo usar?</h3>
        <p>
            Depende del plan (1, 2 o 3 a la vez). El límite es de uso <em>simultáneo</em>: puedes tener la
            aplicación instalada en más aparatos, pero solo ese número puede estar activo al mismo tiempo. Si
            superas el límite, la sesión más antigua se cierra.
        </p>

        <h2>Problemas al reproducir</h2>

        <h3>La aplicación no carga el catálogo, o me dice que el contenido está limitado en mi zona</h3>
        <p>
            Algunos títulos y canales están restringidos por región: es el proveedor del contenido quien lo
            limita según el país desde el que te conectas, no tu conexión ni la aplicación.
        </p>
        <p>
            La solución es <strong>conectarte con una VPN a México</strong> y volver a abrir la aplicación. En
            cuanto el contenido cargue, <strong>puedes desconectar la VPN</strong>: seguirá funcionando con
            normalidad. Tienes una VPN recomendada y las instrucciones en
            <a href="/app-tv/vpn-info.html">esta página</a>.
        </p>

        <h3>Un título concreto no tiene fuentes disponibles</h3>
        <p>
            Las fuentes las sirven proveedores de terceros y cambian con el tiempo: a veces un título deja de
            estar disponible temporalmente. Prueba más tarde o con otro título. Que un título no aparezca no
            afecta a tu suscripción, que cubre el uso del software.
        </p>

        <h3>Se ve entrecortado o tarda en empezar</h3>
        <ul>
            <li>Comprueba tu velocidad de conexión; para vídeo en alta definición conviene una conexión estable.</li>
            <li>Si usas Wi-Fi, acércate al router o prueba con cable en el televisor.</li>
            <li>Cierra otras aplicaciones que consuman red en el mismo dispositivo.</li>
            <li>Prueba con otra fuente del mismo título si hay varias disponibles.</li>
        </ul>

        <h3>En la web no me reproduce, pero en la aplicación sí</h3>
        <p>
            Es lo esperado hoy: el navegador solo puede reproducir enlaces directos, mientras que las
            aplicaciones de móvil y televisor admiten muchas más fuentes. Para ver contenido, usa las
            aplicaciones; la web te sirve para explorar el catálogo y gestionar tu cuenta.
        </p>

        <h3>No veo los cambios recientes de la web</h3>
        <p>
            Tu navegador puede tener guardada una versión anterior. Bórrale los datos del sitio o ábrelo en una
            ventana privada y se actualizará.
        </p>

        <h2>Sobre el contenido</h2>

        <h3>¿Yammbo Tv aloja las películas y series?</h3>
        <p>
            No. Yammbo Tv es software: un reproductor y organizador que muestra lo que sirven proveedores de
            terceros. <strong>No alojamos, almacenamos ni distribuimos ningún vídeo, película, serie, canal o
            portada.</strong> Tu suscripción paga el uso de las aplicaciones y del sistema de cuentas, no el
            contenido. Puedes leerlo en detalle en los <a href="/terms">Términos de Servicio</a>.
        </p>

        <h3>Soy titular de derechos y quiero notificar algo</h3>
        <p>
            Escríbenos a <a href="mailto:support@yammbo.com">support@yammbo.com</a> indicando el material, la
            fuente concreta y tu acreditación. Aunque no alojamos contenido, cuando proceda dejaremos de mostrar
            la fuente señalada dentro de nuestro software.
        </p>

        <h2>¿Sigues con dudas?</h2>
        <p>
            Escríbenos a <a href="mailto:support@yammbo.com">support@yammbo.com</a>. Cuéntanos qué dispositivo
            usas, qué plan tienes y qué ves en pantalla; con eso te ayudamos mucho más rápido.
        </p>
    </div>
    </x-container>
</x-layouts.marketing>
