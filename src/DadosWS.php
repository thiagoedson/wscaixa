<?php
/**
 * User: Thiago Edson
 * Date: 05/2018
 * Version: 2.0
 *
 * Classe simplificada para preenchimento dos dados para testes do webservice
 *
 * Durante a implementação do envio dos dados, fique atento à quantidade de caracteres que são encaminhados e
 * ao envio de caracteres especiais que podem ultrapassar o limite conforme encoding.
 * Cuide também a formatação dos campos conforme o manual
 *
 */

namespace DadosWS;

class DadosWS {

	public $urlIntegracao = 'https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo';
	//public $urlIntegracao = 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo';
	public $codigoCedente = ''; //cedente ou beneficiário
	public $codigoCedente2 = ''; //cedente ou beneficiário
	//public $codigoCedente = '5296377'; //cedente ou beneficiário
	public $nossoNumero = '';
	public $dataVencimento = '2018-05-18';
	public $valorNominal = '1';
	public $cnpj = '';
	public $codigoTitulo = '';
	public $dataEmissao = '2018-05-16';

	public $dataJuros = '0000-00-00';
	public $juros = '0.00';

	public $dataMulta = '0000-00-00';
	public $multa = '0.00';

	public $numeroAgencia = '';

	public $mensagem = '';


	/**
	 * Caso o pagador seja uma pessoa fisica CPF
	 **/
	public $infoPagador = array(
		'CPF'      => '',
		'NOME'     => '',
		'ENDERECO' => array(
			'LOGRADOURO' => '',
			'BAIRRO'     => '',
			'CIDADE'     => '',
			'UF'         => '',
			'CEP'        => ''
		)
	);

	/**
	 * Caso o pagador seja uma empresa CNPJ
	 **/

	public $infoPagadorCNPJ = array(
		'CNPJ'         => '',
		'RAZAO_SOCIAL' => '',
		'ENDERECO'     => array(
			'LOGRADOURO' => '',
			'BAIRRO'     => '',
			'CIDADE'     => '',
			'UF'         => '',
			'CEP'        => ''
		)
	);

}