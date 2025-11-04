# Guia de Configuração - WSCaixa

Este documento fornece instruções detalhadas para configurar e utilizar a biblioteca WSCaixa em diferentes ambientes e cenários.

## Índice

- [Instalação](#instalação)
- [Configuração Básica](#configuração-básica)
- [Ambientes](#ambientes)
- [Variáveis de Ambiente](#variáveis-de-ambiente)
- [Configuração Avançada](#configuração-avançada)
- [Integração com Frameworks](#integração-com-frameworks)
- [Troubleshooting](#troubleshooting)

---

## Instalação

### Requisitos do Sistema

Antes de instalar, certifique-se de que seu sistema atende aos seguintes requisitos:

```bash
# PHP versão 5.4 ou superior
php -v

# Verificar extensões necessárias
php -m | grep -E 'curl|xml|json|dom|SimpleXML'
```

**Extensões necessárias:**
- `php-curl` - Para requisições HTTP
- `php-xml` - Para manipulação de XML
- `php-json` - Para manipulação JSON
- `php-dom` - Para DOMDocument
- `php-simplexml` - Para SimpleXMLElement

### Instalação via Composer

```bash
# Instalar a biblioteca
composer require cassone200/wscaixa

# Ou adicionar ao composer.json
{
    "require": {
        "cassone200/wscaixa": "^1.1"
    }
}
```

### Instalação Manual

```bash
# Clonar repositório
git clone https://github.com/cassone200/wscaixa.git

# Entrar no diretório
cd wscaixa

# Instalar dependências (se houver)
composer install

# Ou usar autoload manual
require_once 'lib/WSCaixa.php';
require_once 'src/DadosWS.php';
```

### Verificar Instalação

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

echo "WSCaixa instalado com sucesso!\n";
echo "Versão: 1.1.8\n";
```

---

## Configuração Básica

### Estrutura Mínima

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

$config = [
    // URL do webservice
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',

    // Dados do cedente
    'codigoCedente' => 'SEU_CODIGO_CEDENTE',
    'cnpj' => 'SEU_CNPJ',
    'numeroAgencia' => 'SUA_AGENCIA',

    // Dados do boleto
    'nossoNumero' => '14000000000000001',
    'codigoTitulo' => 'TITULO001',
    'dataVencimento' => '2025-12-31',
    'dataEmissao' => '2025-11-01',
    'valorNominal' => 100.00,

    // Dados do pagador
    'infoPagador' => [
        'CPF' => 'CPF_PAGADOR',
        'NOME' => 'Nome do Pagador',
        'ENDERECO' => [
            'LOGRADOURO' => 'Rua Exemplo, 123',
            'BAIRRO' => 'Centro',
            'CIDADE' => 'São Paulo',
            'UF' => 'SP',
            'CEP' => '01234567'
        ]
    ]
];

// Criar instância e usar
$ws = new WSCaixa($config);
```

---

## Ambientes

### Ambiente de Produção

```php
// config/producao.php
return [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'usuarioServico' => 'SGCBS02P',
    'codigoCedente' => getenv('CAIXA_CODIGO_CEDENTE'),
    'cnpj' => getenv('CAIXA_CNPJ'),
    'numeroAgencia' => getenv('CAIXA_AGENCIA'),
];
```

### Ambiente de Homologação

```php
// config/homologacao.php
return [
    'urlIntegracao' => 'https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'usuarioServico' => 'SGCBS01D',
    'codigoCedente' => '000000',  // Código de teste
    'cnpj' => '00000000000000',   // CNPJ de teste
    'numeroAgencia' => '0000',    // Agência de teste
];
```

### Seleção de Ambiente

```php
// config/config.php
$ambiente = getenv('APP_ENV') ?: 'homologacao';

switch ($ambiente) {
    case 'producao':
        $config = require __DIR__ . '/producao.php';
        break;
    case 'homologacao':
    default:
        $config = require __DIR__ . '/homologacao.php';
        break;
}

return $config;
```

**Uso:**

```php
<?php
$config = require 'config/config.php';

$dadosBoleto = array_merge($config, [
    'nossoNumero' => '14000000000000001',
    'valorNominal' => 100.00,
    // ... outros dados
]);

$ws = new WSCaixa($dadosBoleto);
```

---

## Variáveis de Ambiente

### Arquivo `.env`

Crie um arquivo `.env` na raiz do projeto:

```env
# Ambiente
APP_ENV=homologacao  # ou 'producao'

# Caixa - Produção
CAIXA_URL_PROD=https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo
CAIXA_USUARIO_PROD=SGCBS02P

# Caixa - Homologação
CAIXA_URL_HOM=https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo
CAIXA_USUARIO_HOM=SGCBS01D

# Credenciais (NUNCA commitar estes valores!)
CAIXA_CODIGO_CEDENTE=123456
CAIXA_CNPJ=12345678000199
CAIXA_AGENCIA=1234

# Opções
CAIXA_TIMEOUT=30
CAIXA_SSL_VERIFY=true
```

### Carregar Variáveis de Ambiente

**Método 1: Com vlucas/phpdotenv**

```bash
composer require vlucas/phpdotenv
```

```php
<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$config = [
    'urlIntegracao' => $_ENV['APP_ENV'] == 'producao'
        ? $_ENV['CAIXA_URL_PROD']
        : $_ENV['CAIXA_URL_HOM'],
    'codigoCedente' => $_ENV['CAIXA_CODIGO_CEDENTE'],
    'cnpj' => $_ENV['CAIXA_CNPJ'],
    'numeroAgencia' => $_ENV['CAIXA_AGENCIA'],
];
```

**Método 2: Função getenv()**

```php
<?php
$config = [
    'urlIntegracao' => getenv('CAIXA_URL_PROD'),
    'codigoCedente' => getenv('CAIXA_CODIGO_CEDENTE'),
    'cnpj' => getenv('CAIXA_CNPJ'),
    'numeroAgencia' => getenv('CAIXA_AGENCIA'),
];
```

### Segurança do `.env`

**IMPORTANTE:** Adicione `.env` ao `.gitignore`:

```bash
# .gitignore
.env
.env.*
!.env.example
```

Crie um `.env.example` para documentação:

```env
# .env.example
APP_ENV=homologacao
CAIXA_CODIGO_CEDENTE=
CAIXA_CNPJ=
CAIXA_AGENCIA=
```

---

## Configuração Avançada

### Wrapper de Configuração

Crie uma classe de configuração centralizada:

```php
<?php
// src/Config/CaixaConfig.php

namespace App\Config;

class CaixaConfig {
    private static $instance = null;
    private $config = [];

    private function __construct() {
        $this->loadConfig();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadConfig() {
        $ambiente = getenv('APP_ENV') ?: 'homologacao';

        $this->config = [
            'ambiente' => $ambiente,
            'urlIntegracao' => $this->getUrlPorAmbiente($ambiente),
            'usuarioServico' => $this->getUsuarioPorAmbiente($ambiente),
            'codigoCedente' => getenv('CAIXA_CODIGO_CEDENTE'),
            'cnpj' => getenv('CAIXA_CNPJ'),
            'numeroAgencia' => getenv('CAIXA_AGENCIA'),
            'timeout' => (int) getenv('CAIXA_TIMEOUT') ?: 30,
            'sslVerify' => filter_var(getenv('CAIXA_SSL_VERIFY'), FILTER_VALIDATE_BOOLEAN) !== false,
        ];
    }

    private function getUrlPorAmbiente($ambiente) {
        $urls = [
            'producao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
            'homologacao' => 'https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
        ];
        return $urls[$ambiente] ?? $urls['homologacao'];
    }

    private function getUsuarioPorAmbiente($ambiente) {
        return $ambiente === 'producao' ? 'SGCBS02P' : 'SGCBS01D';
    }

    public function get($key, $default = null) {
        return $this->config[$key] ?? $default;
    }

    public function getAll() {
        return $this->config;
    }

    public function isProducao() {
        return $this->config['ambiente'] === 'producao';
    }
}
```

**Uso:**

```php
<?php
use App\Config\CaixaConfig;
use WSCaixa\WSCaixa;

$config = CaixaConfig::getInstance();

$dadosBoleto = array_merge($config->getAll(), [
    'nossoNumero' => '14000000000000001',
    'valorNominal' => 100.00,
    // ... outros dados
]);

$ws = new WSCaixa($dadosBoleto);
```

---

### Builder Pattern para Boletos

Facilite a criação de boletos com um Builder:

```php
<?php
// src/Builder/BoletoBuilder.php

class BoletoBuilder {
    private $dados = [];

    public function __construct() {
        $config = CaixaConfig::getInstance();
        $this->dados = $config->getAll();
    }

    public function setNossoNumero($numero) {
        $this->dados['nossoNumero'] = $numero;
        return $this;
    }

    public function setValor($valor) {
        $this->dados['valorNominal'] = $valor;
        return $this;
    }

    public function setVencimento($data) {
        $this->dados['dataVencimento'] = $data;
        return $this;
    }

    public function setEmissao($data) {
        $this->dados['dataEmissao'] = $data;
        return $this;
    }

    public function setCodigoTitulo($codigo) {
        $this->dados['codigoTitulo'] = $codigo;
        return $this;
    }

    public function setPagadorPF($cpf, $nome, $endereco) {
        $this->dados['infoPagador'] = [
            'CPF' => $cpf,
            'NOME' => $nome,
            'ENDERECO' => $endereco
        ];
        return $this;
    }

    public function setPagadorPJ($cnpj, $razaoSocial, $endereco) {
        $this->dados['infoPagadorCNPJ'] = [
            'CNPJ' => $cnpj,
            'RAZAO_SOCIAL' => $razaoSocial,
            'ENDERECO' => $endereco
        ];
        return $this;
    }

    public function setMensagem($linha1, $linha2 = '') {
        $this->dados['mensagem'] = $linha1;
        if ($linha2) {
            $this->dados['mensagem2'] = $linha2;
        }
        return $this;
    }

    public function setJuros($tipo, $valor, $dataInicio) {
        $this->dados['juros'] = [
            'TIPO' => $tipo,
            'VALOR' => $valor,
            'DATA_JUROS' => $dataInicio
        ];
        return $this;
    }

    public function setMulta($tipo, $valor, $dataInicio) {
        $this->dados['multa'] = [
            'TIPO' => $tipo,
            'VALOR_MULTA' => $valor,
            'DATA_MULTA' => $dataInicio
        ];
        return $this;
    }

    public function adicionarDesconto($data, $valor) {
        if (!isset($this->dados['descontos'])) {
            $this->dados['descontos'] = [];
        }
        $this->dados['descontos'][] = [
            'DATA_DESCONTO_1' => $data,
            'VALOR_DESCONTO_1' => $valor
        ];
        return $this;
    }

    public function build() {
        return $this->dados;
    }

    public function registrar() {
        $descontos = $this->dados['descontos'] ?? null;
        unset($this->dados['descontos']);

        $ws = new WSCaixa($this->dados, $descontos);
        return $ws->realizarRegistro();
    }
}
```

**Uso do Builder:**

```php
<?php
use App\Builder\BoletoBuilder;

$resultado = (new BoletoBuilder())
    ->setNossoNumero('14000000000000001')
    ->setValor(250.00)
    ->setVencimento('2025-12-31')
    ->setEmissao('2025-11-01')
    ->setCodigoTitulo('VENDA-001')
    ->setPagadorPF('12345678901', 'João Silva', [
        'LOGRADOURO' => 'Rua A, 123',
        'BAIRRO' => 'Centro',
        'CIDADE' => 'São Paulo',
        'UF' => 'SP',
        'CEP' => '01234567'
    ])
    ->setMensagem('Referente à compra', 'Não receber após vencimento')
    ->adicionarDesconto('2025-11-15', 25.00)
    ->registrar();
```

---

## Integração com Frameworks

### Laravel

**1. Service Provider**

```php
<?php
// app/Providers/CaixaServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CaixaService;

class CaixaServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->singleton(CaixaService::class, function ($app) {
            return new CaixaService();
        });
    }
}
```

**2. Service Class**

```php
<?php
// app/Services/CaixaService.php

namespace App\Services;

use WSCaixa\WSCaixa;

class CaixaService {
    private $config;

    public function __construct() {
        $this->config = [
            'urlIntegracao' => config('caixa.url'),
            'codigoCedente' => config('caixa.codigo_cedente'),
            'cnpj' => config('caixa.cnpj'),
            'numeroAgencia' => config('caixa.agencia'),
        ];
    }

    public function registrarBoleto($dados) {
        $boleto = array_merge($this->config, $dados);
        $ws = new WSCaixa($boleto);
        return $ws->realizarRegistro();
    }
}
```

**3. Config File**

```php
<?php
// config/caixa.php

return [
    'url' => env('CAIXA_URL', 'https://des.barramento.caixa.gov.br/...'),
    'codigo_cedente' => env('CAIXA_CODIGO_CEDENTE'),
    'cnpj' => env('CAIXA_CNPJ'),
    'agencia' => env('CAIXA_AGENCIA'),
];
```

---

### Symfony

**1. Service Configuration**

```yaml
# config/services.yaml

services:
    App\Service\CaixaService:
        arguments:
            $urlIntegracao: '%env(CAIXA_URL)%'
            $codigoCedente: '%env(CAIXA_CODIGO_CEDENTE)%'
            $cnpj: '%env(CAIXA_CNPJ)%'
            $agencia: '%env(CAIXA_AGENCIA)%'
```

**2. Service Class**

```php
<?php
// src/Service/CaixaService.php

namespace App\Service;

use WSCaixa\WSCaixa;

class CaixaService {
    private $config;

    public function __construct(
        string $urlIntegracao,
        string $codigoCedente,
        string $cnpj,
        string $agencia
    ) {
        $this->config = [
            'urlIntegracao' => $urlIntegracao,
            'codigoCedente' => $codigoCedente,
            'cnpj' => $cnpj,
            'numeroAgencia' => $agencia,
        ];
    }

    public function registrarBoleto(array $dados): array {
        $boleto = array_merge($this->config, $dados);
        $ws = new WSCaixa($boleto);
        return $ws->realizarRegistro();
    }
}
```

---

## Troubleshooting

### Problemas Comuns

#### 1. Erro: "SSL certificate problem"

**Solução:**

```php
// Temporário (NÃO recomendado para produção)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Recomendado: Configurar certificados CA
curl_setopt($ch, CURLOPT_CAINFO, '/path/to/cacert.pem');
```

#### 2. Erro: "Connection timeout"

**Solução:**

```php
// Aumentar timeout
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
```

#### 3. Erro: "Nosso número inválido"

**Verificar:**
- Deve ter exatamente 14 dígitos
- Apenas números
- Não pode estar duplicado

```php
$nossoNumero = str_pad($numero, 14, '0', STR_PAD_LEFT);
```

#### 4. Erro: "CNPJ/CPF inválido"

**Verificar:**
- Apenas números (sem pontuação)
- Validar dígitos verificadores

```php
$cnpj = preg_replace('/[^0-9]/', '', $cnpj);
```

### Logs e Debug

```php
// Habilitar logs de erro
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log de debug WSCaixa
$ws = new WSCaixa($dados);
$resultado = $ws->realizarRegistro(
    $debug = true,  // Ativa modo debug
    $xml = true     // Inclui XML na resposta
);

print_r($resultado);
```

---

## Próximos Passos

- Veja [EXAMPLES.md](EXAMPLES.md) para exemplos práticos
- Consulte [API.md](API.md) para referência completa da API
- Leia [SECURITY.md](SECURITY.md) para práticas de segurança
