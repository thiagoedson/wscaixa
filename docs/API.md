# API Reference - WSCaixa

Esta documentação detalha todas as classes, métodos e propriedades disponíveis na biblioteca WSCaixa.

## Índice

- [Namespace WSCaixa](#namespace-wscaixa)
  - [Classe WSCaixa](#classe-wscaixa)
  - [Classe XmlDomConstruct](#classe-xmldomconstruct)
- [Namespace DadosWS](#namespace-dadosws)
  - [Classe DadosWS](#classe-dadosws)

---

## Namespace WSCaixa

### Classe WSCaixa

**Arquivo:** `lib/WSCaixa.php`

Classe principal responsável pela integração com o webservice da Caixa Econômica Federal.

#### Propriedades

##### `$urlIntegracao` (string)
URL do endpoint do webservice da Caixa.

```php
public $urlIntegracao;
```

**Valores possíveis:**
- Produção: `https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo`
- Homologação: `https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo`

##### `$dadosXml` (XmlDomConstruct)
Objeto que armazena a estrutura XML gerada para as requisições SOAP.

```php
public $dadosXml;
```

---

#### Métodos Públicos

### `__construct()`

Construtor da classe. Inicializa a integração com os dados do boleto.

**Assinatura:**
```php
public function __construct(array $informacoes, array $arrDescontos = null, string $tipo = 'INCLUI_BOLETO')
```

**Parâmetros:**

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `$informacoes` | array | Sim | Array com os dados do boleto (ver estrutura abaixo) |
| `$arrDescontos` | array | Não | Array com descontos a serem aplicados (opcional) |
| `$tipo` | string | Não | Tipo de operação: `INCLUI_BOLETO` ou `CONSULTA_BOLETO` |

**Estrutura do array `$informacoes`:**

```php
$informacoes = [
    // Configuração do Webservice
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/...',

    // Dados do Cedente
    'codigoCedente' => '123456',        // Código do cedente (6 dígitos)
    'cnpj' => '12345678000199',         // CNPJ do cedente
    'numeroAgencia' => '1234',          // Número da agência

    // Dados do Boleto
    'nossoNumero' => '14000000000001',  // Nosso número (14 dígitos)
    'codigoTitulo' => 'CODIGO123',      // Código do título
    'dataVencimento' => '2025-12-31',   // Data de vencimento (YYYY-MM-DD)
    'dataEmissao' => '2025-11-01',      // Data de emissão (YYYY-MM-DD)
    'valorNominal' => 100.00,           // Valor nominal do boleto

    // Juros e Multa (opcional)
    'juros' => [
        'TIPO' => 'ISENTO',             // ISENTO, VALOR_DIA, TAXA_MENSAL
        'VALOR' => 0.00,
        'DATA_JUROS' => '2025-12-31'
    ],
    'multa' => [
        'TIPO' => 'ISENTO',             // ISENTO, PERCENTUAL, VALOR_FIXO
        'DATA_MULTA' => '2025-12-31',
        'VALOR_MULTA' => 0.00
    ],

    // Mensagens no Boleto
    'mensagem' => 'Mensagem linha 1',
    'mensagem2' => 'Mensagem linha 2',

    // Dados do Pagador (Pessoa Física)
    'infoPagador' => [
        'CPF' => '12345678901',
        'NOME' => 'Nome do Pagador',
        'ENDERECO' => [
            'LOGRADOURO' => 'Rua Exemplo',
            'BAIRRO' => 'Centro',
            'CIDADE' => 'São Paulo',
            'UF' => 'SP',
            'CEP' => '01234567'
        ]
    ],

    // OU Dados do Pagador (Pessoa Jurídica)
    'infoPagadorCNPJ' => [
        'CNPJ' => '12345678000199',
        'RAZAO_SOCIAL' => 'Empresa Exemplo LTDA',
        'ENDERECO' => [
            'LOGRADOURO' => 'Av. Exemplo',
            'BAIRRO' => 'Centro',
            'CIDADE' => 'São Paulo',
            'UF' => 'SP',
            'CEP' => '01234567'
        ]
    ]
];
```

**Estrutura do array `$arrDescontos`:**

```php
$arrDescontos = [
    [
        'DATA_DESCONTO_1' => '2025-11-15',  // Data limite do desconto
        'VALOR_DESCONTO_1' => 10.00          // Valor do desconto
    ]
];
```

**Exemplo:**
```php
$dadosBoleto = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/...',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000001',
    'dataVencimento' => '2025-12-31',
    'valorNominal' => 100.00,
    'cnpj' => '12345678000199'
];

$ws = new WSCaixa($dadosBoleto);
```

---

### `realizarRegistro()`

Realiza o registro (inclusão) de um novo boleto no webservice da Caixa.

**Assinatura:**
```php
public function realizarRegistro(bool $debug = false, bool $xml = false): array
```

**Parâmetros:**

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `$debug` | bool | Não | Se `true`, retorna informações de debug |
| `$xml` | bool | Não | Se `true`, retorna o XML da requisição |

**Retorno:**

Retorna um array com a resposta do webservice:

**Em caso de sucesso:**
```php
[
    'COD_RETORNO' => '0',
    'MENSAGEM' => 'OPERACAO EFETUADA COM SUCESSO',
    'NOSSO_NUMERO' => '14000000000000001',
    'CODIGO_BARRAS' => '10491234560000100001234567890123456789',
    'LINHA_DIGITAVEL' => '10491.23456 00001.000012 34567.890123 4 56789012345678'
]
```

**Em caso de erro:**
```php
[
    'COD_RETORNO' => '1',
    'MENSAGEM' => 'Descrição do erro',
    'EXCECAO' => 'Detalhes técnicos do erro'
]
```

**Exemplo:**
```php
$ws = new WSCaixa($dadosBoleto);
$resultado = $ws->realizarRegistro();

if ($resultado['COD_RETORNO'] == '0') {
    echo "Boleto registrado: " . $resultado['NOSSO_NUMERO'];
} else {
    echo "Erro: " . $resultado['MENSAGEM'];
}
```

---

### `consultarRegistro()`

Consulta um boleto já registrado no webservice da Caixa.

**Assinatura:**
```php
public function consultarRegistro(bool $debug = false, bool $xml = false): array
```

**Parâmetros:**

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `$debug` | bool | Não | Se `true`, retorna informações de debug |
| `$xml` | bool | Não | Se `true`, retorna o XML da requisição |

**Retorno:**

Retorna um array com a resposta do webservice contendo os dados do boleto consultado.

**Exemplo:**
```php
$dadosConsulta = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/...',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000001',
    'cnpj' => '12345678000199'
];

$ws = new WSCaixa($dadosConsulta, null, 'CONSULTA_BOLETO');
$resultado = $ws->consultarRegistro();

if ($resultado['COD_RETORNO'] == '0') {
    echo "Status do Boleto: " . $resultado['STATUS'];
}
```

---

#### Métodos Privados

### `_setConfigs()`

Configura internamente os dados do boleto para geração do XML.

**Assinatura:**
```php
private function _setConfigs(array $informacoes, array $arrDescontos = null, string $tipo = 'INCLUI_BOLETO'): void
```

**Localização:** `lib/WSCaixa.php:52`

---

### `_geraHashAutenticacao()`

Gera o hash SHA256 para autenticação na API da Caixa.

**Assinatura:**
```php
private function _geraHashAutenticacao(array $arrayDadosHash, string $tipo): string
```

**Localização:** `lib/WSCaixa.php:310`

**Lógica do Hash:**

Para `INCLUI_BOLETO`:
```
hash = SHA256(codigoCedente + nossoNumero + dataVencimento + valor + cnpj)
```

Para `CONSULTA_BOLETO`:
```
hash = SHA256(codigoCedente + nossoNumero + cnpj)
```

---

### `_geraEstruturaXml()`

Gera a estrutura XML SOAP para envio ao webservice.

**Assinatura:**
```php
private function _geraEstruturaXml(array $arrayDados, string $tipo): void
```

**Localização:** `lib/WSCaixa.php:340`

---

## Classe XmlDomConstruct

**Arquivo:** `lib/WSCaixa.php:374-408`
**Estende:** `DOMDocument`

Classe auxiliar para conversão de arrays PHP em estruturas XML DOM.

### Método Principal

#### `convertArrayToXml()`

Converte recursivamente um array PHP em elementos XML DOM.

**Assinatura:**
```php
public function convertArrayToXml(mixed $mixed, DOMElement $domElement = null): void
```

**Parâmetros:**

| Parâmetro | Tipo | Descrição |
|-----------|------|-----------|
| `$mixed` | mixed | Array ou valor a ser convertido |
| `$domElement` | DOMElement | Elemento DOM pai (opcional) |

---

## Namespace DadosWS

### Classe DadosWS

**Arquivo:** `src/DadosWS.php`

Classe de modelo de dados com propriedades públicas para armazenar informações do boleto. Útil para testes e como exemplo de estrutura de dados.

#### Propriedades Públicas

```php
class DadosWS {
    // Configuração
    public $urlIntegracao;

    // Dados do Cedente
    public $codigoCedente;
    public $cnpj;
    public $numeroAgencia;

    // Dados do Boleto
    public $nossoNumero;
    public $codigoTitulo;
    public $dataVencimento;
    public $dataEmissao;
    public $valorNominal;

    // Juros e Multa
    public $juros;
    public $multa;
    public $dataJuros;
    public $dataMulta;

    // Mensagens
    public $mensagem;
    public $mensagem2;

    // Pagador
    public $infoPagador;        // Pessoa Física
    public $infoPagadorCNPJ;    // Pessoa Jurídica
}
```

**Exemplo de uso:**
```php
use DadosWS\DadosWS;

$dados = new DadosWS();
$dados->codigoCedente = '123456';
$dados->nossoNumero = '14000000000000001';
$dados->valorNominal = 100.00;

$ws = new WSCaixa((array) $dados);
```

---

## Códigos de Retorno

### Códigos Comuns

| Código | Descrição |
|--------|-----------|
| 0 | Operação efetuada com sucesso |
| 1 | Erro genérico |
| 2 | Boleto já registrado |
| 3 | Dados inválidos |
| 4 | Cedente não encontrado |
| 5 | Nosso número duplicado |

---

## Tratamento de Erros

A biblioteca retorna erros através do array de resposta. Sempre verifique o campo `COD_RETORNO`:

```php
$resultado = $ws->realizarRegistro();

if (isset($resultado['COD_RETORNO'])) {
    if ($resultado['COD_RETORNO'] == '0') {
        // Sucesso
    } else {
        // Erro - verificar $resultado['MENSAGEM']
        error_log("Erro WSCaixa: " . $resultado['MENSAGEM']);
    }
} else {
    // Erro de comunicação
    error_log("Erro de comunicação com webservice");
}
```

---

## Considerações Importantes

1. **Formato de Datas:** Sempre use o formato `YYYY-MM-DD`
2. **Nosso Número:** Deve ter exatamente 14 dígitos
3. **CNPJ/CPF:** Apenas números, sem pontuação
4. **Valores:** Use ponto como separador decimal (ex: 100.50)
5. **SSL:** A verificação SSL está desabilitada no código atual (veja [SECURITY.md](SECURITY.md))

---

## Próximos Passos

- Veja [EXAMPLES.md](EXAMPLES.md) para exemplos práticos
- Consulte [CONFIGURATION.md](CONFIGURATION.md) para configuração detalhada
- Leia [SECURITY.md](SECURITY.md) para práticas de segurança
