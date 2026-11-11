@php($v = preg_match('/^[A-Za-z0-9_-]{11}$/', (string) request('v')) ? request('v') : '')
@php($hl = preg_match('/^[a-z]{2}$/', (string) request('hl')) ? request('hl') : 'es')
<!doctype html>
<html lang="{{ $hl }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex">
<title>Trailer</title>
<style>
  html, body { margin: 0; height: 100%; background: #000; overflow: hidden; }
  #p { position: fixed; inset: 0; width: 100%; height: 100%; border: 0; }
</style>
</head>
<body>
<div id="p"></div>
<script>
  // Reproductor de trailers para la app de TV: la app lo maneja con el mando via ymb*().
  var VID = @json($v), HL = @json($hl), player = null;
  function send(ev, d) {
    try { if (window.YmbTrailer) YmbTrailer.onEvent(ev, d == null ? '' : String(d)); } catch (e) {}
  }
  function onYouTubeIframeAPIReady() {
    if (!VID) { send('error', 'novideo'); return; }
    player = new YT.Player('p', {
      videoId: VID,
      host: 'https://www.youtube-nocookie.com',
      playerVars: { autoplay: 1, controls: 0, rel: 0, modestbranding: 1, playsinline: 1,
                    iv_load_policy: 3, fs: 0, disablekb: 1, hl: HL, cc_lang_pref: HL },
      events: {
        onReady: function (e) { e.target.playVideo(); send('ready'); },
        onStateChange: function (e) { send('state', e.data); },
        onError: function (e) { send('error', e.data); }
      }
    });
  }
  function ymbToggle() {
    if (!player) return;
    if (player.getPlayerState() === 1) player.pauseVideo(); else player.playVideo();
  }
  function ymbSeek(d) {
    if (player) player.seekTo(Math.max(0, player.getCurrentTime() + d), true);
  }
  function ymbInfo() {
    if (!player || !player.getDuration) return '';
    return player.getCurrentTime() + '|' + player.getDuration() + '|' + player.getPlayerState();
  }
</script>
<script src="https://www.youtube.com/iframe_api"></script>
</body>
</html>
