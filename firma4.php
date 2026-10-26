<?PHP

$config = array('config'=>'C:\openssl\bin\cnf\openssl.cnf');
$pkey = openssl_pkey_new($config);
$dn = array("commonName" => "MyCRS");
$crs = openssl_csr_new($dn, $pkey, $config);

?>