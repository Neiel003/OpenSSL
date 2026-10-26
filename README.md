<table>
  <tr>
    <td align="left" width="50%">
      <img src="Imagenes/Logo1.png" alt="Logo 1" width="120">
    </td>
    <td align="right" width="50%">
      <img src="Imagenes/Logo2.png" alt="Logo 2" width="120">
    </td>
  </tr>
</table>

<h1 align="center">Implementación y documentación de funciones criptográficas con OpenSSL en PHP</h1>

<p align="center">
  <strong>Universidad Tecnológica de Panamá</strong><br>
  Facultad de Ingeniería Eléctrica<br>
  Asignatura: Desarrollo de Web<br>
  <strong>Profesora:</strong> Ing. Irina Fong<br><br>
  <strong>Integrantes:</strong><br>
  Vasti Legaspi<br>
  Gabriel Herrera<br><br>
  <strong>Fecha de entrega:</strong> 30 de octubre de 2026<br>
  <strong>Período académico:</strong> II semestre de 2026
</p>

---

# Índice

1. [Introducción](#1-introducción)
2. [Objetivos](#2-objetivos)
3. [Descripción del proyecto](#3-descripción-del-proyecto)
4. [Evidencias de ejecución](#4-evidencias-de-ejecución)
5. [Documentación técnica de las funciones](#5-documentación-técnica-de-las-funciones)
   - [5.1. FirmaOtra](#51-firmaotra)
   - [5.2. Firma7](#52-firma7)
   - [5.3. FirmaMensaje](#53-firmamensaje)
   - [5.4. Cifrado con OpenSSL](#54-cifrado-con-openssl)
6. [Investigación sobre Libsodium](#6-investigación-sobre-libsodium)
7. [Conclusiones personales](#7-conclusiones-personales)

---

# 1. Introducción

La criptografía es una herramienta fundamental para proteger la información en los sistemas informáticos, ya que permite implementar mecanismos de confidencialidad, integridad, autenticación y verificación de identidad.

PHP dispone de la extensión OpenSSL, que permite utilizar diferentes mecanismos criptográficos, como la generación de claves, la creación de certificados digitales, el cifrado de información y las operaciones relacionadas con firmas digitales.

En este proyecto se trabaja con funciones criptográficas implementadas en PHP mediante OpenSSL. Se documenta el propósito de cada función, sus parámetros, los valores que devuelve y el proceso criptográfico que realiza. Asimismo, se incluyen evidencias de ejecución y una investigación sobre la biblioteca Libsodium como alternativa para implementar operaciones criptográficas en PHP.

# 2. Objetivos

## 2.1. Objetivo general

Implementar y documentar funciones criptográficas en PHP utilizando OpenSSL, comprendiendo su funcionamiento y su aplicación en la protección de la información.

## 2.2. Objetivos específicos

- Identificar las funciones criptográficas utilizadas en los scripts del proyecto.
- Explicar el propósito de cada función, sus parámetros y los valores que devuelve.
- Comprender el proceso de generación de claves y certificados digitales.
- Ejecutar los scripts y documentar sus resultados mediante evidencias.
- Investigar las características y aplicaciones de la biblioteca Libsodium en PHP.
- Reconocer la importancia de utilizar mecanismos criptográficos adecuados para proteger la información.

# 3. Descripción del proyecto

El proyecto consiste en implementar y documentar funciones criptográficas mediante PHP y OpenSSL. Para ello, se analizan los scripts proporcionados, se identifica la finalidad de las operaciones criptográficas y se explica el funcionamiento de las funciones utilizadas.

Entre las operaciones que se documentan se encuentran la generación de claves, la creación de solicitudes de firma de certificados, la generación y exportación de certificados digitales y las operaciones de firma y cifrado que correspondan a los scripts del proyecto.

También se investigará Libsodium, una biblioteca criptográfica que ofrece funciones para realizar operaciones como el cifrado autenticado y el procesamiento seguro de contraseñas.

## 3.1. Tecnologías utilizadas

- **PHP:** lenguaje de programación utilizado para implementar los scripts.
- **OpenSSL:** extensión y biblioteca criptográfica utilizada para realizar las operaciones correspondientes.
- **Libsodium:** biblioteca criptográfica investigada como parte del proyecto.
- **GitHub:** plataforma utilizada para almacenar y documentar el proyecto.

# 4. Evidencias de ejecución

En esta sección se deben incorporar las capturas de pantalla que demuestren la ejecución de los scripts y los resultados obtenidos.

Cada evidencia debe corresponder a una ejecución real del proyecto y permitir identificar qué función se está probando y cuál fue su resultado.

## 4.1. Evidencia de FirmaOtra

**Descripción:** En este apartado se colocará la captura de pantalla de la ejecución del script `FirmaOtra` y del resultado obtenido al generar el certificado.

**Captura de pantalla:**

<!-- Insertar aquí la captura real de ejecución de FirmaOtra. -->

## 4.2. Evidencia de Firma7

**Descripción:** En este apartado se colocará la captura de pantalla de la ejecución del script `Firma7` y del resultado obtenido.

**Captura de pantalla:**

<!-- Insertar aquí la captura real de ejecución de Firma7. -->

## 4.3. Evidencia de FirmaMensaje

**Descripción:** En este apartado se colocará la captura de pantalla de la ejecución del script `FirmaMensaje` y del resultado obtenido.

**Captura de pantalla:**

<!-- Insertar aquí la captura real de ejecución de FirmaMensaje. -->

## 4.4. Evidencia de cifrado con OpenSSL

**Descripción:** En este apartado se colocará la captura de pantalla de la ejecución del script de cifrado y del resultado obtenido.

**Captura de pantalla:**

<!-- Insertar aquí la captura real de ejecución del cifrado con OpenSSL. -->

# 5. Documentación técnica de las funciones

Esta sección describe las funciones criptográficas utilizadas en los scripts del proyecto. Para cada una se debe indicar su nombre, propósito, parámetros, valor de retorno y proceso criptográfico.

## 5.1. FirmaOtra

### 5.1.1. Propósito

El script `FirmaOtra` utiliza funciones de OpenSSL en PHP para generar un par de claves, crear una solicitud de firma de certificado (CSR) y generar un certificado digital autofirmado. Finalmente, exporta el certificado y guarda los archivos correspondientes.

### 5.1.2. Configuración inicial

El script define dos constantes:

```php
define("OPEN_SSL_CONF_PATH", 'C:/wamp64/bin/apache/apache2.4.65/conf/openssl.cnf');

define("OPEN_SSL_CERT_DAYS_VALID", 365);
```

- `OPEN_SSL_CONF_PATH`: especifica la ruta del archivo de configuración de OpenSSL.
- `OPEN_SSL_CERT_DAYS_VALID`: establece el período de validez del certificado en 365 días.

La ruta del archivo de configuración corresponde al entorno en el que se desarrolló el script. Si se ejecuta en otro equipo, puede ser necesario modificarla.

### 5.1.3. Configuración de OpenSSL

Se utiliza un arreglo para establecer los parámetros de generación de las claves:

```php
$configArgs = [
    "config" => OPEN_SSL_CONF_PATH,
    "private_key_bits" => 2048,
    "private_key_type" => OPENSSL_KEYTYPE_RSA
];
```

Los parámetros utilizados son:

| Parámetro | Descripción |
|---|---|
| `config` | Ruta del archivo de configuración de OpenSSL. |
| `private_key_bits` | Tamaño de la clave en bits; en este caso, 2048. |
| `private_key_type` | Tipo de clave que se generará; en este caso, RSA. |

### 5.1.4. Información del certificado

El arreglo `$dn` contiene los datos que se incorporan a la solicitud de certificado:

```php
$dn = [
    "countryName" => "PA",
    "localityName" => "PA-8",
    "organizationName" => "MEF",
    "commonName" => "Irina Fong",
    "emailAddress" => "dreamsweb7@gmail.com"
];
```

| Campo | Valor | Descripción |
|---|---|---|
| `countryName` | `PA` | Código del país. |
| `localityName` | `PA-8` | Información de localidad configurada en el script. |
| `organizationName` | `MEF` | Nombre de la organización. |
| `commonName` | `Irina Fong` | Nombre común asociado al certificado. |
| `emailAddress` | `dreamsweb7@gmail.com` | Dirección de correo electrónico. |

Estos valores corresponden a la configuración del script y no deben interpretarse, por sí solos, como una verificación de la identidad de una persona u organización.

### 5.1.5. Funciones utilizadas

#### `openssl_pkey_new()`

**Propósito:** Generar un nuevo par de claves criptográficas.

**Parámetros:**

- `$configArgs`: arreglo con la configuración de OpenSSL y los parámetros de generación de claves.

**Valor de retorno:** Un recurso u objeto que representa la clave privada generada, dependiendo de la versión de PHP, o `false` si ocurre un error.

**Proceso criptográfico:** Se genera una clave privada junto con su clave pública correspondiente. La clave pública puede compartirse; la clave privada debe mantenerse protegida.

Ejemplo de uso:

```php
$resourceNewKeyPair = openssl_pkey_new($configArgs);
```

#### `openssl_pkey_get_details()`

**Propósito:** Obtener información detallada de una clave.

**Parámetros:**

- `$resourceNewKeyPair`: clave generada anteriormente.

**Valor de retorno:** Un arreglo con información de la clave, incluida su clave pública, o `false` si ocurre un error.

**Proceso criptográfico:** Permite consultar las características de la clave y obtener la representación de la clave pública.

Ejemplo de uso:

```php
$details = openssl_pkey_get_details($resourceNewKeyPair);
$publicKey = $details['key'];
```

#### `openssl_csr_new()`

**Propósito:** Crear una solicitud de firma de certificado (CSR).

**Parámetros:**

- `$dn`: información que se incorporará a la solicitud.
- `$resourceNewKeyPair`: clave privada utilizada para generar la solicitud.
- `$configArgs`: configuración de OpenSSL.

**Valor de retorno:** Una solicitud de certificado o `false` si ocurre un error.

**Proceso criptográfico:** Se crea una solicitud que contiene los datos configurados y la información de la clave pública correspondiente. Una CSR puede presentarse a una autoridad certificadora para solicitar un certificado.

Ejemplo de uso:

```php
$csr = openssl_csr_new(
    $dn,
    $resourceNewKeyPair,
    $configArgs
);
```

#### `openssl_csr_sign()`

**Propósito:** Generar un certificado a partir de una solicitud de certificado.

**Parámetros:**

- `$csr`: solicitud de certificado previamente creada.
- `null`: indica que no se proporciona un certificado emisor.
- `$resourceNewKeyPair`: clave privada utilizada para firmar el certificado.
- `OPEN_SSL_CERT_DAYS_VALID`: período de validez en días.
- `$configArgs`: configuración de OpenSSL.

**Valor de retorno:** Un certificado X.509 o `false` si ocurre un error.

**Proceso criptográfico:** En este script se genera un certificado autofirmado, ya que no se proporciona un certificado emisor. Esto permite crear un certificado para pruebas, aunque no implica que una entidad externa haya verificado la identidad indicada.

Ejemplo de uso:

```php
$sscert = openssl_csr_sign(
    $csr,
    null,
    $resourceNewKeyPair,
    OPEN_SSL_CERT_DAYS_VALID,
    $configArgs
);
```

#### `openssl_x509_export()`

**Propósito:** Exportar un certificado X.509 a una representación textual.

**Parámetros:**

- `$sscert`: certificado que se desea exportar.
- `$certout`: variable en la que se almacenará el certificado exportado.

**Valor de retorno:** `true` si la exportación se realiza correctamente o `false` si ocurre un error.

**Proceso criptográfico:** Convierte el certificado a una representación que puede guardarse o mostrarse.

Ejemplo de uso:

```php
openssl_x509_export($sscert, $certout);

echo $certout;
```

#### `file_put_contents()`

**Propósito:** Guardar información en un archivo.

**Parámetros:**

- Ruta del archivo de destino.
- Contenido que se escribirá en el archivo.

**Valor de retorno:** Número de bytes escritos o `false` si ocurre un error.

**Proceso:** Esta función no realiza una operación criptográfica. Se utiliza para almacenar en archivos el certificado exportado y la clave privada exportada.

En este proyecto se utilizan las rutas:

- `keysCert/certout.csr`
- `keysCert/privkey.pem`

**Nota:** La extensión `.csr` suele utilizarse para solicitudes de firma de certificado. Si el contenido guardado corresponde al certificado generado por `openssl_x509_export()`, sería más claro utilizar una extensión como `.crt` o `.pem`. La extensión no cambia el contenido del archivo.

**Importante:** La clave privada debe mantenerse protegida y no debe publicarse en el repositorio.

### 5.1.6. Resumen del proceso

El proceso realizado por el script puede resumirse así:

1. Configurar OpenSSL y los parámetros de generación de claves.
2. Definir los datos que se incorporarán a la solicitud del certificado.
3. Generar el par de claves criptográficas.
4. Obtener los detalles de la clave y su representación pública.
5. Crear la solicitud de firma de certificado.
6. Generar el certificado autofirmado.
7. Exportar el certificado a texto.
8. Guardar los archivos correspondientes.

## 5.2. Firma7

### 5.2.1. Propósito

En esta sección se documentará el funcionamiento del script `Firma7`, de acuerdo con el código utilizado en el proyecto.

### 5.2.2. Funciones utilizadas

| Nombre de la función | Propósito | Parámetros | Valor de retorno |
|---|---|---|---|
| Pendiente de documentar | Se completará a partir del código de `Firma7`. | Pendiente de revisar el código. | Pendiente de revisar el código. |

### 5.2.3. Proceso criptográfico

Pendiente de completar después de revisar el código y comprobar la ejecución del script.

### 5.2.4. Evidencia

Pendiente de incorporar la captura de pantalla real de la ejecución.

## 5.3. FirmaMensaje

### 5.3.1. Propósito

En esta sección se documentará el funcionamiento del script `FirmaMensaje`, de acuerdo con el código utilizado en el proyecto.

### 5.3.2. Funciones utilizadas

| Nombre de la función | Propósito | Parámetros | Valor de retorno |
|---|---|---|---|
| Pendiente de documentar | Se completará a partir del código de `FirmaMensaje`. | Pendiente de revisar el código. | Pendiente de revisar el código. |

### 5.3.3. Proceso criptográfico

Pendiente de completar después de revisar el código y comprobar la ejecución del script.

### 5.3.4. Evidencia

Pendiente de incorporar la captura de pantalla real de la ejecución.

## 5.4. Cifrado con OpenSSL

### 5.4.1. Propósito

Esta sección documentará el script de cifrado implementado con OpenSSL en PHP, explicando cómo se procesa la información y qué funciones criptográficas se utilizan.

### 5.4.2. Funciones utilizadas

| Nombre de la función | Propósito | Parámetros | Valor de retorno |
|---|---|---|---|
| Pendiente de identificar | Se completará a partir del código del script de cifrado. | Pendiente de revisar el código. | Pendiente de revisar el código. |

### 5.4.3. Proceso criptográfico

Pendiente de completar a partir del código real del script, identificando el algoritmo utilizado, los parámetros necesarios y el resultado de la operación.

### 5.4.4. Evidencia

Pendiente de incorporar la captura de pantalla real de la ejecución.

# 6. Investigación sobre Libsodium

## 6.1. ¿Qué es Libsodium?

Libsodium es una biblioteca criptográfica que ofrece funciones para implementar operaciones de seguridad, como el cifrado autenticado, la generación de claves y el procesamiento de contraseñas.

En PHP, estas funciones pueden utilizarse mediante la extensión Sodium, disponible de forma integrada a partir de PHP 7.2.

## 6.2. Características principales

Entre las operaciones que ofrece Libsodium se encuentran:

- Cifrado y descifrado de información.
- Cifrado autenticado, que permite proteger la confidencialidad y comprobar la integridad de los datos.
- Funciones para derivar claves a partir de contraseñas.
- Operaciones criptográficas con claves públicas y privadas.

## 6.3. Función `sodium_crypto_secretbox()`

**Propósito:** Cifrar un mensaje utilizando una clave secreta y un nonce.

Esta función permite cifrar información y generar una etiqueta de autenticación asociada al mensaje cifrado.

Sus argumentos principales son:

- El mensaje que se desea cifrar.
- Un nonce, que debe cumplir los requisitos de la función y no reutilizarse con la misma clave.
- La clave secreta utilizada para el cifrado.

**Valor de retorno:** Una cadena binaria que contiene el mensaje cifrado y la información de autenticación.

## 6.4. Función `sodium_crypto_secretbox_open()`

**Propósito:** Descifrar y verificar un mensaje protegido mediante `sodium_crypto_secretbox()`.

Sus argumentos principales son:

- El mensaje cifrado.
- El mismo nonce utilizado durante el cifrado.
- La misma clave secreta.

**Valor de retorno:** El mensaje original si la autenticación es correcta, o `false` si la verificación falla.

## 6.5. Ejemplo ejecutable en PHP

El siguiente ejemplo muestra cómo cifrar un mensaje y después recuperarlo utilizando Libsodium.

```php
<?php

$mensaje = "Mensaje de prueba para Libsodium";

// Generar una clave secreta segura.
$clave = sodium_crypto_secretbox_keygen();

// Generar un nonce adecuado para la función.
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

// Cifrar el mensaje.
$mensajeCifrado = sodium_crypto_secretbox(
    $mensaje,
    $nonce,
    $clave
);

echo "Mensaje cifrado correctamente." . PHP_EOL;

// Descifrar y verificar el mensaje.
$mensajeDescifrado = sodium_crypto_secretbox_open(
    $mensajeCifrado,
    $nonce,
    $clave
);

if ($mensajeDescifrado === false) {
    echo "No fue posible verificar o descifrar el mensaje." . PHP_EOL;
} else {
    echo "Mensaje original: " . $mensajeDescifrado . PHP_EOL;
}

// Liberar la memoria de la clave cuando ya no se necesite.
sodium_memzero($clave);

?>
```

### 6.5.1. Explicación del ejemplo

1. Se define el mensaje que se desea proteger.
2. Se genera una clave secreta mediante `sodium_crypto_secretbox_keygen()`.
3. Se genera un nonce con el tamaño requerido por la función.
4. Se cifra el mensaje con `sodium_crypto_secretbox()`.
5. Se utiliza `sodium_crypto_secretbox_open()` para verificar y recuperar el mensaje.
6. Se comprueba si la operación fue exitosa.
7. Se utiliza `sodium_memzero()` para borrar la clave de la memoria cuando ya no se necesita.

**Consideración de seguridad:** Para recuperar el mensaje, es necesario conservar de forma segura la clave y el nonce utilizados. La clave no debe publicarse ni compartirse junto con el mensaje cifrado.

## 6.6. Diferencias generales entre OpenSSL y Libsodium

| Aspecto | OpenSSL | Libsodium |
|---|---|---|
| Uso | Ofrece distintas operaciones criptográficas, certificados y herramientas de clave pública. | Ofrece funciones criptográficas con interfaces orientadas a operaciones específicas y seguras. |
| Certificados X.509 | Permite trabajar con certificados y solicitudes de certificados. | No está orientada a sustituir las funciones de gestión de certificados X.509 de OpenSSL. |
| Cifrado | Permite utilizar distintos algoritmos y configuraciones. | Ofrece funciones específicas para cifrado autenticado, como `sodium_crypto_secretbox()`. |
| Contraseñas | Dispone de herramientas criptográficas, según las funciones utilizadas. | Incluye funciones especializadas para derivar claves a partir de contraseñas. |

# 7. Conclusiones personales

## 7.1. Conclusión de Vasti Legaspi

Pendiente de completar con la reflexión personal sobre los conocimientos adquiridos durante la implementación y documentación de las funciones criptográficas.

## 7.2. Conclusión de Gabriel Herrera

Pendiente de completar con la reflexión personal sobre los conocimientos adquiridos durante el desarrollo del proyecto.

---

**Repositorio del proyecto:** [SeguridadOpenSSL](https://github.com/Salomon2514/SeguridadOpenSSL.git)
