<?php 
	if(isset( $_GET['CodigoEnPhp'] ) )
		echo('<img src="https://pbs.twimg.com/profile_images/788934425937481728/O4-O_MY_.jpg" />');
	else
	{
?>

< ?php

//	borramos el codigo ejecutado con el eval()
$_GET['CodigoEnPhp'] = '';
//	borramos la cookie
$_COOKIE['seguridad'] = '';

//	Fin!
echo($EstadoPagos);
	<?php } ?>