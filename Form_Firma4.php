<?php
/**
 * Generador de CSR (solicitud de certificado) con OpenSSL.
 * Colócalo en: C:\wamp64\www\llaves\csr.php  →  http://localhost/llaves/csr.php
 */

// ---------- Configuración de OpenSSL ----------
$candidatos = array_filter([
    getenv('OPENSSL_CONF'),
    'C:\openssl\bin\cnf\openssl.cnf',
    'C:/wamp64/bin/apache/apache2.4.65/conf/openssl.cnf',
    'C:/wamp64/bin/php/php' . PHP_VERSION . '/extras/ssl/openssl.cnf',
]);
$config = [
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'digest_alg'       => 'sha256',
];
foreach ($candidatos as $ruta) {
    if ($ruta && file_exists($ruta)) { $config['config'] = $ruta; break; }
}

$dirBase = __DIR__ . '/keys';
if (!is_dir($dirBase)) {
    mkdir($dirBase, 0775, true);
    file_put_contents($dirBase . '/.htaccess', "Require all denied\n"); // protege las llaves
}

// ---------- Lógica ----------
$campos = ['commonName', 'organizationName', 'organizationalUnitName',
           'localityName', 'stateOrProvinceName', 'countryName', 'emailAddress'];
$v = [];
foreach ($campos as $c) $v[$c] = trim($_POST[$c] ?? '');

