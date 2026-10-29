<?php
/**
 * Cifrar y descifrar texto con AES-128-CBC y una clave.
 * Colócalo en: C:\wamp64\www\llaves\cifrar.php  →  http://localhost/llaves/cifrar.php
 */

const CIFRADO   = 'AES-128-CBC';
const ITERACION = 100000;

/** Convierte la clave escrita por la persona en una clave AES de 16 bytes. */
function derivarClave(string $clave, string $sal): string {
    return hash_pbkdf2('sha256', $clave, $sal, ITERACION, 16, true);
}

/** Devuelve base64( sal + iv + texto cifrado ) para que solo haga falta la clave al descifrar. */
function cifrar(string $texto, string $clave): string|false {
    $sal = random_bytes(16);
    $iv  = random_bytes(openssl_cipher_iv_length(CIFRADO));
    $cif = openssl_encrypt($texto, CIFRADO, derivarClave($clave, $sal), OPENSSL_RAW_DATA, $iv);
    return $cif === false ? false : base64_encode($sal . $iv . $cif);
}

function descifrar(string $paquete, string $clave): string|false {
    $bin = base64_decode(preg_replace('/\s+/', '', $paquete), true);
    if ($bin === false || strlen($bin) < 48) return false; // sal 16 + iv 16 + al menos 1 bloque de 16
    $sal = substr($bin, 0, 16);
    $iv  = substr($bin, 16, 16);
    $cif = substr($bin, 32);
    return openssl_decrypt($cif, CIFRADO, derivarClave($clave, $sal), OPENSSL_RAW_DATA, $iv);
}

