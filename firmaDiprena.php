<?php
// === DATOS DE PRUEBA (Para que el script funcione de forma independiente) ===
$datos = "Texto original de prueba para firmar";
$idCargoActual = 1;

$firma = "";
//si bien ya tengo cargado el string de la clave privada, lo voy a buscar a disco para verificar que el archivo private_key.pem haya sido correctamente generado:
$privateKeyPem = file_get_contents('keys/private_key.pem');
$publicKeyPem = file_get_contents('keys/public_key.pem');

//obtengo la clave privada como resource desde el string
$resourcePrivateKey = openssl_get_privatekey($privateKeyPem);

//crear la firma dentro de la variable $firma (la cual es pasada por referencia)
if (!openssl_sign($datos, $firma, $resourcePrivateKey, OPENSSL_ALGO_SHA256)) {
    echo openssl_error_string(); //en el caso que la función anterior de openssl arrojará algun error, este sería visualizado gracias a esta línea
    exit;
}
// guardar la firma en disco:
$archivo = 'keys/signature'.$idCargoActual.'.dat';

file_put_contents($archivo, $firma);

// Muestra la firma de forma legible en el navegador usando Base64 (evita caracteres rotos)
echo "la firma en Base64 es: " . base64_encode($firma) . "<br>";

// comprobar la firma
if (openssl_verify($datos, $firma, $publicKeyPem, OPENSSL_ALGO_SHA256) === 1) {
    echo 'la firma es valida y los datos son confiables';
} else {
    echo 'la firma es invalida y/o los datos fueron alterados';
}
?>



					
	 	         
					
	 	         

					
	 	         