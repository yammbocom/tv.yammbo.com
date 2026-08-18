<?php
    use function Laravel\Folio\name;
    name('privacy');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Política de Privacidad — Yammbo Tv',
        'description' => 'Cómo Yammbo Tv recopila, usa y protege tus datos personales.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-12 sm:py-24 prose prose-lg">
        <h1>Política de Privacidad</h1>
        <p class="text-zinc-500"><em>Última actualización: 29 de mayo de 2026</em></p>

        <p>
            En Yammbo Tv respetamos tu privacidad. Esta política explica qué datos recopilamos cuando
            usas nuestra aplicación y sitio web (el «Servicio»), para qué los usamos y qué control tienes
            sobre ellos. Yammbo Tv es un servicio de suscripción de pago operado por Yammbo.
        </p>

        <h2>1. Datos que recopilamos</h2>
        <ul>
            <li><strong>Cuenta:</strong> tu nombre y correo electrónico cuando te registras.</li>
            <li><strong>Suscripción y pagos:</strong> el cobro lo procesa nuestro proveedor de pagos, Stripe.
                <strong>No almacenamos los datos completos de tu tarjeta</strong> en nuestros servidores; solo guardamos
                el estado de tu suscripción (plan, fechas, si está activa).</li>
            <li><strong>Tu biblioteca:</strong> los títulos que añades, tu progreso de reproducción y preferencias,
                para poder sincronizarlos entre tus dispositivos.</li>
            <li><strong>Datos técnicos:</strong> tipo de dispositivo, versión de la app, dirección IP y registros
                de actividad, usados para seguridad, prevención de fraude y diagnóstico.</li>
            <li><strong>Cookies:</strong> usamos cookies necesarias para iniciar sesión y mantener tu sesión. Puedes
                rechazar las no esenciales desde tu navegador.</li>
        </ul>

        <h2>2. Cómo usamos tus datos</h2>
        <ul>
            <li>Para prestar y mantener el Servicio y sincronizar tu biblioteca.</li>
            <li>Para gestionar tu suscripción, cobros y facturación.</li>
            <li>Para darte soporte y responder tus consultas.</li>
            <li>Para mejorar la app, prevenir abusos y cumplir obligaciones legales.</li>
        </ul>

        <h2>3. Con quién compartimos datos</h2>
        <p>
            <strong>No vendemos tus datos personales.</strong> Solo los compartimos con:
        </p>
        <ul>
            <li><strong>Stripe</strong>, para procesar pagos de forma segura.</li>
            <li>Proveedores de infraestructura (alojamiento, correo) que actúan en nuestro nombre.</li>
            <li>Autoridades, cuando la ley nos obliga a ello.</li>
        </ul>

        <h2>4. Contenido y complementos de terceros</h2>
        <p>
            Yammbo Tv es una interfaz que organiza catálogos y reproduce contenido proporcionado por
            complementos y fuentes de terceros. Esas fuentes son independientes de nosotros y pueden tener sus
            propias prácticas de datos. No controlamos ni nos responsabilizamos de la información que tú decidas
            facilitar a servicios de terceros.
        </p>

        <h2>5. Conservación</h2>
        <p>
            Conservamos tus datos mientras tu cuenta esté activa y durante el tiempo necesario para cumplir
            obligaciones legales o fiscales. Si cierras tu cuenta, eliminamos o anonimizamos tus datos personales
            salvo lo que debamos conservar por ley.
        </p>

        <h2>6. Seguridad</h2>
        <p>
            Aplicamos medidas técnicas y organizativas razonables para proteger tus datos. Ningún sistema es
            100&nbsp;% infalible, pero trabajamos para mantener tu información a salvo.
        </p>

        <h2>7. Tus derechos</h2>
        <p>
            Puedes solicitar acceso, rectificación o eliminación de tus datos, así como una copia de los mismos,
            escribiéndonos a <a href="mailto:soporte@yammbo.com">soporte@yammbo.com</a>.
        </p>

        <h2>8. Menores</h2>
        <p>
            El Servicio no está dirigido a menores de 13 años (o la edad mínima que exija tu país). No recopilamos
            conscientemente datos de menores sin el consentimiento de un adulto responsable.
        </p>

        <h2>9. Cambios en esta política</h2>
        <p>
            Podemos actualizar esta política. Publicaremos la versión vigente en esta página con su fecha de
            actualización. El uso continuado del Servicio implica la aceptación de los cambios.
        </p>

        <h2>10. Contacto</h2>
        <p>
            ¿Dudas sobre privacidad? Escríbenos a <a href="mailto:soporte@yammbo.com">soporte@yammbo.com</a>.
        </p>
    </x-container>
</x-layouts.marketing>
