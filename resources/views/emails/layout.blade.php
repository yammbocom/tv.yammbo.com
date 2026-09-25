<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title>{{ $subject ?? 'Yammbo Tv' }}</title>
</head>
{{-- Correos: tablas + estilos en linea (los clientes de correo ignoran CSS moderno) --}}
<body style="margin:0;padding:0;background:#0a0a0a;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#0a0a0a;padding:28px 12px;">
    <tr>
        <td align="center">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="max-width:560px;background:#141416;border-radius:18px;overflow:hidden;">

                {{-- Cabecera --}}
                <tr>
                    <td style="padding:30px 34px 0 34px;">
                        <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
                                    color:#e50914;font-size:24px;font-weight:800;letter-spacing:3px;">YAMBO TV</div>
                    </td>
                </tr>

                {{-- Franja de color segun el tipo de aviso --}}
                <tr>
                    <td style="padding:18px 34px 0 34px;">
                        <div style="height:3px;width:56px;background:{{ $accent ?? '#e50914' }};border-radius:99px;"></div>
                    </td>
                </tr>

                {{-- Contenido --}}
                <tr>
                    <td style="padding:22px 34px 8px 34px;
                               font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                        <h1 style="margin:0 0 12px 0;color:#ffffff;font-size:23px;font-weight:800;line-height:1.25;">
                            {{ $title }}
                        </h1>
                        <div style="color:#bfbfbf;font-size:15px;line-height:1.6;">
                            {!! $body !!}
                        </div>
                    </td>
                </tr>

                {{-- Boton principal --}}
                @if(!empty($actionUrl) && !empty($actionText))
                <tr>
                    <td style="padding:24px 34px 6px 34px;">
                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="background:{{ $accent ?? '#e50914' }};border-radius:12px;">
                                    <a href="{{ $actionUrl }}"
                                       style="display:inline-block;padding:14px 30px;color:#ffffff;text-decoration:none;
                                              font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
                                              font-size:16px;font-weight:700;">{{ $actionText }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:14px 34px 0 34px;
                               font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                        <div style="color:#6b6b70;font-size:12px;line-height:1.6;word-break:break-all;">
                            Si el boton no funciona, copia y pega este enlace:<br>
                            <span style="color:#9b9b9f;">{{ $actionUrl }}</span>
                        </div>
                    </td>
                </tr>
                @endif

                {{-- Datos extra (plan, fechas, importes) --}}
                @if(!empty($rows))
                <tr>
                    <td style="padding:22px 34px 0 34px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="background:#1b1b1f;border-radius:12px;
                                      font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                            @foreach($rows as $label => $value)
                            <tr>
                                <td style="padding:13px 18px;color:#9b9b9f;font-size:13px;">{{ $label }}</td>
                                <td align="right" style="padding:13px 18px;color:#ffffff;font-size:13px;font-weight:700;">{{ $value }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </td>
                </tr>
                @endif

                {{-- Nota al pie del contenido --}}
                @if(!empty($note))
                <tr>
                    <td style="padding:20px 34px 0 34px;
                               font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;">
                        <div style="color:#6b6b70;font-size:12px;line-height:1.6;">{!! $note !!}</div>
                    </td>
                </tr>
                @endif

                {{-- Pie --}}
                <tr>
                    <td style="padding:28px 34px 30px 34px;">
                        <div style="height:1px;background:#232327;margin-bottom:18px;"></div>
                        <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
                                    color:#6b6b70;font-size:12px;line-height:1.6;">
                            Este mensaje es de <span style="color:#9b9b9f;">Yammbo Tv</span>.<br>
                            Gestiona tu cuenta en <a href="https://tv.yammbo.com/mi-suscripcion"
                               style="color:#e50914;text-decoration:none;">tv.yammbo.com</a>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif;
                        color:#4a4a50;font-size:11px;margin-top:16px;">
                &copy; {{ date('Y') }} Yammbo Tv
            </div>

        </td>
    </tr>
</table>
</body>
</html>
