@php
    /*
     * Los precios salen de la BD (los mismos que cobra Stripe). Estaban escritos
     * a mano y se habian desincronizado: Premium se anunciaba a 17.99 cuando el
     * cobro real es 19.98, y Standard anual a 129.99 en vez de 119.99.
     */
    $priceLabels = [];
    foreach ($plans as $p) {
        $priceLabels[$p->name] = [
            'm' => $p->monthly_price !== null ? number_format((float) $p->monthly_price, 2, '.', '') : '-',
            'y' => $p->yearly_price !== null ? number_format((float) $p->yearly_price, 2, '.', '') : '-',
        ];
    }
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscribete - Yammbo Tv</title>
    <link rel="preload" href="/fonts/geist/geist-latin.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/css/noir.css?v={{ filemtime(public_path('css/noir.css')) }}">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background: var(--bg); color: #fff; min-height: 100vh;
            font-family:var(--font);
            padding: 28px 18px 40px;
        }
        .wrap { max-width: 520px; margin: 0 auto; }
        .logo { color: var(--accent-text); font-size: 28px; font-weight: 800; letter-spacing: -.02em; text-align: center; margin-bottom: 6px; }
        h1 { font-size: 22px; font-weight: 700; text-align: center; margin-bottom: 6px; }
        .sub { color: var(--muted); font-size: 14px; text-align: center; margin-bottom: 8px; }
        .who { color: var(--fg); font-size: 13px; text-align: center; margin-bottom: 22px; }
        .who strong { color: #fff; }

        .feats { list-style: none; margin: 12px 0 4px; padding: 0; text-align: left; }
        .feats li { font-size: 13px; line-height: 1.5; margin-bottom: 5px; }
        .feats li.si { color: var(--fg); }
        .feats li.no { color: var(--muted); }
        .toggle { display: flex; background: var(--soft-2); border-radius: 999px; padding: 5px; margin: 0 auto 24px; max-width: 320px; }
        .toggle button {
            flex: 1; padding: 11px; border: 0; border-radius: 999px; background: transparent;
            color: var(--muted); font-size: 14px; font-weight: 600; cursor: pointer;
        }
        .toggle button.on { background: var(--accent); color: #fff; }
        .save { font-size: 11px; opacity: .85; }

        .plan {
            background: var(--soft); border: 1px solid var(--line); border-radius:var(--radius);
            padding: 20px; margin-bottom: 14px; position: relative;
        }
        .plan.best { border-color: var(--accent); }
        .tag {
            position: absolute; top: -11px; left: 50%; transform: translateX(-50%);
            background: var(--accent); color: #fff; font-size: 11px; font-weight: 700;
            padding: 4px 14px; border-radius: 999px;
        }
        .plan h2 { font-size: 20px; font-weight: 700; margin-bottom: 4px; }
        .plan .desc { color: var(--muted); font-size: 13px; margin-bottom: 14px; }
        .price { font-size: 34px; font-weight: 800; margin-bottom: 2px; line-height: 1.1; }
        .price .amt { font-size: inherit; font-weight: inherit; color: inherit; }
        .price .per { font-size: 14px; font-weight: 500; color: var(--muted); }
        .cycle-note { color: var(--muted); font-size: 12px; margin-bottom: 16px; }
        .plan button.buy {
            width: 100%; padding: 14px; border: 0; border-radius: 12px;
            background: var(--accent); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer;
        }
        .plan.alt button.buy { background: var(--line); }
        .foot { color: var(--muted); font-size: 12px; text-align: center; margin-top: 18px; line-height: 1.5; }
        .err { background: var(--soft); border: 1px solid var(--accent); color: var(--accent-text); padding: 14px; border-radius: 12px; text-align: center; font-size: 14px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="logo">YAMBO TV</div>
    <h1>Activa tu suscripcion</h1>
    <div class="sub">Elige tu plan y paga de forma segura con Stripe.</div>

    @if(! $user)
        <div class="err">
            El codigo expiro o no es valido.<br>
            Vuelve a abrir la pantalla de suscripcion en tu TV y escanea el QR otra vez.
        </div>
    @elseif(! empty($current))
        <div class="who">Cuenta: <strong>{{ $user->email }}</strong></div>
        <div class="err" style="border-color:var(--line-strong);background:var(--soft);color:var(--fg)">
            Ya tienes el plan <strong>{{ $current['plan'] }}</strong> activo{{ $current['ends'] ? ' hasta el '.$current['ends'] : '' }}.<br>
            No necesitas pagar de nuevo: vuelve a la app de tu TV o del movil y entrara sola.
            @if($current['stripe'])<br>Para cambiar de plan o cancelarlo, entra en tv.yammbo.com/mi-suscripcion.@endif
        </div>
    @else
        <div class="who">Cuenta: <strong>{{ $user->email }}</strong></div>

        <div class="toggle">
            <button id="btn-m" class="on" onclick="setCycle('monthly')">Mensual</button>
            <button id="btn-y" onclick="setCycle('yearly')">Anual <span class="save">-17%</span></button>
        </div>

        @foreach($plans as $plan)
            @php
                $lbl = $priceLabels[$plan->name] ?? ['m' => '-', 'y' => '-'];
                $best = $plan->name === 'Standard';
            @endphp
            <div class="plan {{ $best ? 'best' : 'alt' }}">
                @if($best)<div class="tag">RECOMENDADO</div>@endif
                <h2>{{ $plan->name }}</h2>
                <div class="desc">{{ $plan->description ?? 'Acceso completo al catalogo' }}</div>
                @php
                    $feats = $plan->featureList();
                @endphp
                @if(count($feats))
                    <ul class="feats">
                        @foreach($feats as $f)
                            @php
                                $no  = $f['excluded'];
                                $txt = $f['label'];
                            @endphp
                            <li class="{{ $no ? 'no' : 'si' }}">{{ $no ? '×' : '✓' }} {{ $txt }}</li>
                        @endforeach
                    </ul>
                @endif
                <div class="price">
                    $<span class="amt" data-m="{{ $lbl['m'] }}" data-y="{{ $lbl['y'] }}">{{ $lbl['m'] }}</span>
                    <span class="per">/mes</span>
                </div>
                <div class="cycle-note per-note">Facturado cada mes</div>
                <form method="POST" action="{{ url('/precios-tv/checkout') }}">
                    @csrf
                    <input type="hidden" name="t" value="{{ $t }}">
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <input type="hidden" name="cycle" class="cycle-input" value="monthly">
                    <button class="buy" type="submit">Elegir {{ $plan->name }}</button>
                </form>
            </div>
        @endforeach

        <div class="foot">
            Al pagar, tu TV se desbloquea sola en unos segundos.<br>
            Puedes cancelar cuando quieras desde tu cuenta.
        </div>
    @endif
</div>

<script>
    function setCycle(c) {
        document.getElementById('btn-m').className = (c === 'monthly') ? 'on' : '';
        document.getElementById('btn-y').className = (c === 'yearly') ? 'on' : '';
        Array.prototype.forEach.call(document.querySelectorAll('.cycle-input'), function (i) { i.value = c; });
        Array.prototype.forEach.call(document.querySelectorAll('.amt'), function (a) {
            a.textContent = (c === 'monthly') ? a.getAttribute('data-m') : a.getAttribute('data-y');
        });
        Array.prototype.forEach.call(document.querySelectorAll('.per'), function (p) {
            p.textContent = (c === 'monthly') ? '/mes' : '/ano';
        });
        Array.prototype.forEach.call(document.querySelectorAll('.per-note'), function (p) {
            p.textContent = (c === 'monthly') ? 'Facturado cada mes' : 'Facturado una vez al ano';
        });
    }
</script>
</body>
</html>