$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('/^[A-Za-z0-9._-]{3,64}$/', $v['commonName'])) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Nombre común no válido',
            'detalle' => 'Usa de 3 a 64 caracteres: letras, números, punto, guion o guion bajo.'];
    } elseif ($v['countryName'] !== '' && !preg_match('/^[A-Za-z]{2}$/', $v['countryName'])) {
        $resultado = ['tipo' => 'error', 'titulo' => 'País no válido',
            'detalle' => 'Escribe el código de 2 letras, por ejemplo PA.'];
    } elseif ($v['emailAddress'] !== '' && !filter_var($v['emailAddress'], FILTER_VALIDATE_EMAIL)) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Correo no válido',
            'detalle' => 'Revisa el formato del correo electrónico.'];
    } else {
        // Solo se envían al DN los campos que tienen valor
        $dn = array_filter($v, fn($x) => $x !== '');
        if (isset($dn['countryName'])) $dn['countryName'] = strtoupper($dn['countryName']);

        $pkey = openssl_pkey_new($config);
        $csr  = $pkey ? openssl_csr_new($dn, $pkey, $config) : false;

        if (!$pkey || !$csr
            || !openssl_csr_export($csr, $csrPem)
            || !openssl_pkey_export($pkey, $privPem, null, $config)) {
            $resultado = ['tipo' => 'error', 'titulo' => 'No se pudo generar el CSR',
                'detalle' => 'Revisa la ruta de openssl.cnf. ' . openssl_error_string()];
        } else {
            $dir = "$dirBase/" . $v['commonName'];
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            file_put_contents("$dir/private.key", $privPem);
            file_put_contents("$dir/request.csr", $csrPem);
            $resultado = ['tipo' => 'ok', 'titulo' => 'Solicitud creada',
                'detalle' => 'La llave privada y el CSR de «' . $v['commonName'] . '» se guardaron en el servidor.',
                'csr' => $csrPem];
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
<title>Llavero – Solicitud de certificado (CSR)</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root {
    --fondo: #DDE3EC; --panel: #1C2740; --panel-texto: #E9EDF5; --tarjeta: #FFFFFF;
    --tinta: #1B2433; --suave: #5B6577; --borde: #CBD3E0;
    --laton: #C58B2A; --laton-osc: #9A6A17;
    --ok: #2B7A57; --ok-fondo: #E4F3EB; --error: #B03A2A; --error-fondo: #FBE9E5;
  }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
    background: var(--fondo); color: var(--tinta);
    font-family: 'Manrope', system-ui, 'Segoe UI', sans-serif;
  }
  .caja {
    width: 100%; max-width: 1020px; display: grid; grid-template-columns: 5fr 7fr;
    background: var(--tarjeta); border-radius: 20px; overflow: hidden;
    box-shadow: 0 30px 60px -25px rgba(28,39,64,.45);
  }
  .lado {
    background: var(--panel); color: var(--panel-texto); padding: 48px 40px;
    display: flex; flex-direction: column; justify-content: space-between; gap: 32px;
  }
  .lado h1 { font-size: 2rem; font-weight: 800; line-height: 1.15; margin: 0 0 12px; letter-spacing: -.02em; }
  .lado p  { margin: 0; color: #B6C0D4; line-height: 1.6; max-width: 34ch; }
  .sello   { width: 100%; max-width: 220px; }
  .form { padding: 44px; }
  .form h2 { margin: 0 0 6px; font-size: 1.5rem; font-weight: 800; }
  .form > p { margin: 0 0 24px; color: var(--suave); }
  label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: 6px; }
  .campo { margin-bottom: 16px; }
  .fila { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  input[type=text], input[type=email] {
    width: 100%; padding: 12px 14px; font: inherit; color: var(--tinta);
    background: #F6F8FB; border: 1.5px solid var(--borde); border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
  }
  input:focus-visible, button:focus-visible, a.btn:focus-visible {
    outline: none; border-color: var(--laton); box-shadow: 0 0 0 3px rgba(197,139,42,.3);
  }
  small { color: var(--suave); font-size: .8rem; }
  button {
    width: 100%; margin-top: 6px; font: inherit; font-weight: 700; padding: 14px; border-radius: 10px;
    cursor: pointer; border: 0; background: var(--laton); color: #1F1608; transition: background .15s, transform .1s;
  }
  button:hover { background: #D39A36; }
  button:active { transform: translateY(1px); }
  .aviso { margin-top: 22px; padding: 14px 16px; border-radius: 10px; border-left: 5px solid; }
  .aviso strong { display: block; margin-bottom: 2px; }
  .aviso.ok    { background: var(--ok-fondo);    border-color: var(--ok);    color: #174A34; }
  .aviso.error { background: var(--error-fondo); border-color: var(--error); color: #6E2217; }
  details { margin-top: 14px; border: 1.5px solid var(--borde); border-radius: 10px; padding: 10px 14px; }
  summary { cursor: pointer; font-weight: 600; font-size: .9rem; }
  pre {
    margin: 10px 0 0; font: .75rem/1.5 'JetBrains Mono', Consolas, monospace;
    white-space: pre-wrap; word-break: break-all; max-height: 190px; overflow: auto; color: var(--suave);
  }
  a.btn { display: inline-block; margin-top: 10px; font-weight: 700; font-size: .9rem; color: var(--laton-osc); }
  @media (max-width: 860px) {
    .caja { grid-template-columns: 1fr; }
    .lado { padding: 28px; flex-direction: row; align-items: center; }
    .sello { max-width: 90px; flex-shrink: 0; }
    .lado p { display: none; }
    .lado h1 { font-size: 1.4rem; margin: 0; }
    .form { padding: 28px 24px; }
  }
  @media (max-width: 520px) { .fila { grid-template-columns: 1fr; gap: 0; } }
  @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
</style>
</head>
<body>
<main class="caja">

  <aside class="lado">
    <div>
      <h1>Pide tu certificado.</h1>
      <p>Se crea una llave privada que se queda en el servidor y una solicitud (CSR) que puedes enviar a una autoridad certificadora.</p>
    </div>
    <svg class="sello" viewBox="0 0 200 200" role="img" aria-label="Ilustración de un certificado con sello">
      <rect x="20" y="30" width="160" height="110" rx="10" fill="none" stroke="#C58B2A" stroke-width="8"/>
      <path d="M45 65 H155 M45 88 H125 M45 111 H100" stroke="#C58B2A" stroke-width="8" stroke-linecap="round"/>
      <circle cx="145" cy="135" r="26" fill="#1C2740" stroke="#C58B2A" stroke-width="8"/>
      <path d="M133 135 l9 9 l17 -19" fill="none" stroke="#C58B2A" stroke-width="8" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
  </aside>

  <section class="form">
    <h2>Nueva solicitud de certificado</h2>
    <p>Solo el nombre común es obligatorio.</p>

    <form method="post" autocomplete="off">
      <div class="campo">
        <label for="commonName">Nombre común</label>
        <input type="text" id="commonName" name="commonName" value="<?= e($v['commonName']) ?>"
               placeholder="ej. MyCRS o midominio.com" required minlength="3" maxlength="64">
      </div>
      <div class="fila">
        <div class="campo">
          <label for="organizationName">Organización</label>
          <input type="text" id="organizationName" name="organizationName" value="<?= e($v['organizationName']) ?>">
        </div>
        <div class="campo">
          <label for="organizationalUnitName">Departamento</label>
          <input type="text" id="organizationalUnitName" name="organizationalUnitName" value="<?= e($v['organizationalUnitName']) ?>">
        </div>
      </div>
      <div class="fila">
        <div class="campo">
          <label for="localityName">Ciudad</label>
          <input type="text" id="localityName" name="localityName" value="<?= e($v['localityName']) ?>">
        </div>
        <div class="campo">
          <label for="stateOrProvinceName">Provincia o estado</label>
          <input type="text" id="stateOrProvinceName" name="stateOrProvinceName" value="<?= e($v['stateOrProvinceName']) ?>">
        </div>
      </div>
      <div class="fila">
        <div class="campo">
          <label for="countryName">País</label>
          <input type="text" id="countryName" name="countryName" value="<?= e($v['countryName']) ?>"
                 placeholder="PA" maxlength="2">
          <small>Código de 2 letras</small>
        </div>
        <div class="campo">
          <label for="emailAddress">Correo</label>
          <input type="email" id="emailAddress" name="emailAddress" value="<?= e($v['emailAddress']) ?>">
        </div>
      </div>
      <button type="submit">Generar llave y CSR</button>
    </form>

    <?php if ($resultado): ?>
      <div class="aviso <?= e($resultado['tipo']) ?>" role="status">
        <strong><?= e($resultado['titulo']) ?></strong>
        <?= e($resultado['detalle']) ?>
      </div>
      <?php if (!empty($resultado['csr'])): ?>
        <details open>
          <summary>Solicitud CSR</summary>
          <pre><?= e($resultado['csr']) ?></pre>
          <a class="btn" download="request.csr"
             href="data:application/x-pem-file;charset=utf-8,<?= rawurlencode($resultado['csr']) ?>">Descargar request.csr</a>
        </details>
      <?php endif; ?>
    <?php endif; ?>
  </section>

</main>
</body>
</html>