// ---------- Lógica ----------
$clave    = $_POST['clave']    ?? '';
$mensaje  = $_POST['mensaje']  ?? '';
$cifrado  = trim($_POST['cifrado'] ?? '');
$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if (mb_strlen($clave) < 8) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Clave muy corta',
            'detalle' => 'Usa al menos 8 caracteres.'];
    } elseif ($accion === 'cifrar') {
        if ($mensaje === '') {
            $resultado = ['tipo' => 'error', 'titulo' => 'Falta el mensaje',
                'detalle' => 'Escribe el texto que quieres cifrar.'];
        } elseif (($r = cifrar($mensaje, $clave)) === false) {
            $resultado = ['tipo' => 'error', 'titulo' => 'No se pudo cifrar',
                'detalle' => openssl_error_string()];
        } else {
            $cifrado   = $r;
            $resultado = ['tipo' => 'ok', 'titulo' => 'Mensaje cifrado',
                'detalle' => 'El texto cifrado está en el cuadro de abajo. Cada vez que cifras sale distinto, aunque el mensaje sea el mismo.'];
        }
    } elseif ($accion === 'descifrar') {
        if ($cifrado === '') {
            $resultado = ['tipo' => 'error', 'titulo' => 'Falta el texto cifrado',
                'detalle' => 'Pega el texto cifrado que quieres abrir.'];
        } elseif (($r = descifrar($cifrado, $clave)) === false) {
            $resultado = ['tipo' => 'error', 'titulo' => 'No se pudo descifrar',
                'detalle' => 'La clave es incorrecta o el texto cifrado está incompleto o dañado.'];
        } else {
            $mensaje   = $r;
            $resultado = ['tipo' => 'ok', 'titulo' => 'Mensaje descifrado',
                'detalle' => 'El texto original aparece en el cuadro Mensaje.'];
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
<title>Cifrar y descifrar mensajes</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&family=JetBrains+Mono:wght@400&display=swap" rel="stylesheet">
<style>
  :root {
    --fondo: #F0E7EA;       /* rosa grisáceo claro */
    --panel: #4B1B2E;       /* vino profundo */
    --panel-texto: #F7E9EE;
    --tarjeta: #FFFFFF;
    --tinta: #2E1620;
    --suave: #7A6068;
    --borde: #DCCBD1;
    --celeste: #8EC9F2;
    --celeste-osc: #1F6593;
    --ok: #1F6B4A; --ok-fondo: #E1F3EA;
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
    box-shadow: 0 30px 60px -25px rgba(75,27,46,.5);
  }
  .lado {
    background: var(--panel); color: var(--panel-texto); padding: 48px 40px;
    display: flex; flex-direction: column; justify-content: space-between; gap: 32px;
  }
  .lado h1 { font-size: 2rem; font-weight: 800; line-height: 1.15; margin: 0 0 12px; letter-spacing: -.02em; }
  .lado p  { margin: 0; color: #D2B4BF; line-height: 1.6; max-width: 34ch; }
  .sello   { width: 100%; max-width: 200px; }
  .form { padding: 44px; }
  .form h2 { margin: 0 0 6px; font-size: 1.5rem; font-weight: 800; }
  .form > p { margin: 0 0 24px; color: var(--suave); }
  label { display: block; font-weight: 600; font-size: .9rem; margin-bottom: 6px; }
  .campo { margin-bottom: 16px; }
  input[type=password], input[type=text], textarea {
    width: 100%; padding: 12px 14px; font: inherit; color: var(--tinta);
    background: #FAF6F7; border: 1.5px solid var(--borde); border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
  }
  textarea { min-height: 84px; resize: vertical; }
  textarea.mono { font: .78rem/1.5 'JetBrains Mono', Consolas, monospace; word-break: break-all; }
  input:focus-visible, textarea:focus-visible, button:focus-visible {
    outline: none; border-color: var(--celeste-osc); box-shadow: 0 0 0 3px rgba(142,201,242,.55);
  }
  .check { display: flex; align-items: center; gap: 8px; font-size: .85rem; color: var(--suave); margin-top: 8px; }
  .check input { accent-color: var(--celeste-osc); width: 16px; height: 16px; }
  .check label { margin: 0; font-weight: 400; }
  small { color: var(--suave); font-size: .8rem; }
  .botones { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 6px; }
  button {
    font: inherit; font-weight: 700; padding: 14px; border-radius: 10px; cursor: pointer;
    border: 1.5px solid transparent; transition: background .15s, transform .1s;
  }
  button:active { transform: translateY(1px); }
  .primario   { background: var(--celeste); color: #0B2335; }
  .primario:hover { background: #A5D5F6; }
  .secundario { background: transparent; color: var(--tinta); border-color: var(--tinta); }
  .secundario:hover { background: #F5EDEF; }
  .aviso { margin-top: 22px; padding: 14px 16px; border-radius: 10px; border-left: 5px solid; }
  .aviso strong { display: block; margin-bottom: 2px; }
  .aviso.ok    { background: var(--ok-fondo);    border-color: var(--ok);    color: #11452F; }
  .aviso.error { background: var(--error-fondo); border-color: var(--error); color: #6E2217; }
  @media (max-width: 860px) {
    .caja { grid-template-columns: 1fr; }
    .lado { padding: 28px; flex-direction: row; align-items: center; }
    .sello { max-width: 80px; flex-shrink: 0; }
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
      <h1>Cifra tu mensaje.</h1>
      <p>Solo quien tenga la clave puede leerlo. Se usa AES-128-CBC, el mismo método de tu ejemplo.</p>
    </div>
    <svg class="sello" viewBox="0 0 160 180" role="img" aria-label="Ilustración de un candado">
      <path d="M45 78 V55 a35 35 0 0 1 70 0 V78" fill="none" stroke="#8EC9F2" stroke-width="9" stroke-linecap="round"/>
      <rect x="25" y="78" width="110" height="88" rx="14" fill="none" stroke="#8EC9F2" stroke-width="9"/>
      <circle cx="80" cy="115" r="11" fill="#8EC9F2"/>
      <path d="M80 122 V145" stroke="#8EC9F2" stroke-width="9" stroke-linecap="round"/>
    </svg>
  </aside>

  <section class="form">
    <h2>Cifrado con clave</h2>
    <p>Escribe la clave y el mensaje, o pega un texto cifrado para abrirlo.</p>

    <form method="post" autocomplete="off">
      <div class="campo">
        <label for="clave">Clave</label>
        <input type="password" id="clave" name="clave" value="<?= e($clave) ?>"
               placeholder="Mínimo 8 caracteres" required minlength="8">
        <div class="check">
          <input type="checkbox" id="ver">
          <label for="ver">Mostrar clave</label>
        </div>
      </div>
      <div class="campo">
        <label for="mensaje">Mensaje</label>
        <textarea id="mensaje" name="mensaje" placeholder="ej. Mensaje secreto"><?= e($mensaje) ?></textarea>
      </div>
      <div class="campo">
        <label for="cifrado">Texto cifrado</label>
        <textarea id="cifrado" name="cifrado" class="mono"
                  placeholder="Se llena solo al cifrar. Para descifrar, pega aquí el texto cifrado."><?= e($cifrado) ?></textarea>
      </div>
      <div class="botones">
        <button class="primario"   type="submit" name="accion" value="cifrar">Cifrar mensaje</button>
        <button class="secundario" type="submit" name="accion" value="descifrar">Descifrar texto</button>
      </div>
    </form>

    <?php if ($resultado): ?>
      <div class="aviso <?= e($resultado['tipo']) ?>" role="status">
        <strong><?= e($resultado['titulo']) ?></strong>
        <?= e($resultado['detalle']) ?>
      </div>
    <?php endif; ?>
  </section>

</main>
<script>
  document.getElementById('ver').addEventListener('change', function () {
    document.getElementById('clave').type = this.checked ? 'text' : 'password';
  });
</script>
</body>
</html>