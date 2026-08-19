<?php
    use function Laravel\Folio\name;
    name('help');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Ayuda y Soporte — Yammbo Tv',
        'description' => 'Guía de inicio, preguntas frecuentes y soporte de Yammbo Tv.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-16 sm:py-24">
    <div class="prose-content">
        <h1>Ayuda y Soporte</h1>
        <p>¿Necesitas una mano? Aquí tienes lo esencial para empezar y resolver dudas frecuentes.</p>

        <h2>Primeros pasos</h2>
        <ul>
            <li><strong>En Android (móvil / TV):</strong> instala la app YamboTV desde la página de
                <a href="/install">instalación</a>. Incluye todo lo necesario, no requiere nada extra.</li>
            <li><strong>En el navegador (PC):</strong> para reproducir necesitas instalar una vez el pequeño
                <strong>Yammbo TV Service</strong> (gratuito) desde la página de <a href="/install">instalación</a>.
                Déjalo corriendo en segundo plano y recarga tv.yammbo.com.</li>
        </ul>

        <h2>Preguntas frecuentes</h2>

        <h3>En el navegador no reproduce nada / «Transmisión URL: error»</h3>
        <p>
            El reproductor web necesita el <strong>Yammbo TV Service</strong> instalado y en ejecución. Descárgalo desde
            <a href="/install">/install</a>, instálalo y recarga la página. En <em>Configuración → Transmisión</em> el
            estado debe aparecer <strong>en línea</strong> (en verde).
        </p>

        <h3>Windows me muestra «Windows protegió tu PC» al instalar</h3>
        <p>
            Es normal: el instalador no tiene una licencia de firma de pago. No es un virus. Pulsa
            <strong>«Más información» → «Ejecutar de todas formas»</strong>. Tienes la guía completa en
            <a href="/install">/install</a>.
        </p>

        <h3>En Android aparece «origen desconocido» o un aviso de Play Protect</h3>
        <p>
            Android pide permiso para instalar apps fuera de Play Store. Activa <strong>«Permitir de esta fuente»</strong> y,
            si aparece Play Protect, pulsa <strong>«Más detalles» → «Instalar de todas formas»</strong>.
        </p>

        <h3>Un título concreto no carga o se queda buscando</h3>
        <p>
            El contenido proviene de fuentes de terceros y su disponibilidad puede variar. Prueba con otra opción de
            reproducción o más tarde. Si tienes plan Premium y no aparecen fuentes, verifica que tu sesión esté activa.
        </p>

        <h3>¿Cómo gestiono o cancelo mi suscripción?</h3>
        <p>
            Desde tu cuenta puedes ver tu plan y gestionarlo. Consulta los planes en <a href="/pricing">/pricing</a>.
            Puedes cancelar cuando quieras y mantendrás el acceso hasta el final del periodo pagado.
        </p>

        <h3>¿En cuántos dispositivos puedo usarlo?</h3>
        <p>
            Tu cuenta funciona en tus dispositivos: Android, Android TV y navegador. Tu biblioteca y tu progreso se
            sincronizan entre ellos.
        </p>

        <h3>¿Qué es la VPN y la necesito?</h3>
        <p>
            La VPN Yammbo es <strong>opcional y solo para Android</strong>. Úsala si tu operador o país bloquea algún
            contenido. Puedes descargarla desde <a href="/install">/install</a>.
        </p>

        <h2>¿Sigues con problemas?</h2>
        <p>
            Escríbenos a <a href="mailto:soporte@yammbo.com">soporte@yammbo.com</a> e incluye tu dispositivo, qué
            intentabas hacer y, si puedes, una captura del error. Te ayudamos lo antes posible.
        </p>
    </div>
    </x-container>
</x-layouts.marketing>
