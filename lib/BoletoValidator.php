<?php
/**
 * Classe de validação de dados para boletos
 *
 * Esta classe implementa validações de segurança para prevenir
 * injeções XML e garantir integridade dos dados
 *
 * @author Thiago Edson
 * @version 1.0
 */

namespace WSCaixa;

class BoletoValidator {

	/**
	 * Valida todos os dados obrigatórios do boleto
	 *
	 * @param array $dados
	 * @return bool
	 * @throws \Exception
	 */
	public static function validar( array $dados ) {
		// Validar campos obrigatórios
		$required = [ 'codigoCedente', 'nossoNumero', 'valorNominal', 'dataVencimento', 'cnpj' ];

		foreach ( $required as $field ) {
			if ( empty( $dados[ $field ] ) ) {
				throw new \Exception( "Campo obrigatório ausente: {$field}" );
			}
		}

		// Validar formato do Nosso Número (14 dígitos)
		if ( ! preg_match( '/^\d{14}$/', $dados['nossoNumero'] ) ) {
			throw new \Exception( 'Nosso número deve ter exatamente 14 dígitos numéricos' );
		}

		// Validar valor nominal
		if ( ! is_numeric( $dados['valorNominal'] ) || $dados['valorNominal'] <= 0 ) {
			throw new \Exception( 'Valor nominal deve ser um número positivo' );
		}

		// Validar data de vencimento
		$vencimento = strtotime( $dados['dataVencimento'] );
		if ( $vencimento === false ) {
			throw new \Exception( 'Data de vencimento inválida. Use formato YYYY-MM-DD' );
		}

		// Validar CNPJ
		if ( ! self::validarCNPJ( $dados['cnpj'] ) ) {
			throw new \Exception( 'CNPJ inválido' );
		}

		// Validar CPF/CNPJ do pagador se presente
		if ( isset( $dados['infoPagador']['CPF'] ) ) {
			if ( ! self::validarCPF( $dados['infoPagador']['CPF'] ) ) {
				throw new \Exception( 'CPF do pagador inválido' );
			}
		}

		if ( isset( $dados['infoPagadorCNPJ']['CNPJ'] ) ) {
			if ( ! self::validarCNPJ( $dados['infoPagadorCNPJ']['CNPJ'] ) ) {
				throw new \Exception( 'CNPJ do pagador inválido' );
			}
		}

		return true;
	}

	/**
	 * Valida CPF usando algoritmo oficial
	 *
	 * @param string $cpf
	 * @return bool
	 */
	public static function validarCPF( $cpf ) {
		// Remove caracteres não numéricos
		$cpf = preg_replace( '/[^0-9]/', '', $cpf );

		// Verifica se tem 11 dígitos
		if ( strlen( $cpf ) != 11 ) {
			return false;
		}

		// Verifica se todos os dígitos são iguais (ex: 111.111.111-11)
		if ( preg_match( '/^(\d)\1{10}$/', $cpf ) ) {
			return false;
		}

		// Validação dos dígitos verificadores
		for ( $t = 9; $t < 11; $t ++ ) {
			$d = 0;
			for ( $c = 0; $c < $t; $c ++ ) {
				$d += $cpf[ $c ] * ( ( $t + 1 ) - $c );
			}
			$d = ( ( 10 * $d ) % 11 ) % 10;
			if ( $cpf[ $c ] != $d ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Valida CNPJ usando algoritmo oficial
	 *
	 * @param string $cnpj
	 * @return bool
	 */
	public static function validarCNPJ( $cnpj ) {
		// Remove caracteres não numéricos
		$cnpj = preg_replace( '/[^0-9]/', '', $cnpj );

		// Verifica se tem 14 dígitos
		if ( strlen( $cnpj ) != 14 ) {
			return false;
		}

		// Verifica se todos os dígitos são iguais
		if ( preg_match( '/^(\d)\1{13}$/', $cnpj ) ) {
			return false;
		}

		// Validação dos dígitos verificadores
		$length = strlen( $cnpj ) - 2;
		$numbers = substr( $cnpj, 0, $length );
		$digits = substr( $cnpj, $length );
		$sum = 0;
		$pos = $length - 7;

		for ( $i = $length; $i >= 1; $i -- ) {
			$sum += $numbers[ $length - $i ] * $pos --;
			if ( $pos < 2 ) {
				$pos = 9;
			}
		}

		$result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;

		if ( $result != $digits[0] ) {
			return false;
		}

		$length = $length + 1;
		$numbers = substr( $cnpj, 0, $length );
		$sum = 0;
		$pos = $length - 7;

		for ( $i = $length; $i >= 1; $i -- ) {
			$sum += $numbers[ $length - $i ] * $pos --;
			if ( $pos < 2 ) {
				$pos = 9;
			}
		}

		$result = $sum % 11 < 2 ? 0 : 11 - $sum % 11;

		if ( $result != $digits[1] ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitiza string para prevenir XML injection
	 *
	 * @param string $valor
	 * @return string
	 */
	public static function sanitizarParaXML( $valor ) {
		if ( ! is_string( $valor ) ) {
			return $valor;
		}

		// Remove caracteres especiais XML
		$valor = htmlspecialchars( $valor, ENT_QUOTES | ENT_XML1, 'UTF-8' );

		// Remove caracteres de controle
		$valor = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $valor );

		return $valor;
	}

	/**
	 * Sanitiza recursivamente array de dados
	 *
	 * @param array $dados
	 * @return array
	 */
	public static function sanitizarDados( array $dados ) {
		array_walk_recursive( $dados, function ( &$item ) {
			if ( is_string( $item ) ) {
				$item = self::sanitizarParaXML( $item );
			}
		} );

		return $dados;
	}

	/**
	 * Valida URL de integração
	 *
	 * @param string $url
	 * @return bool
	 * @throws \Exception
	 */
	public static function validarURL( $url ) {
		if ( empty( $url ) ) {
			throw new \Exception( 'URL de integração não pode estar vazia' );
		}

		// Deve ser HTTPS
		if ( strpos( $url, 'https://' ) !== 0 ) {
			throw new \Exception( 'URL de integração deve usar HTTPS' );
		}

		// Deve ser domínio da Caixa
		if ( strpos( $url, 'caixa.gov.br' ) === false ) {
			throw new \Exception( 'URL de integração deve ser do domínio caixa.gov.br' );
		}

		return true;
	}
}
