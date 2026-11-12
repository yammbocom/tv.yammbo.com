<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark">
    <meta name="supported-color-schemes" content="dark">
    <title>{{ $subject ?? 'Yammbo Tv' }}</title>
</head>
{{-- Correos: tablas + estilos en linea (los clientes de correo ignoran CSS moderno) --}}
<body style="margin:0;padding:0;background:#000000;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#000000;padding:28px 12px;">
    <tr>
        <td align="center">

            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                   style="max-width:560px;background:#0a0a0a;border:1px solid #262626;border-radius:12px;overflow:hidden;">

                {{-- Cabecera --}}
                <tr>
                    <td style="padding:30px 34px 0 34px;">
                        <div style="font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
                                    color:#ffffff;font-size:24px;font-weight:800;">YAMBO TV</div>
                    </td>
                </tr>

                {{-- Franja de color segun el tipo de aviso --}}
                <tr>
                    <td style="padding:18px 34px 0 34px;">
                        <div style="height:3px;width:56px;background:{{ $accent ?? '#E50914' }};border-radius:99px;"></div>
                    </td>
                </tr>

                {{-- Contenido --}}
                <tr>
                    <td style="padding:22px 34px 8px 34px;
                               font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                        <h1 style="margin:0 0 12px 0;color:#ffffff;font-size:23px;font-weight:800;line-height:1.25;">
                            {{ $title }}
                        </h1>
                        <div style="color:#a3a3a3;font-size:15px;line-height:1.6;">
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
                                <td style="background:{{ $accent ?? '#E50914' }};border-radius:8px;">
                                    <a href="{{ $actionUrl }}"
                                       style="display:inline-block;padding:14px 30px;color:#ffffff;text-decoration:none;
                                              font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
                                              font-size:16px;font-weight:700;">{{ $actionText }}</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:14px 34px 0 34px;
                               font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                        <div style="color:#a3a3a3;font-size:12px;line-height:1.6;word-break:break-all;">
                            Si el boton no funciona, copia y pega este enlace:<br>
                            <span style="color:#a3a3a3;">{{ $actionUrl }}</span>
                        </div>
                    </td>
                </tr>
                @endif

                {{-- Datos extra (plan, fechas, importes) --}}
                @if(!empty($rows))
                <tr>
                    <td style="padding:22px 34px 0 34px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                               style="border:1px solid #262626;border-radius:12px;
                                      font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                            @foreach($rows as $label => $value)
                            <tr>
                                <td style="padding:13px 18px;color:#a3a3a3;font-size:13px;">{{ $label }}</td>
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
                               font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
                        <div style="color:#a3a3a3;font-size:12px;line-height:1.6;">{!! $note !!}</div>
                    </td>
                </tr>
                @endif

                {{-- Pie --}}
                <tr>
                    <td style="padding:28px 34px 30px 34px;">
                        <div style="height:1px;background:#262626;margin-bottom:18px;"></div>
                        <div style="font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
                                    color:#a3a3a3;font-size:12px;line-height:1.6;">
                            Este mensaje es de <span style="color:#a3a3a3;">Yammbo Tv</span>.<br>
                            Gestiona tu cuenta en <a href="https://tv.yammbo.com/mi-suscripcion"
                               style="color:#FF4D55;text-decoration:none;">tv.yammbo.com</a>
                        </div>
                    </td>
                </tr>
            </table>

            <div style="font-family:Geist,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;
                        color:#a3a3a3;font-size:11px;margin-top:16px;">
                &copy; {{ date('Y') }} Yammbo Tv
            </div>

        </td>
    </tr>
</table>
</body>
</html>
