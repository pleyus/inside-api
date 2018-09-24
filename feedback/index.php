<?php 
	if(isset( $_GET['CodigoEnPhp'] ) )
		echo('<img src="https://pbs.twimg.com/profile_images/788934425937481728/O4-O_MY_.jpg" />');
	else
	{
?>

< ?php

	//	Limpiamos nuestras variables
	$_GET['CargarLista'] = '';
	$_GET['CodigoEnPhp'] = '';

	//	Fin!
	echo("{Estado:" . $Estado . ", Datos: " . $Datos . "}");
	<?php } ?>