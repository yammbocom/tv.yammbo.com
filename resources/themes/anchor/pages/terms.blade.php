<?php
    use function Laravel\Folio\name;
    name('terms');
?>

<x-layouts.marketing
    :seo="[
        'title' => 'Términos de Servicio — Yammbo Tv',
        'description' => 'Condiciones de uso del servicio de suscripción Yammbo Tv.',
        'type' => 'website',
    ]"
>
    <x-container class="max-w-3xl py-12 sm:py-24 prose prose-lg">
        <h1>Términos de Servicio</h1>
        <p class="text-zinc-500"><em>Última actualización: 29 de mayo de 2026</em></p>

        <p>
            Estos Términos rigen tu uso de Yammbo Tv (el «Servicio»), operado por Yammbo. Al crear una cuenta
            o usar el Servicio, aceptas estos Términos. Si no estás de acuerdo, no uses el Servicio.
        </p>

        <h2>1. Qué es Yammbo Tv</h2>
        <p>
            Yammbo Tv es una aplicación y un centro multimedia que organiza catálogos y reproduce contenido
            proporcionado por <strong>complementos y fuentes de terceros</strong>. <strong>Yammbo Tv no aloja, sube ni
            controla el contenido</strong> al que se accede a través de esas fuentes, ni garantiza su disponibilidad,
            legalidad o calidad. Somos un software que actúa como interfaz.
        </p>

        <h2>2. Elegibilidad y cuenta</h2>
        <p>
            Debes tener la edad mínima legal en tu país para contratar el Servicio. Eres responsable de mantener
            la confidencialidad de tus credenciales y de toda la actividad de tu cuenta.
        </p>

        <h2>3. Suscripciones, precios y renovación</h2>
        <ul>
            <li>Yammbo Tv es un <strong>servicio de pago</strong>. Los planes y precios se muestran en la página de
                <a href="/pricing">planes</a> antes de contratar.</li>
            <li>Las suscripciones se <strong>renuevan automáticamente</strong> al final de cada periodo (mensual o anual)
                al precio vigente, hasta que las canceles.</li>
            <li>Los pagos se procesan a través de <strong>Stripe</strong>. Los precios pueden incluir o excluir impuestos
                según tu país.</li>
            <li>Podemos cambiar los precios; te avisaremos con antelación razonable y los cambios se aplicarán en tu
                siguiente renovación.</li>
        </ul>

        <h2>4. Prueba gratuita</h2>
        <p>
            Si ofrecemos una prueba gratuita, al finalizar el periodo de prueba tu acceso se pausa automáticamente
            salvo que contrates un plan. No se realizan cargos durante la prueba a menos que se indique lo contrario.
        </p>

        <h2>5. Cancelación y reembolsos</h2>
        <ul>
            <li>Puedes <strong>cancelar en cualquier momento</strong> desde tu cuenta. Mantendrás el acceso hasta el final
                del periodo ya pagado; no se renovará después.</li>
            <li>Salvo que la ley de tu país disponga lo contrario, los importes ya cobrados por periodos en curso
                <strong>no son reembolsables</strong>.</li>
        </ul>

        <h2>6. Uso aceptable</h2>
        <p>Te comprometes a no:</p>
        <ul>
            <li>Usar el Servicio para fines ilícitos o que infrinjan derechos de terceros.</li>
            <li>Compartir, revender o sublicenciar tu cuenta o el acceso al Servicio.</li>
            <li>Intentar vulnerar, copiar o realizar ingeniería inversa del Servicio, ni eludir sus medidas de seguridad.</li>
        </ul>

        <h2>7. Responsabilidad sobre el contenido</h2>
        <p>
            Como el contenido proviene de fuentes de terceros, <strong>eres responsable de cómo usas el Servicio y de
            cumplir las leyes de propiedad intelectual y de tu jurisdicción</strong>. Yammbo Tv no se hace responsable del
            contenido de terceros ni de su uso indebido.
        </p>

        <h2>8. Exención de garantías</h2>
        <p>
            El Servicio se ofrece «tal cual» y «según disponibilidad», sin garantías de ningún tipo. No garantizamos
            que el Servicio sea ininterrumpido, libre de errores o que una fuente concreta esté siempre disponible.
        </p>

        <h2>9. Limitación de responsabilidad</h2>
        <p>
            En la máxima medida permitida por la ley, Yammbo no será responsable de daños indirectos, incidentales o
            consecuentes. Nuestra responsabilidad total se limita al importe que hayas pagado en los últimos 12 meses.
        </p>

        <h2>10. Terminación</h2>
        <p>
            Podemos suspender o cerrar tu cuenta si incumples estos Términos. Tú puedes dejar de usar el Servicio y
            cancelar tu suscripción cuando quieras.
        </p>

        <h2>11. Cambios en los Términos</h2>
        <p>
            Podemos actualizar estos Términos. Publicaremos la versión vigente en esta página. El uso continuado del
            Servicio tras los cambios implica su aceptación.
        </p>

        <h2>12. Contacto</h2>
        <p>
            Para cualquier consulta sobre estos Términos, escríbenos a
            <a href="mailto:soporte@yammbo.com">soporte@yammbo.com</a>.
        </p>
    </x-container>
</x-layouts.marketing>
