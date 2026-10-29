<?php
/**
 * Login de llaves RSA – genera llaves, firma y verifica.
 * Colócalo en: C:\wamp64\www\llaves\index.php  →  http://localhost/llaves/
 */

// ---------- Configuración de OpenSSL (WAMP) ----------
$candidatos = array_filter([
    getenv('OPENSSL_CONF'),
    'C:/wamp64/bin/apache/apache2.4.65/conf/openssl.cnf',
    'C:/wamp64/bin/php/' . 'php' . PHP_VERSION . '/extras/ssl/openssl.cnf',
]);
$configArgs = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];
foreach ($candidatos as $ruta) {
    if ($ruta && file_exists($ruta)) { $configArgs['config'] = $ruta; break; }
}

$dirBase = __DIR__ . '/keys';
if (!is_dir($dirBase)) {
    mkdir($dirBase, 0775, true);
    // Bloquea el acceso web a la carpeta de llaves (Apache 2.4)
    file_put_contents($dirBase . '/.htaccess', "Require all denied\n");
}

// ---------- Lógica ----------
$resultado = null; // ['tipo' => ok|error, 'titulo' => , 'detalle' => , 'publica' => , 'firma' => ]
$usuario   = trim($_POST['usuario'] ?? '');
$mensaje   = $_POST['mensaje'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if (!preg_match('/^[A-Za-z0-9_-]{3,30}$/', $usuario)) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Usuario no válido',
            'detalle' => 'Usa de 3 a 30 caracteres: letras, números, guion o guion bajo.'];
    } elseif (trim($mensaje) === '') {
        $resultado = ['tipo' => 'error', 'titulo' => 'Falta el mensaje',
            'detalle' => 'Escribe el texto que quieres firmar o verificar.'];
    } else {
        $dir      = "$dirBase/$usuario";
        $fPriv    = "$dir/private_key.pem";
        $fPub     = "$dir/public_key.pem";
        $fFirma   = "$dir/signature.dat";

        if ($accion === 'firmar') {
            if (!is_dir($dir)) mkdir($dir, 0775, true);

            // Genera llaves si no existen o si se pidió regenerarlas
            if (!file_exists($fPriv) || !empty($_POST['regenerar'])) {
                $par = openssl_pkey_new($configArgs);
                if (!$par) {
                    $resultado = ['tipo' => 'error', 'titulo' => 'No se pudieron crear las llaves',
                        'detalle' => 'Revisa la ruta de openssl.cnf. ' . openssl_error_string()];
                } else {
                    $det = openssl_pkey_get_details($par);
                    openssl_pkey_export($par, $privPem, null, $configArgs);
                    file_put_contents($fPriv, $privPem);
                    file_put_contents($fPub, $det['key']);
                }
            }

            if (!$resultado) {
                $priv = openssl_get_privatekey(file_get_contents($fPriv));
                if (openssl_sign($mensaje, $firma, $priv, OPENSSL_ALGO_SHA256)) {
                    file_put_contents($fFirma, $firma);
                    $resultado = ['tipo' => 'ok', 'titulo' => 'Mensaje firmado',
                        'detalle' => 'La firma se guardó para el usuario «' . $usuario . '».',
                        'publica' => file_get_contents($fPub),
                        'firma'   => chunk_split(base64_encode($firma), 64, "\n")];
                } else {
                    $resultado = ['tipo' => 'error', 'titulo' => 'No se pudo firmar',
                        'detalle' => openssl_error_string()];
                }
            }
        } elseif ($accion === 'verificar') {
            if (!file_exists($fPub) || !file_exists($fFirma)) {
                $resultado = ['tipo' => 'error', 'titulo' => 'Sin firma guardada',
                    'detalle' => 'Este usuario todavía no tiene llaves ni firma. Firma un mensaje primero.'];
            } else {
                $pub   = file_get_contents($fPub);
                $firma = file_get_contents($fFirma);
                $ok    = openssl_verify($mensaje, $firma, $pub, OPENSSL_ALGO_SHA256) === 1;
                $resultado = $ok
                    ? ['tipo' => 'ok', 'titulo' => 'Firma válida',
                       'detalle' => 'El mensaje es auténtico y no fue modificado.']
                    : ['tipo' => 'error', 'titulo' => 'Firma inválida',
                       'detalle' => 'El mensaje fue alterado o no corresponde a la firma de este usuario.'];
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
<title>Llavero – Firma digital RSA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root {
    --fondo: #DDE3EC;
    --panel: #1C2740;
    --panel-texto: #E9EDF5;
    --tarjeta: #FFFFFF;
    --tinta: #1B2433;
    --suave: #5B6577;
    --borde: #CBD3E0;
    --laton: #C58B2A;
    --laton-osc: #9A6A17;
    --ok: #2B7A57;
    --ok-fondo: #E4F3EB;
    --error: #B03A2A;
    --error-fondo: #FBE9E5;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
    background: var(--fondo); color: var(--tinta);
    font-family: 'Manrope', system-ui, 'Segoe UI', sans-serif;
  }
  .caja {
    width: 100%; max-width: 980px; display: grid; grid-template-columns: 5fr 6fr;
    background: var(--tarjeta); border-radius: 20px; overflow: hidden;
    box-shadow: 0 30px 60px -25px rgba(28,39,64,.45);
  }
  .lado {
    background: var(--panel); color: var(--panel-texto); padding: 48px 40px;
    display: flex; flex-direction: column; justify-content: space-between; gap: 32px;
  }
  .lado h1 { font-size: 2rem; font-weight: 800; line-height: 1.15; margin: 0 0 12px; letter-spacing: -.02em; }
  .lado p  { margin: 0; color: #B6C0D4; line-height: 1.6; max-width: 34ch; }
  .llave   { width: 100%; max-width: 280px; }
  .form { padding: 48px 44px; }
  .form h2 { margin: 0 0 6px; font-size: 1.5rem; font-weight: 800; }
  .form > p { margin: 0 0 28px; color: var(--suave); }
  label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: 6px; }
  .campo { margin-bottom: 18px; }
  input[type=text], textarea {
    width: 100%; padding: 13px 14px; font: inherit; color: var(--tinta);
    background: #F6F8FB; border: 1.5px solid var(--borde); border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
  }
  textarea { min-height: 110px; resize: vertical; }
  input:focus-visible, textarea:focus-visible, button:focus-visible {
    outline: none; border-color: var(--laton); box-shadow: 0 0 0 3px rgba(197,139,42,.3);
  }
  .check { display: flex; align-items: center; gap: 8px; font-size: .9rem; color: var(--suave); margin-bottom: 22px; }
  .check input { accent-color: var(--laton-osc); width: 16px; height: 16px; }
  .check label { margin: 0; font-weight: 400; }
  .botones { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  button {
    font: inherit; font-weight: 700; padding: 14px; border-radius: 10px; cursor: pointer;
    border: 1.5px solid transparent; transition: background .15s, transform .1s;
  }
  button:active { transform: translateY(1px); }
  .primario   { background: var(--laton); color: #1F1608; }
  .primario:hover { background: #D39A36; }
  .secundario { background: transparent; color: var(--tinta); border-color: var(--tinta); }
  .secundario:hover { background: #EEF1F6; }
  .aviso { margin-top: 24px; padding: 14px 16px; border-radius: 10px; border-left: 5px solid; }
  .aviso strong { display: block; margin-bottom: 2px; }
  .aviso.ok    { background: var(--ok-fondo);    border-color: var(--ok);    color: #174A34; }
  .aviso.error { background: var(--error-fondo); border-color: var(--error); color: #6E2217; }
  details { margin-top: 16px; border: 1.5px solid var(--borde); border-radius: 10px; padding: 10px 14px; }
  summary { cursor: pointer; font-weight: 600; font-size: .9rem; }
  pre {
    margin: 10px 0 0; font: .75rem/1.5 'JetBrains Mono', Consolas, monospace;
    white-space: pre-wrap; word-break: break-all; max-height: 170px; overflow: auto; color: var(--suave);
  }
  @media (max-width: 820px) {
    .caja { grid-template-columns: 1fr; }
    .lado { padding: 32px 28px; flex-direction: row; align-items: center; }
    .llave { max-width: 110px; flex-shrink: 0; }
    .lado p { display: none; }
    .lado h1 { font-size: 1.4rem; margin: 0; }
    .form { padding: 32px 26px; }
  }
  @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
</head>
<body>
<main class="caja">

  <aside class="lado">
    <div>
      <h1>Tu firma, tu llave.</h1>
      <p>Cada usuario tiene un par de llaves RSA. La privada firma el mensaje y la pública comprueba que nadie lo haya cambiado.</p>
    </div>
    <svg class="llave" viewBox="0 0 280 140" role="img" aria-label="Ilustración de una llave">
      <g fill="none" stroke="#C58B2A" stroke-width="10" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="62" cy="70" r="44"/>
        <circle cx="62" cy="70" r="14" fill="#C58B2A" stroke="none"/>
        <path d="M106 70 H262"/>
        <path d="M222 70 v30"/>
        <path d="M246 70 v22"/>
      </g>
    </svg>
  </aside>

  <section class="form">
    <h2>Acceso con llaves</h2>
    <p>Ingresa tu usuario y el mensaje que quieres firmar o comprobar.</p>

    <form method="post" autocomplete="off">
      <div class="campo">
        <label for="usuario">Usuario</label>
        <input type="text" id="usuario" name="usuario" value="<?= e($usuario) ?>"
               placeholder="ej. maria_perez" required minlength="3" maxlength="30">
      </div>
      <div class="campo">
        <label for="mensaje">Mensaje</label>
        <textarea id="mensaje" name="mensaje" placeholder="Escribe el texto a firmar" required><?= e($mensaje) ?></textarea>
      </div>
      <div class="check">
        <input type="checkbox" id="regenerar" name="regenerar" value="1">
        <label for="regenerar">Generar llaves nuevas al firmar</label>
      </div>
      <div class="botones">
        <button class="primario"   type="submit" name="accion" value="firmar">Firmar mensaje</button>
        <button class="secundario" type="submit" name="accion" value="verificar">Verificar firma</button>
      </div>
    </form>

    <?php if ($resultado): ?>
      <div class="aviso <?= e($resultado['tipo']) ?>" role="status">
        <strong><?= e($resultado['titulo']) ?></strong>
        <?= e($resultado['detalle']) ?>
      </div>
      <?php if (!empty($resultado['publica'])): ?>
        <details>
          <summary>Llave pública</summary>
          <pre><?= e($resultado['publica']) ?></pre>
        </details>
        <details>
          <summary>Firma (base64)</summary>
          <pre><?= e($resultado['firma']) ?></pre>
        </details>
      <?php endif; ?>
    <?php endif; ?>
  </section>

</main>
</body>
</html>