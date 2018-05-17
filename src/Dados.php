<?php
/**
 * User: Wagner Mengue
 * Date: 01/2018
 * Version: 1.0
 *
 * Classe simplificada para preenchimento dos dados para testes do webservice
 *
 * Durante a implementação do envio dos dados, fique atento à quantidade de caracteres que são encaminhados e
 * ao envio de caracteres especiais que podem ultrapassar o limite conforme encoding.
 * Cuide também a formatação dos campos conforme o manual
 *
 */

class Dados {
	//public $urlIntegracao = 'https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo';
	public $urlIntegracao = 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo';
	public $codigoCedente = '7267827'; //cedente ou beneficiário
	//public $codigoCedente = '5296377'; //cedente ou beneficiário
	public $nossoNumero = '14100000001858414';
	public $dataVencimento = '2018-05-18';
	public $valorNominal = '1';
	public $cnpj = '82572207000103';
	public $codigoTitulo = '00001858414';
	public $dataEmissao = '2018-05-16';

	public $dataJuros = '2018-05-16';
	public $juros = '0.0';

	public $dataMulta = '2018-05-16';
	public $multa = '0.00';

	public $numeroAgencia = '32980';


	/**
	 * Caso o pagador seja uma pessoa fisica
	 **/
	public $infoPagador = array(
		'CPF'      => '05649540985',
		'NOME'     => 'THIAGO EDSON PEREIRA',
		'ENDERECO' => array(
			'LOGRADOURO' => '',
			'BAIRRO'     => '',
			'CIDADE'     => 'ITAPEMA',
			'UF'         => 'SC',
			'CEP'        => '88220000'
		)
	);

	/**
	 * Caso o pagador seja uma empresa
	 **/

	/*    public $infoPagador = array(
			'CNPJ' => '',
			'RAZAO_SOCIAL' => '',
			'ENDERECO' => array(
				'LOGRADOURO' => '',
				'BAIRRO' => '',
				'CIDADE' => '',
				'UF' => '',
				'CEP' => ''
			)
		);*/

}
