<?php
/**
 * User: Wagner Mengue
 * Date: 01/2018
 * Version: 1.0
 *
 * Arquivo inicial que tem a função de importar as classes usadas e realizar uma chamada
 *
 */
require_once '../../include/config.php';
require_once 'src/Dados.php';
require_once 'lib/Caixa.php';

try {

	$integracao = new Caixa( (array) new Dados() );

	$integracao->realizarRegistro();

	echo json_encode($integracao);

} catch ( Exception $e ) {
	echo json_encode($e);
}
