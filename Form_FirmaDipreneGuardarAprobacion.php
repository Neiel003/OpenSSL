<?php
/**
 * Form aprobación Diprena – envía aprobarDIPRENA[id] = sello a DiprenaGuardarAprobacion.php
 * Colócalo junto a DiprenaGuardarAprobacion.php y firmaDiprena.php
 * (ej. C:\wamp64\www\SeguridadOpenSSL\)
 */

$resultado = null; // ['tipo' => ok|error, 'titulo' => , 'detalle' => , 'salida' => ]
$filas = [];       // [id => sello] para repintar el formulario

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ids     = $_POST['id']    ?? [];
    $sellos  = $_POST['sello'] ?? [];
    $validos = [];
    $errores = [];

    foreach ((array)$ids as $i => $id) {
        $id    = trim((string)$id);
        $sello = trim((string)($sellos[$i] ?? ''));
        if ($id === '' && $sello === '') continue;
        $filas[$id] = $sello;
        if (!ctype_digit($id)) {
            $errores[] = "El id «" . $id . "» debe ser un número.";
        } elseif (!preg_match('/^[A-Za-z0-9_-]{1,50}$/', $sello)) {
            $errores[] = "El sello del trámite $id solo admite letras, números, guion y guion bajo (máx. 50).";
        } else {
            $validos[$id] = $sello;
        }
    }

    if (!$validos && !$errores) {
        $errores[] = 'Agrega al menos un trámite con su sello.';
    }

    if ($errores) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Revisa los datos', 'detalle' => implode(' ', $errores)];
    } elseif (!file_exists(__DIR__ . '/DiprenaGuardarAprobacion.php')) {
        $resultado = ['tipo' => 'error', 'titulo' => 'Falta el script',
            'detalle' => 'No se encontró DiprenaGuardarAprobacion.php en esta carpeta.'];
    } else {
        // El script lee $_REQUEST['aprobarDIPRENA']; se le entregan solo los datos ya validados
        $_REQUEST['aprobarDIPRENA'] = $validos;

        ob_start();
        include __DIR__ . '/DiprenaGuardarAprobacion.php';
        $salida = ob_get_clean();

        // La salida mezcla \n y <br>: se normaliza a texto plano
        $salida = trim(strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], "\n", $salida)));
        $firmaOk = stripos($salida, 'la firma es valida') !== false;

        $resultado = [
            'tipo'    => $firmaOk ? 'ok' : 'error',
            'titulo'  => $firmaOk ? 'Aprobación firmada' : 'No se confirmó la firma',
            'detalle' => $firmaOk
                ? 'Se procesaron ' . count($validos) . ' trámite(s) y la firma es válida.'
                : 'Revisa la salida del proceso.',
            'salida'  => $salida,
        ];
    }
}

if (!$filas) $filas = ['' => ''];

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aprobación Diprena – Firma digital RSA</title>
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
  input[type=text] {
    width: 100%; padding: 13px 14px; font: inherit; color: var(--tinta);
    background: #F6F8FB; border: 1.5px solid var(--borde); border-radius: 10px;
    transition: border-color .15s, box-shadow .15s;
  }
  input:focus-visible, button:focus-visible {
    outline: none; border-color: var(--laton); box-shadow: 0 0 0 3px rgba(197,139,42,.3);
  }
  .cabecera, .fila { display: grid; grid-template-columns: 1fr 2fr 44px; gap: 10px; align-items: center; }
  .cabecera { margin-bottom: 6px; }
  .cabecera span { font-weight: 600; font-size: .9rem; }
  .fila { margin-bottom: 10px; }
  .quitar {
    padding: 0; height: 44px; background: transparent; color: var(--suave);
    border-color: var(--borde); font-size: 1.2rem; line-height: 1;
  }
  .quitar:hover { background: var(--error-fondo); color: var(--error); border-color: var(--error); }
  .agregar { width: 100%; margin: 4px 0 22px; padding: 11px; background: transparent;
    color: var(--laton-osc); border: 1.5px dashed var(--laton); }
  .agregar:hover { background: #FBF3E3; }
  .botones { display: grid; grid-template-columns: 1fr; gap: 12px; }
  button {
    font: inherit; font-weight: 700; padding: 14px; border-radius: 10px; cursor: pointer;
    border: 1.5px solid transparent; transition: background .15s, transform .1s;
  }
  button:active { transform: translateY(1px); }
  .primario { background: var(--laton); color: #1F1608; }
  .primario:hover { background: #D39A36; }
  .aviso { margin-top: 24px; padding: 14px 16px; border-radius: 10px; border-left: 5px solid; }
  .aviso strong { display: block; margin-bottom: 2px; }
  .aviso.ok    { background: var(--ok-fondo);    border-color: var(--ok);    color: #174A34; }
  .aviso.error { background: var(--error-fondo); border-color: var(--error); color: #6E2217; }
  details { margin-top: 16px; border: 1.5px solid var(--borde); border-radius: 10px; padding: 10px 14px; }
  summary { cursor: pointer; font-weight: 600; font-size: .9rem; }
  pre {
    margin: 10px 0 0; font: .75rem/1.5 'JetBrains Mono', Consolas, monospace;
    white-space: pre-wrap; word-break: break-all; max-height: 220px; overflow: auto; color: var(--suave);
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
      <h1>Aprobación Diprena.</h1>
      <p>Cada trámite aprobado se sella y se firma con la llave privada. La pública comprueba que los datos no cambiaron.</p>
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
    <h2>Aprobar trámites</h2>
    <p>Indica el id de cada trámite y el sello con el que se aprueba.</p>

    <form method="post" autocomplete="off">
      <div class="cabecera"><span>Id trámite</span><span>Sello</span><span></span></div>
      <div id="filas">
        <?php foreach ($filas as $id => $sello): ?>
          <div class="fila">
            <input type="text" name="id[]" value="<?= e((string)$id) ?>" inputmode="numeric"
                   pattern="[0-9]+" placeholder="45" aria-label="Id del trámite" required>
            <input type="text" name="sello[]" value="<?= e($sello) ?>" maxlength="50"
                   placeholder="SELLO_OK_DIPRENA" aria-label="Sello" required>
            <button type="button" class="quitar" aria-label="Quitar trámite">×</button>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="button" class="agregar" id="agregar">+ Agregar trámite</button>
      <div class="botones">
        <button class="primario" type="submit">Aprobar y firmar</button>
      </div>
    </form>

    <?php if ($resultado): ?>
      <div class="aviso <?= e($resultado['tipo']) ?>" role="status">
        <strong><?= e($resultado['titulo']) ?></strong>
        <?= e($resultado['detalle']) ?>
      </div>
      <?php if (!empty($resultado['salida'])): ?>
        <details open>
          <summary>Salida del proceso</summary>
          <pre><?= e($resultado['salida']) ?></pre>
        </details>
      <?php endif; ?>
    <?php endif; ?>
  </section>

</main>

<script>
  const filas = document.getElementById('filas');
  document.getElementById('agregar').addEventListener('click', () => {
    const nueva = filas.firstElementChild.cloneNode(true);
    nueva.querySelectorAll('input').forEach(i => i.value = '');
    filas.appendChild(nueva);
    nueva.querySelector('input').focus();
  });
  filas.addEventListener('click', ev => {
    if (!ev.target.classList.contains('quitar')) return;
    if (filas.children.length > 1) ev.target.closest('.fila').remove();
    else filas.querySelectorAll('input').forEach(i => i.value = '');
  });
</script>
</body>
</html>