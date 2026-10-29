<?php
/**
 * Generador de certificado X.509 autofirmado.
 * Colócalo en: C:\wamp64\www\llaves\cert.php  →  http://localhost/llaves/cert.php
 */

// ---------- Configuración de OpenSSL ----------
$candidatos = array_filter([
    getenv('OPENSSL_CONF'),
    'C:\wamp64\bin\apache\apache2.4.65\conf\openssl.cnf',
    'C:\openssl\bin\cnf\openssl.cnf',
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

$dirBase = __DIR__ . '/keysCert';
if (!is_dir($dirBase)) {
    mkdir($dirBase, 0775, true);
    file_put_contents($dirBase . '/.htaccess', "Require all denied\n"); // protege las llaves
}

// ---------- Lógica ----------
$campos = ['commonName', 'organizationName', 'localityName', 'countryName', 'emailAddress', 'dias'];
$v = [];
foreach ($campos as $c) $v[$c] = trim($_POST[$c] ?? '');
if ($v['dias'] === '') $v['dias'] = '365';

$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!preg_match('/^[\p{L}\p{N} ._-]{3,64}$/u', $v['commonName'])) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Nombre común no válido',
            'detalle' => 'Usa de 3 a 64 caracteres: letras, números, espacios, punto, guion o guion bajo.'];
    } elseif (!preg_match('/^[A-Za-z]{2}$/', $v['countryName'])) {
        $resultado = ['tipo' => 'error', 'titulo' => 'País no válido',
            'detalle' => 'Escribe el código de 2 letras, por ejemplo PA.'];
    } elseif ($v['emailAddress'] !== '' && !filter_var($v['emailAddress'], FILTER_VALIDATE_EMAIL)) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Correo no válido',
            'detalle' => 'Revisa el formato del correo electrónico.'];
    } elseif (!ctype_digit($v['dias']) || (int)$v['dias'] < 1 || (int)$v['dias'] > 3650) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Vigencia no válida',
            'detalle' => 'Indica un número de días entre 1 y 3650.'];
    } else {
        $dn = array_filter([
            'countryName'      => strtoupper($v['countryName']),
            'localityName'     => $v['localityName'],
            'organizationName' => $v['organizationName'],
            'commonName'       => $v['commonName'],
            'emailAddress'     => $v['emailAddress'],
        ], fn($x) => $x !== '');

        $pkey   = openssl_pkey_new($config);
        $csr    = $pkey ? openssl_csr_new($dn, $pkey, $config) : false;
        $cert   = $csr  ? openssl_csr_sign($csr, null, $pkey, (int)$v['dias'], $config) : false;

        if (!$pkey || !$csr || !$cert
            || !openssl_x509_export($cert, $certPem)
            || !openssl_pkey_export($pkey, $privPem, null, $config)) {
            $resultado = ['tipo' => 'error', 'titulo' => 'No se pudo crear el certificado',
                'detalle' => 'Revisa la ruta de openssl.cnf. ' . openssl_error_string()];
        } else {
            // Carpeta por nombre (sin acentos ni espacios)
            $slug = strtolower(preg_replace('/[^A-Za-z0-9_-]+/', '_',
                    @iconv('UTF-8', 'ASCII//TRANSLIT', $v['commonName']) ?: $v['commonName']));
            $slug = trim($slug, '_') ?: 'certificado';
            $dir  = "$dirBase/$slug";
            if (!is_dir($dir)) mkdir($dir, 0775, true);
            file_put_contents("$dir/certout.crt", $certPem);
            file_put_contents("$dir/privkey.pem", $privPem);

            $info = openssl_x509_parse($cert);
            $resultado = [
                'tipo'    => 'ok',
                'titulo'  => 'Certificado creado',
                'detalle' => 'El certificado y su llave privada se guardaron en keysCert/' . $slug . '/.',
                'pem'     => $certPem,
                'datos'   => [
                    'Titular'       => $v['commonName'],
                    'Válido desde'  => date('d/m/Y', $info['validFrom_time_t']),
                    'Válido hasta'  => date('d/m/Y', $info['validTo_time_t']),
                    'Algoritmo'     => $info['signatureTypeSN'] ?? 'sha256WithRSAEncryption',
                    'Huella SHA-256'=> chunk_split(strtoupper(openssl_x509_fingerprint($cert, 'sha256')), 2, ':'),
                ],
            ];
            $resultado['datos']['Huella SHA-256'] = rtrim($resultado['datos']['Huella SHA-256'], ':');
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
<title>Certificado X.509 autofirmado</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root {
    --fondo: #E3ECE8;       /* salvia clara */
    --panel: #0F3D3E;       /* verde azulado profundo */
    --panel-texto: #E6F2EE;
    --tarjeta: #FFFFFF;
    --tinta: #15302F;
    --suave: #587270;
    --borde: #C5D6D1;
    --coral: #F28C5B;
    --coral-osc: #B4481F;
    --ok: #1F7A56; --ok-fondo: #E1F3EA;
    --error: #B03A2A; --error-fondo: #FBE9E5;
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
    box-shadow: 0 30px 60px -25px rgba(15,61,62,.5);
  }
  .lado {
    background: var(--panel); color: var(--panel-texto); padding: 48px 40px;
    display: flex; flex-direction: column; justify-content: space-between; gap: 32px;
  }
  .lado h1 { font-size: 2rem; font-weight: 800; line-height: 1.15; margin: 0 0 12px; letter-spacing: -.02em; }
  .lado p  { margin: 0; color: #A9C7C1; line-height: 1.6; max-width: 34ch; }
  .sello   { width: 100%; max-width: 220px; }
  .form { padding: 44px; }
  .form h2 { margin: 0 0 6px; font-size: 1.5rem; font-weight: 800; }
  .form > p { margin: 0 0 24px; color: var(--suave); }
  label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: 6px; }
  .campo { margin-bottom: 16px; }
  .fila { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
  input[type=text], input[type=email], input[type=number] {
    width: 100%; padding: 12px 14px; font: inherit; color: var(--tinta);
    background: #F4F8F6; border: 1.5px solid var(--borde); border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
  }
  input:focus-visible, button:focus-visible, a.btn:focus-visible {
    outline: none; border-color: var(--coral); box-shadow: 0 0 0 3px rgba(242,140,91,.35);
  }
  small { color: var(--suave); font-size: .8rem; }
  button {
    width: 100%; margin-top: 6px; font: inherit; font-weight: 700; padding: 14px; border-radius: 10px;
    cursor: pointer; border: 0; background: var(--coral); color: #2B1006; transition: background .15s, transform .1s;
  }
  button:hover { background: #F69B70; }
  button:active { transform: translateY(1px); }
  .aviso { margin-top: 22px; padding: 14px 16px; border-radius: 10px; border-left: 5px solid; }
  .aviso strong { display: block; margin-bottom: 2px; }
  .aviso.ok    { background: var(--ok-fondo);    border-color: var(--ok);    color: #11452F; }
  .aviso.error { background: var(--error-fondo); border-color: var(--error); color: #6E2217; }
  dl { margin: 16px 0 0; display: grid; grid-template-columns: max-content 1fr; gap: 8px 18px; font-size: .9rem; }
  dt { color: var(--suave); font-weight: 600; }
  dd { margin: 0; word-break: break-all; }
  dd.mono { font: .75rem/1.5 'JetBrains Mono', Consolas, monospace; }
  details { margin-top: 16px; border: 1.5px solid var(--borde); border-radius: 10px; padding: 10px 14px; }
  summary { cursor: pointer; font-weight: 600; font-size: .9rem; }
  pre {
    margin: 10px 0 0; font: .75rem/1.5 'JetBrains Mono', Consolas, monospace;
    white-space: pre-wrap; word-break: break-all; max-height: 190px; overflow: auto; color: var(--suave);
  }
  a.btn { display: inline-block; margin-top: 10px; font-weight: 700; font-size: .9rem; color: var(--coral-osc); }
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
      <h1>Emite tu certificado.</h1>
      <p>Crea un certificado X.509 autofirmado con su llave privada. Sirve para pruebas con HTTPS y para firmar y verificar datos.</p>
    </div>
    <svg class="sello" viewBox="0 0 200 200" role="img" aria-label="Ilustración de un sello de certificado">
      <circle cx="100" cy="85" r="58" fill="none" stroke="#F28C5B" stroke-width="8"/>
      <circle cx="100" cy="85" r="36" fill="none" stroke="#F28C5B" stroke-width="5" stroke-dasharray="3 9" stroke-linecap="round"/>
      <path d="M84 85 l12 12 l22 -25" fill="none" stroke="#F28C5B" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M62 132 L48 188 L100 166 L152 188 L138 132" fill="none" stroke="#F28C5B" stroke-width="8" stroke-linejoin="round" stroke-linecap="round"/>
    </svg>
  </aside>

  <section class="form">
    <h2>Nuevo certificado</h2>
    <p>Completa los datos del titular. Nombre común y país son obligatorios.</p>

    <form method="post" autocomplete="off">
      <div class="campo">
        <label for="commonName">Nombre común</label>
        <input type="text" id="commonName" name="commonName" value="<?= e($v['commonName']) ?>"
               placeholder="ej. Irina Fong o midominio.com" required minlength="3" maxlength="64">
      </div>
      <div class="fila">
        <div class="campo">
          <label for="organizationName">Organización</label>
          <input type="text" id="organizationName" name="organizationName" value="<?= e($v['organizationName']) ?>" placeholder="ej. MEF">
        </div>
        <div class="campo">
          <label for="localityName">Localidad</label>
          <input type="text" id="localityName" name="localityName" value="<?= e($v['localityName']) ?>" placeholder="ej. PA-8">
        </div>
      </div>
      <div class="fila">
        <div class="campo">
          <label for="countryName">País</label>
          <input type="text" id="countryName" name="countryName" value="<?= e($v['countryName'] ?: 'PA') ?>"
                 maxlength="2" required>
          <small>Código de 2 letras</small>
        </div>
        <div class="campo">
          <label for="dias">Vigencia (días)</label>
          <input type="number" id="dias" name="dias" value="<?= e($v['dias']) ?>" min="1" max="3650" required>
        </div>
      </div>
      <div class="campo">
        <label for="emailAddress">Correo</label>
        <input type="email" id="emailAddress" name="emailAddress" value="<?= e($v['emailAddress']) ?>">
      </div>
      <button type="submit">Generar certificado</button>
    </form>

    <?php if ($resultado): ?>
      <div class="aviso <?= e($resultado['tipo']) ?>" role="status">
        <strong><?= e($resultado['titulo']) ?></strong>
        <?= e($resultado['detalle']) ?>
      </div>
      <?php if (!empty($resultado['datos'])): ?>
        <dl>
          <?php foreach ($resultado['datos'] as $k => $val): ?>
            <dt><?= e($k) ?></dt>
            <dd<?= $k === 'Huella SHA-256' ? ' class="mono"' : '' ?>><?= e($val) ?></dd>
          <?php endforeach; ?>
        </dl>
        <details>
          <summary>Certificado (PEM)</summary>
          <pre><?= e($resultado['pem']) ?></pre>
          <a class="btn" download="certout.crt"
             href="data:application/x-x509-ca-cert;charset=utf-8,<?= rawurlencode($resultado['pem']) ?>">Descargar certout.crt</a>
        </details>
      <?php endif; ?>
    <?php endif; ?>
  </section>

</main>
</body>
</html>