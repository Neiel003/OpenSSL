<?php
/**
 * Firmar un mensaje con la llave privada y verificarlo con el certificado.
 * Primero crea un certificado con cert.php.
 * Colócalo en: C:\wamp64\www\llaves\firmar.php  →  http://localhost/llaves/firmar.php
 */

$dirBase = __DIR__ . '/keysCert';

// ---------- Certificados disponibles ----------
// Cada certificado es una carpeta keysCert/<nombre>/ con privkey.pem y certout.crt (o certout.csr).
function archivoCert(string $dir): ?string {
    foreach (['certout.crt', 'certout.csr'] as $n) {
        if (file_exists("$dir/$n")) return "$dir/$n";
    }
    return null;
}
$disponibles = []; // clave => etiqueta
if (is_dir($dirBase)) {
    foreach (glob($dirBase . '/*', GLOB_ONLYDIR) as $d) {
        if (file_exists("$d/privkey.pem") && archivoCert($d)) {
            $disponibles[basename($d)] = basename($d);
        }
    }
    if (file_exists("$dirBase/privkey.pem") && archivoCert($dirBase)) {
        $disponibles['__raiz'] = 'keysCert (carpeta principal)';
    }
}

// ---------- Lógica ----------
$sel     = $_POST['certificado'] ?? '';
$mensaje = $_POST['mensaje'] ?? '';
$firma64 = trim($_POST['firma'] ?? '');
$resultado = null;
$titular = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if (!isset($disponibles[$sel])) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Certificado no válido',
            'detalle' => 'Elige un certificado de la lista.'];
    } elseif ($mensaje === '') {
        $resultado = ['tipo' => 'error', 'titulo' => 'Falta el mensaje',
            'detalle' => 'Escribe el texto que quieres firmar o verificar.'];
    } else {
        $dir      = $sel === '__raiz' ? $dirBase : "$dirBase/$sel";
        $certPem  = file_get_contents(archivoCert($dir));
        $info     = openssl_x509_parse($certPem);
        $titular  = $info['subject']['CN'] ?? $disponibles[$sel];

        if ($accion === 'firmar') {
            $priv = openssl_pkey_get_private(file_get_contents("$dir/privkey.pem"));
            if (!$priv || !openssl_sign($mensaje, $firmaBin, $priv, OPENSSL_ALGO_SHA256)) {
                $resultado = ['tipo' => 'error', 'titulo' => 'No se pudo firmar',
                    'detalle' => openssl_error_string() ?: 'No se pudo leer la llave privada.'];
            } else {
                $firma64   = base64_encode($firmaBin);
                $resultado = ['tipo' => 'ok', 'titulo' => 'Mensaje firmado',
                    'detalle' => 'La firma está en el cuadro de abajo. Cópiala para enviarla junto con el mensaje.'];
            }
        } elseif ($accion === 'verificar') {
            $firmaBin = base64_decode(preg_replace('/\s+/', '', $firma64), true);
            $pub      = openssl_pkey_get_public($certPem);
            if ($firma64 === '' || $firmaBin === false) {
                $resultado = ['tipo' => 'error', 'titulo' => 'Firma no válida',
                    'detalle' => 'Pega la firma en base64 que se generó al firmar.'];
            } elseif (!$pub) {
                $resultado = ['tipo' => 'error', 'titulo' => 'Certificado ilegible',
                    'detalle' => openssl_error_string()];
            } else {
                $ok = openssl_verify($mensaje, $firmaBin, $pub, OPENSSL_ALGO_SHA256);
                if ($ok === 1) {
                    $resultado = ['tipo' => 'ok', 'titulo' => 'Firma válida',
                        'detalle' => 'El mensaje no fue alterado y fue firmado por «' . $titular . '».'];
                } elseif ($ok === 0) {
                    $resultado = ['tipo' => 'error', 'titulo' => 'Firma inválida',
                        'detalle' => 'El mensaje fue modificado o la firma no es de este certificado.'];
                } else {
                    $resultado = ['tipo' => 'error', 'titulo' => 'Error al verificar',
                        'detalle' => openssl_error_string()];
                }
            }
        }
    }
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Firmar y verificar mensajes</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root {
    --fondo: #E9E5F1;       /* lavanda gris */
    --panel: #2E1F4D;       /* violeta profundo */
    --panel-texto: #EEE9F8;
    --tarjeta: #FFFFFF;
    --tinta: #251A3A;
    --suave: #6A5F80;
    --borde: #D3CBE3;
    --menta: #7BDCB5;
    --menta-osc: #17724F;
    --ok: #17724F; --ok-fondo: #E0F5EC;
    --error: #B03A2A; --error-fondo: #FBE9E5;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
    background: var(--fondo); color: var(--tinta);
    font-family: 'Manrope', system-ui, 'Segoe UI', sans-serif;
  }
  .caja {
    width: 100%; max-width: 1000px; display: grid; grid-template-columns: 5fr 7fr;
    background: var(--tarjeta); border-radius: 20px; overflow: hidden;
    box-shadow: 0 30px 60px -25px rgba(46,31,77,.5);
  }
  .lado {
    background: var(--panel); color: var(--panel-texto); padding: 48px 40px;
    display: flex; flex-direction: column; justify-content: space-between; gap: 32px;
  }
  .lado h1 { font-size: 2rem; font-weight: 800; line-height: 1.15; margin: 0 0 12px; letter-spacing: -.02em; }
  .lado p  { margin: 0; color: #BDB2D6; line-height: 1.6; max-width: 34ch; }
  .sello   { width: 100%; max-width: 230px; }
  .form { padding: 44px; }
  .form h2 { margin: 0 0 6px; font-size: 1.5rem; font-weight: 800; }
  .form > p { margin: 0 0 24px; color: var(--suave); }
  label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: 6px; }
  .campo { margin-bottom: 16px; }
  select, textarea {
    width: 100%; padding: 12px 14px; font: inherit; color: var(--tinta);
    background: #F7F5FB; border: 1.5px solid var(--borde); border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
  }
  textarea { min-height: 84px; resize: vertical; }
  textarea.mono { font: .78rem/1.5 'JetBrains Mono', Consolas, monospace; word-break: break-all; }
  select:focus-visible, textarea:focus-visible, button:focus-visible, a:focus-visible {
    outline: none; border-color: var(--menta-osc); box-shadow: 0 0 0 3px rgba(123,220,181,.5);
  }
  .botones { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 6px; }
  button {
    font: inherit; font-weight: 700; padding: 14px; border-radius: 10px; cursor: pointer;
    border: 1.5px solid transparent; transition: background .15s, transform .1s;
  }
  button:active { transform: translateY(1px); }
  .primario   { background: var(--menta); color: #0C2A1E; }
  .primario:hover { background: #92E6C4; }
  .secundario { background: transparent; color: var(--tinta); border-color: var(--tinta); }
  .secundario:hover { background: #F1EEF7; }
  .aviso { margin-top: 22px; padding: 14px 16px; border-radius: 10px; border-left: 5px solid; }
  .aviso strong { display: block; margin-bottom: 2px; }
  .aviso.ok    { background: var(--ok-fondo);    border-color: var(--ok);    color: #0E4631; }
  .aviso.error { background: var(--error-fondo); border-color: var(--error); color: #6E2217; }
  .vacio { padding: 18px; border: 1.5px dashed var(--borde); border-radius: 10px; color: var(--suave); line-height: 1.5; }
  .vacio a { color: var(--menta-osc); font-weight: 700; }
  @media (max-width: 860px) {
    .caja { grid-template-columns: 1fr; }
    .lado { padding: 28px; flex-direction: row; align-items: center; }
    .sello { max-width: 90px; flex-shrink: 0; }
    .lado p { display: none; }
    .lado h1 { font-size: 1.4rem; margin: 0; }
    .form { padding: 28px 24px; }
  }
  @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
</head>
<body>
<main class="caja">

  <aside class="lado">
    <div>
      <h1>Firma y comprueba.</h1>
      <p>La llave privada firma el mensaje. Quien tenga tu certificado público puede comprobar que nadie lo cambió.</p>
    </div>
    <svg class="sello" viewBox="0 0 200 160" role="img" aria-label="Ilustración de un documento firmado">
      <path d="M45 15 H120 L155 50 V145 H45 Z" fill="none" stroke="#7BDCB5" stroke-width="8" stroke-linejoin="round"/>
      <path d="M120 15 V50 H155" fill="none" stroke="#7BDCB5" stroke-width="8" stroke-linejoin="round"/>
      <path d="M68 78 H110 M68 98 H98" stroke="#7BDCB5" stroke-width="8" stroke-linecap="round"/>
      <path d="M72 128 q8 -16 16 0 t16 0 t16 0" fill="none" stroke="#7BDCB5" stroke-width="7" stroke-linecap="round"/>
    </svg>
  </aside>

  <section class="form">
    <h2>Firma digital</h2>
    <p>Elige un certificado, escribe el mensaje y firma o verifica.</p>

    <?php if (!$disponibles): ?>
      <div class="vacio">
        Todavía no hay certificados. Crea uno primero en
        <a href="cert.php">cert.php</a> y vuelve aquí.
      </div>
    <?php else: ?>
    <form method="post" autocomplete="off">
      <div class="campo">
        <label for="certificado">Certificado</label>
        <select id="certificado" name="certificado" required>
          <?php foreach ($disponibles as $k => $etiqueta): ?>
            <option value="<?= e($k) ?>"<?= $k === $sel ? ' selected' : '' ?>><?= e($etiqueta) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="campo">
        <label for="mensaje">Mensaje</label>
        <textarea id="mensaje" name="mensaje" placeholder="ej. Este es un mensaje confidencial" required><?= e($mensaje) ?></textarea>
      </div>
      <div class="campo">
        <label for="firma">Firma (base64)</label>
        <textarea id="firma" name="firma" class="mono"
                  placeholder="Se llena sola al firmar. Para verificar, pega aquí la firma recibida."><?= e($firma64) ?></textarea>
      </div>
      <div class="botones">
        <button class="primario"   type="submit" name="accion" value="firmar">Firmar mensaje</button>
        <button class="secundario" type="submit" name="accion" value="verificar">Verificar firma</button>
      </div>
    </form>
    <?php endif; ?>

    <?php if ($resultado): ?>
      <div class="aviso <?= e($resultado['tipo']) ?>" role="status">
        <strong><?= e($resultado['titulo']) ?></strong>
        <?= e($resultado['detalle']) ?>
      </div>
    <?php endif; ?>
  </section>

</main>
</body>
</html>