# Exemplos de Uso - WSCaixa

Este documento apresenta exemplos práticos de uso da biblioteca WSCaixa em diferentes cenários.

## Índice

- [Exemplo Básico](#exemplo-básico)
- [Registro de Boleto Completo](#registro-de-boleto-completo)
- [Boleto com Desconto](#boleto-com-desconto)
- [Boleto com Juros e Multa](#boleto-com-juros-e-multa)
- [Consulta de Boleto](#consulta-de-boleto)
- [Pagador Pessoa Jurídica](#pagador-pessoa-jurídica)
- [Tratamento de Erros](#tratamento-de-erros)
- [Integração com Framework](#integração-com-framework)
- [Ambiente de Homologação](#ambiente-de-homologação)

---

## Exemplo Básico

Registro simples de um boleto com dados mínimos:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

// Dados mínimos do boleto
$dados = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000001',
    'numeroAgencia' => '1234',
    'dataVencimento' => '2025-12-31',
    'dataEmissao' => '2025-11-01',
    'valorNominal' => 100.00,
    'cnpj' => '12345678000199',
    'codigoTitulo' => 'TITULO001',
    'infoPagador' => [
        'CPF' => '12345678901',
        'NOME' => 'João da Silva',
        'ENDERECO' => [
            'LOGRADOURO' => 'Rua Exemplo, 123',
            'BAIRRO' => 'Centro',
            'CIDADE' => 'São Paulo',
            'UF' => 'SP',
            'CEP' => '01234567'
        ]
    ]
];

// Criar instância e registrar
$wsCaixa = new WSCaixa($dados);
$resultado = $wsCaixa->realizarRegistro();

// Processar resultado
if ($resultado['COD_RETORNO'] == '0') {
    echo "✓ Boleto registrado com sucesso!\n";
    echo "Nosso Número: {$resultado['NOSSO_NUMERO']}\n";
    echo "Código de Barras: {$resultado['CODIGO_BARRAS']}\n";
    echo "Linha Digitável: {$resultado['LINHA_DIGITAVEL']}\n";
} else {
    echo "✗ Erro ao registrar boleto\n";
    echo "Mensagem: {$resultado['MENSAGEM']}\n";
}
```

---

## Registro de Boleto Completo

Exemplo com todos os campos possíveis:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

$dados = [
    // Configuração
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',

    // Dados do Cedente
    'codigoCedente' => '123456',
    'cnpj' => '12345678000199',
    'numeroAgencia' => '1234',

    // Dados do Boleto
    'nossoNumero' => '14000000000000001',
    'codigoTitulo' => 'VENDA-2025-001',
    'dataVencimento' => '2025-12-31',
    'dataEmissao' => '2025-11-01',
    'valorNominal' => 250.00,

    // Mensagens no Boleto
    'mensagem' => 'Referente à compra de produtos',
    'mensagem2' => 'Não receber após o vencimento',

    // Juros (opcional)
    'juros' => [
        'TIPO' => 'TAXA_MENSAL',      // ISENTO, VALOR_DIA, TAXA_MENSAL
        'VALOR' => 2.00,                // 2% ao mês
        'DATA_JUROS' => '2026-01-01'
    ],

    // Multa (opcional)
    'multa' => [
        'TIPO' => 'PERCENTUAL',        // ISENTO, PERCENTUAL, VALOR_FIXO
        'DATA_MULTA' => '2026-01-01',
        'VALOR_MULTA' => 5.00          // 5%
    ],

    // Dados do Pagador (Pessoa Física)
    'infoPagador' => [
        'CPF' => '12345678901',
        'NOME' => 'Maria Santos da Silva',
        'ENDERECO' => [
            'LOGRADOURO' => 'Avenida Paulista, 1000, Apto 501',
            'BAIRRO' => 'Bela Vista',
            'CIDADE' => 'São Paulo',
            'UF' => 'SP',
            'CEP' => '01310100'
        ]
    ]
];

$wsCaixa = new WSCaixa($dados);
$resultado = $wsCaixa->realizarRegistro();

// Salvar resultado em banco de dados ou arquivo
if ($resultado['COD_RETORNO'] == '0') {
    // Exemplo: salvar em banco
    salvarBoletoNoBanco([
        'nosso_numero' => $resultado['NOSSO_NUMERO'],
        'codigo_barras' => $resultado['CODIGO_BARRAS'],
        'linha_digitavel' => $resultado['LINHA_DIGITAVEL'],
        'data_registro' => date('Y-m-d H:i:s')
    ]);

    // Exemplo: enviar email ao cliente
    enviarEmailComBoleto($dados['infoPagador']['CPF'], $resultado);
}
```

---

## Boleto com Desconto

Boleto com desconto para pagamento antecipado:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

// Dados do boleto
$dados = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000002',
    'numeroAgencia' => '1234',
    'dataVencimento' => '2025-12-31',
    'dataEmissao' => '2025-11-01',
    'valorNominal' => 500.00,
    'cnpj' => '12345678000199',
    'codigoTitulo' => 'VENDA-002',
    'infoPagador' => [
        'CPF' => '98765432100',
        'NOME' => 'Carlos Oliveira',
        'ENDERECO' => [
            'LOGRADOURO' => 'Rua das Flores, 456',
            'BAIRRO' => 'Jardim Europa',
            'CIDADE' => 'Rio de Janeiro',
            'UF' => 'RJ',
            'CEP' => '22640100'
        ]
    ]
];

// Definir descontos
// Desconto de R$ 50,00 se pagar até 15/11/2025
$descontos = [
    [
        'DATA_DESCONTO_1' => '2025-11-15',
        'VALOR_DESCONTO_1' => 50.00
    ]
];

// Registrar boleto com desconto
$wsCaixa = new WSCaixa($dados, $descontos);
$resultado = $wsCaixa->realizarRegistro();

if ($resultado['COD_RETORNO'] == '0') {
    echo "Boleto registrado com desconto!\n";
    echo "Valor nominal: R$ 500,00\n";
    echo "Desconto até 15/11/2025: R$ 50,00\n";
    echo "Valor com desconto: R$ 450,00\n";
}
```

---

## Boleto com Juros e Multa

Configuração de juros e multa por atraso:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

$dados = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000003',
    'numeroAgencia' => '1234',
    'dataVencimento' => '2025-11-30',
    'dataEmissao' => '2025-11-01',
    'valorNominal' => 1000.00,
    'cnpj' => '12345678000199',
    'codigoTitulo' => 'MENSALIDADE-11-2025',

    // Juros de 1% ao mês após vencimento
    'juros' => [
        'TIPO' => 'TAXA_MENSAL',
        'VALOR' => 1.00,              // 1% ao mês
        'DATA_JUROS' => '2025-12-01'   // Inicia no dia seguinte ao vencimento
    ],

    // Multa de 2% após vencimento
    'multa' => [
        'TIPO' => 'PERCENTUAL',
        'DATA_MULTA' => '2025-12-01',  // Inicia no dia seguinte ao vencimento
        'VALOR_MULTA' => 2.00          // 2%
    ],

    'mensagem' => 'Mensalidade referente a Novembro/2025',
    'mensagem2' => 'Após vencimento: Multa 2% + Juros 1% a.m.',

    'infoPagador' => [
        'CPF' => '11122233344',
        'NOME' => 'Ana Paula Costa',
        'ENDERECO' => [
            'LOGRADOURO' => 'Rua Sete de Setembro, 789',
            'BAIRRO' => 'Centro',
            'CIDADE' => 'Belo Horizonte',
            'UF' => 'MG',
            'CEP' => '30130000'
        ]
    ]
];

$wsCaixa = new WSCaixa($dados);
$resultado = $wsCaixa->realizarRegistro();

if ($resultado['COD_RETORNO'] == '0') {
    echo "Boleto registrado com juros e multa!\n";
    echo "Vencimento: 30/11/2025\n";
    echo "Após vencimento:\n";
    echo "  - Multa: 2% (R$ 20,00)\n";
    echo "  - Juros: 1% a.m. (R$ 10,00/mês)\n";
}
```

---

## Consulta de Boleto

Consultar um boleto já registrado:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

// Dados para consulta (apenas campos essenciais)
$dadosConsulta = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000001',  // Boleto a ser consultado
    'cnpj' => '12345678000199',
    'numeroAgencia' => '1234'
];

// Instanciar com tipo CONSULTA_BOLETO
$wsCaixa = new WSCaixa($dadosConsulta, null, 'CONSULTA_BOLETO');
$resultado = $wsCaixa->consultarRegistro();

if ($resultado['COD_RETORNO'] == '0') {
    echo "Consulta realizada com sucesso!\n";
    echo "Nosso Número: {$resultado['NOSSO_NUMERO']}\n";
    echo "Status: {$resultado['STATUS']}\n";
    echo "Valor: R$ {$resultado['VALOR']}\n";
    echo "Data Vencimento: {$resultado['DATA_VENCIMENTO']}\n";

    // Verificar se foi pago
    if ($resultado['STATUS'] == 'PAGO') {
        echo "✓ Boleto PAGO em {$resultado['DATA_PAGAMENTO']}\n";
        echo "Valor Pago: R$ {$resultado['VALOR_PAGO']}\n";
    } else {
        echo "⚠ Boleto ainda não foi pago\n";
    }
} else {
    echo "Erro na consulta: {$resultado['MENSAGEM']}\n";
}
```

---

## Pagador Pessoa Jurídica

Boleto para pagador pessoa jurídica (CNPJ):

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

$dados = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000004',
    'numeroAgencia' => '1234',
    'dataVencimento' => '2025-12-15',
    'dataEmissao' => '2025-11-01',
    'valorNominal' => 5000.00,
    'cnpj' => '12345678000199',
    'codigoTitulo' => 'NF-2025-1001',
    'mensagem' => 'Ref. Nota Fiscal 1001',

    // Usar infoPagadorCNPJ ao invés de infoPagador
    'infoPagadorCNPJ' => [
        'CNPJ' => '98765432000188',
        'RAZAO_SOCIAL' => 'Empresa ABC Comércio Ltda',
        'ENDERECO' => [
            'LOGRADOURO' => 'Av. Industrial, 2000',
            'BAIRRO' => 'Distrito Industrial',
            'CIDADE' => 'Curitiba',
            'UF' => 'PR',
            'CEP' => '81000000'
        ]
    ]
];

$wsCaixa = new WSCaixa($dados);
$resultado = $wsCaixa->realizarRegistro();

if ($resultado['COD_RETORNO'] == '0') {
    echo "Boleto B2B registrado!\n";
    echo "Pagador: Empresa ABC Comércio Ltda\n";
    echo "CNPJ: 98.765.432/0001-88\n";
}
```

---

## Tratamento de Erros

Exemplo robusto com tratamento de erros:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

function registrarBoletoComTratamento($dados) {
    try {
        // Validações antes de enviar
        if (empty($dados['nossoNumero']) || strlen($dados['nossoNumero']) != 14) {
            throw new Exception('Nosso número deve ter 14 dígitos');
        }

        if (empty($dados['valorNominal']) || $dados['valorNominal'] <= 0) {
            throw new Exception('Valor nominal inválido');
        }

        // Tentar registrar
        $wsCaixa = new WSCaixa($dados);
        $resultado = $wsCaixa->realizarRegistro();

        // Verificar resposta
        if (!isset($resultado['COD_RETORNO'])) {
            throw new Exception('Resposta inválida do webservice');
        }

        if ($resultado['COD_RETORNO'] != '0') {
            // Tratar erros específicos
            switch ($resultado['COD_RETORNO']) {
                case '2':
                    throw new Exception('Boleto já registrado: ' . $resultado['MENSAGEM']);
                case '3':
                    throw new Exception('Dados inválidos: ' . $resultado['MENSAGEM']);
                case '4':
                    throw new Exception('Cedente não encontrado: ' . $resultado['MENSAGEM']);
                case '5':
                    throw new Exception('Nosso número duplicado: ' . $resultado['MENSAGEM']);
                default:
                    throw new Exception('Erro desconhecido: ' . $resultado['MENSAGEM']);
            }
        }

        return [
            'sucesso' => true,
            'dados' => $resultado
        ];

    } catch (Exception $e) {
        // Log do erro
        error_log("Erro ao registrar boleto: " . $e->getMessage());

        return [
            'sucesso' => false,
            'erro' => $e->getMessage()
        ];
    }
}

// Uso
$dados = [/* ... dados do boleto ... */];
$resultado = registrarBoletoComTratamento($dados);

if ($resultado['sucesso']) {
    echo "✓ Boleto registrado: {$resultado['dados']['NOSSO_NUMERO']}\n";
} else {
    echo "✗ Erro: {$resultado['erro']}\n";
}
```

---

## Integração com Framework

### Laravel

```php
<?php
// app/Services/BoletoService.php

namespace App\Services;

use WSCaixa\WSCaixa;
use Illuminate\Support\Facades\Log;

class BoletoService
{
    public function criarBoleto($pedido)
    {
        $dados = [
            'urlIntegracao' => config('boleto.url_integracao'),
            'codigoCedente' => config('boleto.codigo_cedente'),
            'cnpj' => config('boleto.cnpj'),
            'numeroAgencia' => config('boleto.agencia'),
            'nossoNumero' => $this->gerarNossoNumero($pedido->id),
            'codigoTitulo' => "PEDIDO-{$pedido->id}",
            'dataVencimento' => $pedido->data_vencimento->format('Y-m-d'),
            'dataEmissao' => now()->format('Y-m-d'),
            'valorNominal' => $pedido->valor_total,
            'mensagem' => "Pedido #{$pedido->id}",
            'infoPagador' => [
                'CPF' => $pedido->cliente->cpf,
                'NOME' => $pedido->cliente->nome,
                'ENDERECO' => [
                    'LOGRADOURO' => $pedido->cliente->endereco,
                    'BAIRRO' => $pedido->cliente->bairro,
                    'CIDADE' => $pedido->cliente->cidade,
                    'UF' => $pedido->cliente->uf,
                    'CEP' => $pedido->cliente->cep
                ]
            ]
        ];

        try {
            $wsCaixa = new WSCaixa($dados);
            $resultado = $wsCaixa->realizarRegistro();

            if ($resultado['COD_RETORNO'] == '0') {
                // Salvar no banco
                $pedido->boleto()->create([
                    'nosso_numero' => $resultado['NOSSO_NUMERO'],
                    'codigo_barras' => $resultado['CODIGO_BARRAS'],
                    'linha_digitavel' => $resultado['LINHA_DIGITAVEL'],
                ]);

                return $resultado;
            } else {
                Log::error('Erro ao registrar boleto', $resultado);
                throw new \Exception($resultado['MENSAGEM']);
            }

        } catch (\Exception $e) {
            Log::error('Exceção ao registrar boleto: ' . $e->getMessage());
            throw $e;
        }
    }

    private function gerarNossoNumero($pedidoId)
    {
        return '14' . str_pad($pedidoId, 12, '0', STR_PAD_LEFT);
    }
}
```

---

## Ambiente de Homologação

Testar em ambiente de homologação antes de produção:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

// Definir ambiente
$ambiente = 'homologacao'; // ou 'producao'

$config = [
    'homologacao' => [
        'url' => 'https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
        'usuario' => 'SGCBS01D'
    ],
    'producao' => [
        'url' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
        'usuario' => 'SGCBS02P'
    ]
];

$dados = [
    'urlIntegracao' => $config[$ambiente]['url'],
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000999',  // Usar numeração de teste
    'numeroAgencia' => '1234',
    'dataVencimento' => '2025-12-31',
    'dataEmissao' => '2025-11-01',
    'valorNominal' => 10.00,  // Valor baixo para testes
    'cnpj' => '12345678000199',
    'codigoTitulo' => 'TESTE-001',
    'mensagem' => 'BOLETO DE TESTE - NAO PAGAR',
    'infoPagador' => [
        'CPF' => '00000000000',  // CPF de teste
        'NOME' => 'TESTE HOMOLOGACAO',
        'ENDERECO' => [
            'LOGRADOURO' => 'Rua Teste, 123',
            'BAIRRO' => 'Centro',
            'CIDADE' => 'Sao Paulo',
            'UF' => 'SP',
            'CEP' => '01234567'
        ]
    ]
];

echo "Registrando boleto em ambiente de {$ambiente}...\n";

$wsCaixa = new WSCaixa($dados);
$resultado = $wsCaixa->realizarRegistro();

if ($resultado['COD_RETORNO'] == '0') {
    echo "✓ Teste bem-sucedido!\n";
    echo "Nosso Número: {$resultado['NOSSO_NUMERO']}\n";
    echo "Agora você pode migrar para produção.\n";
} else {
    echo "✗ Falha no teste: {$resultado['MENSAGEM']}\n";
}
```

---

## Debug e Desenvolvimento

Habilitar modo debug para ver XML da requisição:

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

$dados = [/* ... dados do boleto ... */];

$wsCaixa = new WSCaixa($dados);

// Ativar modo debug
$resultado = $wsCaixa->realizarRegistro(
    $debug = true,   // Mostra informações de debug
    $xml = true      // Retorna XML da requisição
);

// Inspecionar resposta completa
echo "=== DEBUG ===\n";
print_r($resultado);

// Ver XML enviado
if (isset($wsCaixa->dadosXml)) {
    echo "\n=== XML ENVIADO ===\n";
    echo $wsCaixa->dadosXml->saveXML();
}
```

---

## Próximos Passos

- Veja [API.md](API.md) para referência completa da API
- Consulte [CONFIGURATION.md](CONFIGURATION.md) para configurações avançadas
- Leia [SECURITY.md](SECURITY.md) para boas práticas de segurança
