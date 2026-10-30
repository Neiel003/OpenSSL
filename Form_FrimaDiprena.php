<?php
/**
 * Form firma Diprena – firma y verifica con las llaves ya existentes en keys/.
 * Colócalo junto a firmaDiprena.php (ej. C:\wamp64\www\SeguridadOpenSSL\)
 */

$dirKeys = __DIR__ . '/keys';
$resultado = null; // ['tipo' => ok|error, 'titulo' => , 'detalle' => , 'firma' => ]
$datos         = $_POST['datos'] ?? 'Texto original de prueba para firmar';
$idCargoActual = trim($_POST['idCargo'] ?? '1');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $fPriv  = "$dirKeys/private_key.pem";
    $fPub   = "$dirKeys/public_key.pem";

    if (!ctype_digit($idCargoActual)) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Cargo no válido',
            'detalle' => 'El id del cargo debe ser un número.'];
    } elseif (trim($datos) === '') {
        $resultado = ['tipo' => 'error', 'titulo' => 'Faltan los datos',
            'detalle' => 'Escribe el texto que quieres firmar o verificar.'];
    } elseif (!file_exists($fPriv) || !file_exists($fPub)) {
        $resultado = ['tipo' => 'error', 'titulo' => 'No hay llaves',
            'detalle' => 'No se encontraron keys/private_key.pem y keys/public_key.pem.'];
    } else {
        $archivo = "$dirKeys/signature{$idCargoActual}.dat";
        $publicKeyPem = file_get_contents($fPub);

        if ($accion === 'firmar') {
            $resourcePrivateKey = openssl_get_privatekey(file_get_contents($fPriv));
            if (!$resourcePrivateKey) {
                $resultado = ['tipo' => 'error', 'titulo' => 'Llave privada inválida',
                    'detalle' => openssl_error_string()];
            } elseif (!openssl_sign($datos, $firma, $resourcePrivateKey, OPENSSL_ALGO_SHA256)) {
                $resultado = ['tipo' => 'error', 'titulo' => 'No se pudo firmar',
                    'detalle' => openssl_error_string()];
            } else {
                file_put_contents($archivo, $firma);
                $valida = openssl_verify($datos, $firma, $publicKeyPem, OPENSSL_ALGO_SHA256) === 1;
                $resultado = [
                    'tipo'    => $valida ? 'ok' : 'error',
                    'titulo'  => $valida ? 'Mensaje firmado' : 'Firma inválida',
                    'detalle' => $valida
                        ? 'La firma es válida y los datos son confiables. Se guardó en signature' . $idCargoActual . '.dat.'
                        : 'La firma es inválida y/o los datos fueron alterados.',
                    'firma'   => chunk_split(base64_encode($firma), 64, "\n"),
                ];
            }
        } elseif ($accion === 'verificar') {
            if (!file_exists($archivo)) {
                $resultado = ['tipo' => 'error', 'titulo' => 'Sin firma guardada',
                    'detalle' => 'No existe signature' . $idCargoActual . '.dat. Firma primero.'];
            } else {
                $firma = file_get_contents($archivo);
                $ok = openssl_verify($datos, $firma, $publicKeyPem, OPENSSL_ALGO_SHA256) === 1;
                $resultado = $ok
                    ? ['tipo' => 'ok', 'titulo' => 'Firma válida',
                       'detalle' => 'La firma es válida y los datos son confiables.']
                    : ['tipo' => 'error', 'titulo' => 'Firma inválida',
                       'detalle' => 'La firma es inválida y/o los datos fueron alterados.'];
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
<title>Firma Diprena – Firma digital RSA</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root {
    --fondo: #DDE3EC; --panel: #1C2740; --panel-texto: #E9EDF5; --tarjeta: #FFFFFF;
    --tinta: #1B2433; --suave: #5B6577; --borde: #CBD3E0; --laton: #C58B2A; --laton-osc: #9A6A17;
    --ok: #2B7A57; --ok-fondo: #E4F3EB; --error: #B03A2A; --error-fondo: #FBE9E5;
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
  .botones { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 22px; }
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
      <h1>Firma Diprena.</h1>
      <p>La llave privada firma los datos del cargo y la pública comprueba que nadie los haya cambiado.</p>
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
    <h2>Firma de cargo</h2>
    <p>Indica el id del cargo y los datos que quieres firmar o comprobar.</p>

    <form method="post" autocomplete="off">
      <div class="campo">
        <label for="idCargo">Id del cargo</label>
        <input type="text" id="idCargo" name="idCargo" value="<?= e($idCargoActual) ?>"
               inputmode="numeric" pattern="[0-9]+" required>
      </div>
      <div class="campo">
        <label for="datos">Datos a firmar</label>
        <textarea id="datos" name="datos" required><?= e($datos) ?></textarea>
      </div>
      <div class="botones">
        <button class="primario"   type="submit" name="accion" value="firmar">Firmar datos</button>
        <button class="secundario" type="submit" name="accion" value="verificar">Verificar firma</button>
      </div>
    </form>

    <?php if ($resultado): ?>
      <div class="aviso <?= e($resultado['tipo']) ?>" role="status">
        <strong><?= e($resultado['titulo']) ?></strong>
        <?= e($resultado['detalle']) ?>
      </div>
      <?php if (!empty($resultado['firma'])): ?>
        <details open>
          <summary>Firma (Base64)</summary>
          <pre><?= e($resultado['firma']) ?></pre>
        </details>
      <?php endif; ?>
    <?php endif; ?>
  </section>

</main>
</body>
</html>