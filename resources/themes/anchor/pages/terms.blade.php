<?php
    use function Laravel\Folio\name;
    name('terms');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Términos de Servicio — Yammbo Tv',
        'description' => 'Condiciones de uso del software y las aplicaciones Yammbo Tv.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-16 sm:py-24">
    <div class="prose-content">
        <h1>Términos de Servicio</h1>
        <p class="text-[color:var(--color-ink-dim)]"><em>Última actualización: 26 de agosto de 2026</em></p>

        <p>
            Estos Términos regulan el uso de Yammbo Tv (el «Servicio»), operado por Yammbo. Al crear una cuenta,
            instalar nuestras aplicaciones o usar el Servicio, aceptas estos Términos. Si no estás de acuerdo,
            no uses el Servicio.
        </p>

        <h2>1. Qué es Yammbo Tv (y qué no es)</h2>
        <p>
            Yammbo Tv es <strong>software</strong>: un reproductor y organizador multimedia que presenta un catálogo
            y reproduce material servido por <strong>complementos, fuentes y proveedores de terceros</strong>.
        </p>
        <p>
            <strong>Yammbo Tv no aloja, almacena, sube, copia, transmite ni controla ningún contenido.</strong>
            No guardamos películas, series, canales, pistas de audio, subtítulos ni portadas en nuestros servidores.
            Todo el material —incluidas las imágenes y los metadatos— procede de servicios de terceros a los que
            la aplicación se conecta, y viaja directamente entre esos terceros y tu dispositivo, sin pasar por
            nosotros. No indexamos ni catalogamos ese material: solo mostramos lo que esas fuentes devuelven.
        </p>
        <p>
            No garantizamos la disponibilidad, la legalidad, la exactitud ni la calidad de lo que ofrezcan
            esas fuentes, porque no dependen de nosotros y pueden cambiar o desaparecer en cualquier momento.
        </p>

        <h2>2. Qué estás pagando</h2>
        <p>
            La suscripción es una <strong>licencia de uso del software</strong>: las aplicaciones para móvil y
            televisor, la interfaz web, el sistema de cuentas y sincronización, el soporte y el mantenimiento y
            las mejoras continuas.
        </p>
        <p>
            <strong>La suscripción no es un pago por contenido.</strong> No vendemos, revendemos, licenciamos ni
            distribuimos películas, series ni canales, y el precio no otorga ningún derecho sobre ellos. Si una
            fuente de terceros deja de funcionar o retira material, la suscripción sigue cubriendo lo que sí
            prestamos: el software y el servicio de cuenta.
        </p>

        <h2>3. Cuenta y periodo de prueba</h2>
        <ul>
            <li>Debes registrarte con una dirección de correo válida y ser mayor de edad en tu país, o contar con el permiso de un tutor.</li>
            <li>Eres responsable de tus credenciales y de la actividad de tu cuenta. Avísanos si detectas un uso no autorizado.</li>
            <li>Las cuentas nuevas disponen de un <strong>periodo de prueba de 7 días</strong>. Al terminar, hace falta una suscripción activa para seguir usando el Servicio.</li>
            <li>La cuenta es personal. No la compartas fuera de tu hogar ni revendas el acceso.</li>
        </ul>

        <h2>4. Planes, dispositivos y pagos</h2>
        <p>Cada plan permite un número máximo de dispositivos usándose a la vez:</p>
        <ul>
            <li><strong>Basic</strong> — 1 dispositivo. No incluye TV en vivo.</li>
            <li><strong>Standard</strong> — 2 dispositivos. Incluye TV en vivo.</li>
            <li><strong>Premium</strong> — 3 dispositivos. Incluye TV en vivo.</li>
        </ul>
        <ul>
            <li>Los pagos se procesan a través de <strong>Stripe</strong>. No almacenamos los datos de tu tarjeta.</li>
            <li>La suscripción <strong>se renueva automáticamente</strong> al final de cada periodo hasta que la canceles.</li>
            <li>Puedes <strong>cancelar cuando quieras</strong> desde tu cuenta. Conservarás el acceso hasta el final del periodo ya pagado.</li>
            <li>Los precios pueden cambiar; te avisaremos antes de que se aplique a tu renovación.</li>
            <li>Salvo que la ley de tu país disponga otra cosa, los periodos ya iniciados no son reembolsables. Si algo ha ido mal, escríbenos y lo miramos.</li>
        </ul>

        <h2>5. Uso aceptable</h2>
        <p>Al usar el Servicio te comprometes a no:</p>
        <ul>
            <li>Revender, sublicenciar, redistribuir o explotar comercialmente el Servicio o tu cuenta.</li>
            <li>Compartir credenciales para eludir los límites de dispositivos de tu plan.</li>
            <li>Modificar, descompilar o intentar extraer el código de las aplicaciones, salvo donde la ley lo permita.</li>
            <li>Interferir con la infraestructura, sortear medidas de seguridad o automatizar accesos masivos.</li>
            <li>Usar el Servicio para actividades ilícitas o para infringir derechos de terceros.</li>
        </ul>
        <p>
            Eres responsable de que el uso que hagas del Servicio y del material al que accedas a través de él
            cumpla las leyes de tu país.
        </p>

        <h2>6. Contenido de terceros y propiedad intelectual</h2>
        <p>
            Las marcas, títulos, carátulas y demás material que aparezcan en la aplicación pertenecen a sus
            respectivos titulares, y se muestran tal y como los devuelven las fuentes de terceros. Su aparición
            no implica ninguna relación, patrocinio ni respaldo entre esos titulares y Yammbo Tv.
        </p>
        <p>
            El software, la marca Yammbo Tv, su diseño e interfaz sí son nuestros y están protegidos por la
            legislación de propiedad intelectual.
        </p>

        <h2>7. Notificaciones de derechos de autor</h2>
        <p>
            Como no alojamos contenido, no podemos retirar material que no está en nuestros servidores: eso
            corresponde al proveedor que lo sirve. Aun así, tomamos en serio las notificaciones de titulares de
            derechos y, cuando proceda, <strong>dejaremos de mostrar la fuente señalada</strong> dentro de nuestro
            software.
        </p>
        <p>
            Si eres titular de derechos y consideras que el Servicio facilita el acceso a material que infringe
            los tuyos, escríbenos a <a href="mailto:support@yammbo.com">support@yammbo.com</a> indicando el
            material, la fuente concreta y tu acreditación como titular o representante. Responderemos con
            diligencia.
        </p>

        <h2>8. Disponibilidad y cambios</h2>
        <p>
            Trabajamos para que el Servicio esté siempre disponible, pero puede haber interrupciones por
            mantenimiento, incidencias o causas ajenas a nosotros, incluidos cortes en fuentes de terceros o
            bloqueos impuestos por tu operador de red. Podemos modificar o descontinuar funciones; si un cambio
            es relevante, te avisaremos.
        </p>

        <h2>9. Garantías y responsabilidad</h2>
        <p>
            El Servicio se presta «tal cual». En la medida que permita la ley, no ofrecemos garantías implícitas
            de comerciabilidad o idoneidad para un fin concreto, y nuestra responsabilidad total frente a ti
            quedará limitada al importe que hayas pagado en los doce meses anteriores al hecho que la origine.
            Nada de lo anterior limita responsabilidades que no puedan excluirse legalmente.
        </p>

        <h2>10. Suspensión y terminación</h2>
        <p>
            Puedes dejar de usar el Servicio y eliminar tu cuenta cuando quieras. Podemos suspender o cerrar
            cuentas que incumplan estos Términos o que pongan en riesgo el Servicio o a otros usuarios.
        </p>

        <h2>11. Cambios en estos Términos</h2>
        <p>
            Podemos actualizar estos Términos. Publicaremos la nueva versión con su fecha y, si el cambio es
            sustancial, te lo comunicaremos. Seguir usando el Servicio después implica aceptarlos.
        </p>

        <h2>12. Contacto</h2>
        <p>
            Para cualquier duda sobre estos Términos: <a href="mailto:support@yammbo.com">support@yammbo.com</a>.
        </p>
    </div>
    </x-container>
</x-layouts.marketing>
