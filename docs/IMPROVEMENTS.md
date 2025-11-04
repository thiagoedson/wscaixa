# Propostas de Melhorias - WSCaixa

Este documento lista melhorias propostas para a biblioteca WSCaixa, organizadas por prioridade e impacto. As melhorias foram identificadas através da análise do código atual e boas práticas de desenvolvimento.

## Índice

- [Visão Geral](#visão-geral)
- [Melhorias Críticas](#melhorias-críticas)
- [Melhorias de Alta Prioridade](#melhorias-de-alta-prioridade)
- [Melhorias de Média Prioridade](#melhorias-de-média-prioridade)
- [Melhorias de Baixa Prioridade](#melhorias-de-baixa-prioridade)
- [Roadmap](#roadmap)
- [Como Contribuir](#como-contribuir)

---

## Visão Geral

**Status Atual:** Versão 1.1.8
**Próxima Versão Planejada:** 2.0.0 (Breaking Changes)
**Data Estimada:** A definir

### Estatísticas

| Categoria | Quantidade | Estimativa de Esforço |
|-----------|------------|----------------------|
| Críticas | 2 | 16-24 horas |
| Alta Prioridade | 5 | 40-60 horas |
| Média Prioridade | 8 | 60-80 horas |
| Baixa Prioridade | 6 | 40-50 horas |
| **Total** | **21** | **156-214 horas** |

---

## Melhorias Críticas

### 🔴 1. Habilitar Verificação SSL/TLS

**Prioridade:** CRÍTICA
**Impacto:** ALTO (Segurança)
**Esforço:** 2-4 horas
**Breaking Change:** Sim

**Problema:**
```php
// lib/WSCaixa.php:44-45
curl_setopt($connCURL, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($connCURL, CURLOPT_SSL_VERIFYHOST, false);
```

A verificação SSL está desabilitada, permitindo ataques Man-in-the-Middle.

**Solução Proposta:**

```php
// Habilitar verificação SSL
curl_setopt($connCURL, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($connCURL, CURLOPT_SSL_VERIFYHOST, 2);

// Especificar bundle de certificados CA
$caPath = $this->getCertificatePath();
if (file_exists($caPath)) {
    curl_setopt($connCURL, CURLOPT_CAINFO, $caPath);
} else {
    throw new Exception('Arquivo de certificados CA não encontrado');
}

private function getCertificatePath() {
    // Tentar localizar automaticamente
    $paths = [
        '/etc/ssl/certs/ca-certificates.crt', // Debian/Ubuntu/Gentoo
        '/etc/pki/tls/certs/ca-bundle.crt',   // Fedora/RHEL
        '/etc/ssl/ca-bundle.pem',              // OpenSUSE
        '/etc/ssl/cert.pem',                   // OpenBSD
        __DIR__ . '/../cacert.pem',           // Bundle local
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }

    return false;
}
```

**Benefícios:**
- ✅ Proteção contra ataques MitM
- ✅ Validação de autenticidade do servidor
- ✅ Conformidade com padrões de segurança

**Riscos:**
- ⚠️ Pode quebrar em ambientes sem certificados configurados
- ⚠️ Requer documentação clara de instalação

---

### 🔴 2. Remover `print_r()` e `die` em Produção

**Prioridade:** CRÍTICA
**Impacto:** ALTO (Segurança & UX)
**Esforço:** 4-6 horas
**Breaking Change:** Não

**Problema:**
```php
// lib/WSCaixa.php:58-96
if ($err) {
    print_r(json_encode($err));
    die;
}
```

Código contém múltiplos `print_r()` e `die` que:
- Expõem informações sensíveis
- Interrompem execução abruptamente
- Dificultam tratamento de erros

**Solução Proposta:**

```php
public function realizarRegistro($debug = false, $xml = false) {
    try {
        $connCURL = curl_init($this->urlIntegracao);

        // Configurações cURL...

        $responseCURL = curl_exec($connCURL);
        $err = curl_error($connCURL);
        $httpCode = curl_getinfo($connCURL, CURLINFO_HTTP_CODE);
        curl_close($connCURL);

        if ($err) {
            $this->log('error', "Erro cURL: {$err}");
            throw new CaixaException(
                'Erro ao comunicar com webservice da Caixa',
                CaixaException::ERRO_CURL,
                null,
                ['curl_error' => $err]
            );
        }

        if ($httpCode >= 400) {
            $this->log('error', "HTTP {$httpCode}: {$responseCURL}");
            throw new CaixaException(
                "Erro HTTP {$httpCode}",
                CaixaException::ERRO_HTTP,
                null,
                ['http_code' => $httpCode, 'response' => $responseCURL]
            );
        }

        $response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $responseCURL);
        $xml = new SimpleXMLElement($response);
        $xmlArray = json_decode(json_encode((array) $xml), true);
        $infoArray = $xmlArray['soapenvBody']['manutencaocobrancabancariaSERVICO_SAIDA']['DADOS'];

        if ($xml && $debug) {
            $infoArray['XML']['REQUEST'] = $this->dadosXml;
            $infoArray['XML']['RESPONSE'] = $responseCURL;
        }

        // Log de sucesso
        $this->log('info', 'Boleto registrado com sucesso', [
            'nosso_numero' => $infoArray['NOSSO_NUMERO'] ?? null
        ]);

        return $infoArray;

    } catch (SimpleXMLElement $e) {
        $this->log('error', "Erro ao parsear XML: {$e->getMessage()}");
        throw new CaixaException(
            'Resposta inválida do webservice',
            CaixaException::ERRO_XML,
            $e
        );
    } catch (CaixaException $e) {
        throw $e;
    } catch (Exception $e) {
        $this->log('error', "Erro inesperado: {$e->getMessage()}");
        throw new CaixaException(
            'Erro inesperado ao processar requisição',
            CaixaException::ERRO_GENERICO,
            $e
        );
    }
}

private function log($level, $message, array $context = []) {
    if ($this->logger) {
        $this->logger->log($level, $message, $context);
    } else {
        error_log("[WSCaixa] [{$level}] {$message} " . json_encode($context));
    }
}
```

**Criar Exception Customizada:**

```php
// lib/CaixaException.php
namespace WSCaixa;

class CaixaException extends \Exception {
    const ERRO_CURL = 1;
    const ERRO_HTTP = 2;
    const ERRO_XML = 3;
    const ERRO_VALIDACAO = 4;
    const ERRO_GENERICO = 99;

    private $context = [];

    public function __construct(
        $message = "",
        $code = 0,
        \Throwable $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext() {
        return $this->context;
    }
}
```

**Benefícios:**
- ✅ Tratamento adequado de erros
- ✅ Não expõe informações sensíveis
- ✅ Permite captura e tratamento pelo código cliente
- ✅ Logging estruturado

---

## Melhorias de Alta Prioridade

### 🟠 3. Implementar Validação de Dados de Entrada

**Prioridade:** ALTA
**Impacto:** ALTO (Segurança & Qualidade)
**Esforço:** 8-12 horas
**Breaking Change:** Não

**Solução:**

```php
// lib/Validator/BoletoValidator.php
namespace WSCaixa\Validator;

class BoletoValidator {
    public static function validar(array $dados) {
        self::validarCamposObrigatorios($dados);
        self::validarFormatos($dados);
        self::validarValores($dados);
        self::validarDatas($dados);
        self::validarPagador($dados);
    }

    private static function validarCamposObrigatorios(array $dados) {
        $required = [
            'codigoCedente',
            'nossoNumero',
            'valorNominal',
            'dataVencimento',
            'dataEmissao',
            'cnpj',
            'numeroAgencia'
        ];

        foreach ($required as $field) {
            if (!isset($dados[$field]) || $dados[$field] === '') {
                throw new ValidationException("Campo obrigatório ausente: {$field}");
            }
        }
    }

    private static function validarFormatos(array $dados) {
        // Nosso Número: 14 dígitos
        if (!preg_match('/^\d{14}$/', $dados['nossoNumero'])) {
            throw new ValidationException('Nosso número deve ter 14 dígitos numéricos');
        }

        // CNPJ: 14 dígitos
        $cnpj = preg_replace('/[^0-9]/', '', $dados['cnpj']);
        if (!self::validarCNPJ($cnpj)) {
            throw new ValidationException('CNPJ inválido');
        }

        // CEP
        if (isset($dados['infoPagador']['ENDERECO']['CEP'])) {
            $cep = preg_replace('/[^0-9]/', '', $dados['infoPagador']['ENDERECO']['CEP']);
            if (strlen($cep) != 8) {
                throw new ValidationException('CEP inválido');
            }
        }
    }

    private static function validarValores(array $dados) {
        if (!is_numeric($dados['valorNominal']) || $dados['valorNominal'] <= 0) {
            throw new ValidationException('Valor nominal deve ser maior que zero');
        }

        // Valor máximo de boleto (exemplo: R$ 1.000.000,00)
        if ($dados['valorNominal'] > 1000000) {
            throw new ValidationException('Valor nominal excede o limite permitido');
        }
    }

    private static function validarDatas(array $dados) {
        $vencimento = strtotime($dados['dataVencimento']);
        $emissao = strtotime($dados['dataEmissao']);

        if ($vencimento === false) {
            throw new ValidationException('Data de vencimento inválida');
        }

        if ($emissao === false) {
            throw new ValidationException('Data de emissão inválida');
        }

        // Vencimento não pode ser no passado
        if ($vencimento < strtotime('today')) {
            throw new ValidationException('Data de vencimento não pode ser no passado');
        }

        // Emissão não pode ser posterior ao vencimento
        if ($emissao > $vencimento) {
            throw new ValidationException('Data de emissão não pode ser posterior ao vencimento');
        }
    }

    private static function validarPagador(array $dados) {
        // Validar se tem um tipo de pagador
        $temPF = isset($dados['infoPagador']);
        $temPJ = isset($dados['infoPagadorCNPJ']);

        if (!$temPF && !$temPJ) {
            throw new ValidationException('Dados do pagador não informados');
        }

        if ($temPF && $temPJ) {
            throw new ValidationException('Informar apenas um tipo de pagador (PF ou PJ)');
        }

        // Validar CPF se for PF
        if ($temPF && isset($dados['infoPagador']['CPF'])) {
            $cpf = preg_replace('/[^0-9]/', '', $dados['infoPagador']['CPF']);
            if (!self::validarCPF($cpf)) {
                throw new ValidationException('CPF do pagador inválido');
            }
        }

        // Validar CNPJ se for PJ
        if ($temPJ && isset($dados['infoPagadorCNPJ']['CNPJ'])) {
            $cnpj = preg_replace('/[^0-9]/', '', $dados['infoPagadorCNPJ']['CNPJ']);
            if (!self::validarCNPJ($cnpj)) {
                throw new ValidationException('CNPJ do pagador inválido');
            }
        }
    }

    public static function validarCPF($cpf) {
        $cpf = preg_replace('/[^0-9]/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    public static function validarCNPJ($cnpj) {
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        if (strlen($cnpj) != 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $tamanho = strlen($cnpj) - 2;
        $numeros = substr($cnpj, 0, $tamanho);
        $digitos = substr($cnpj, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;

        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }

        $resultado = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
        if ($resultado != $digitos[0]) {
            return false;
        }

        $tamanho++;
        $numeros = substr($cnpj, 0, $tamanho);
        $soma = 0;
        $pos = $tamanho - 7;

        for ($i = $tamanho; $i >= 1; $i--) {
            $soma += $numeros[$tamanho - $i] * $pos--;
            if ($pos < 2) {
                $pos = 9;
            }
        }

        $resultado = $soma % 11 < 2 ? 0 : 11 - ($soma % 11);
        return $resultado == $digitos[1];
    }
}
```

**Uso:**

```php
use WSCaixa\Validator\BoletoValidator;

try {
    BoletoValidator::validar($dadosBoleto);
    $ws = new WSCaixa($dadosBoleto);
    $resultado = $ws->realizarRegistro();
} catch (ValidationException $e) {
    echo "Erro de validação: " . $e->getMessage();
}
```

---

### 🟠 4. Implementar Sistema de Logging

**Prioridade:** ALTA
**Impacto:** MÉDIO (Observabilidade)
**Esforço:** 6-8 horas
**Breaking Change:** Não

**Solução:**

```php
// lib/Logger/LoggerInterface.php
namespace WSCaixa\Logger;

interface LoggerInterface {
    public function info($message, array $context = []);
    public function warning($message, array $context = []);
    public function error($message, array $context = []);
    public function debug($message, array $context = []);
}

// lib/Logger/FileLogger.php
namespace WSCaixa\Logger;

class FileLogger implements LoggerInterface {
    private $logPath;

    public function __construct($logPath = null) {
        $this->logPath = $logPath ?? sys_get_temp_dir() . '/wscaixa.log';
    }

    public function info($message, array $context = []) {
        $this->log('INFO', $message, $context);
    }

    public function warning($message, array $context = []) {
        $this->log('WARNING', $message, $context);
    }

    public function error($message, array $context = []) {
        $this->log('ERROR', $message, $context);
    }

    public function debug($message, array $context = []) {
        $this->log('DEBUG', $message, $context);
    }

    private function log($level, $message, array $context) {
        $timestamp = date('Y-m-d H:i:s');
        $contextJson = !empty($context) ? json_encode($context) : '';
        $logMessage = "[{$timestamp}] [{$level}] {$message} {$contextJson}\n";

        file_put_contents($this->logPath, $logMessage, FILE_APPEND);
    }
}
```

**Adicionar ao WSCaixa:**

```php
class WSCaixa {
    private $logger;

    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
        return $this;
    }

    // Usar em métodos
    if ($this->logger) {
        $this->logger->info('Registrando boleto', [
            'nosso_numero' => $dados['nossoNumero']
        ]);
    }
}
```

---

### 🟠 5. Adicionar Timeout Configurável

**Prioridade:** ALTA
**Impacto:** MÉDIO (Confiabilidade)
**Esforço:** 2-3 horas
**Breaking Change:** Não

**Solução:**

```php
class WSCaixa {
    private $timeout = 30;          // Timeout total (segundos)
    private $connectTimeout = 10;   // Timeout de conexão (segundos)

    public function setTimeout($seconds) {
        $this->timeout = $seconds;
        return $this;
    }

    public function setConnectTimeout($seconds) {
        $this->connectTimeout = $seconds;
        return $this;
    }

    public function realizarRegistro($debug = false, $xml = false) {
        $connCURL = curl_init($this->urlIntegracao);

        // Adicionar timeouts
        curl_setopt($connCURL, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($connCURL, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);

        // ... resto do código
    }
}
```

**Uso:**

```php
$ws = new WSCaixa($dados);
$ws->setTimeout(60)           // Timeout total de 60s
   ->setConnectTimeout(15);   // Timeout de conexão de 15s
$resultado = $ws->realizarRegistro();
```

---

### 🟠 6. Implementar Rate Limiting

**Prioridade:** ALTA
**Impacto:** MÉDIO (Proteção)
**Esforço:** 4-6 horas
**Breaking Change:** Não

**Solução:**

```php
// lib/RateLimiter/RateLimiter.php
namespace WSCaixa\RateLimiter;

class RateLimiter {
    private $maxRequests;
    private $perSeconds;
    private $requests = [];
    private $storageFile;

    public function __construct($maxRequests = 10, $perSeconds = 60, $storageFile = null) {
        $this->maxRequests = $maxRequests;
        $this->perSeconds = $perSeconds;
        $this->storageFile = $storageFile ?? sys_get_temp_dir() . '/wscaixa_ratelimit.json';
        $this->loadRequests();
    }

    public function allowRequest() {
        $this->cleanup();

        if (count($this->requests) >= $this->maxRequests) {
            $oldestRequest = min($this->requests);
            $waitTime = $this->perSeconds - (time() - $oldestRequest);
            throw new RateLimitException(
                "Rate limit excedido. Aguarde {$waitTime} segundos.",
                $waitTime
            );
        }

        $this->requests[] = time();
        $this->saveRequests();
        return true;
    }

    private function cleanup() {
        $now = time();
        $this->requests = array_filter($this->requests, function($timestamp) use ($now) {
            return ($now - $timestamp) < $this->perSeconds;
        });
    }

    private function loadRequests() {
        if (file_exists($this->storageFile)) {
            $data = json_decode(file_get_contents($this->storageFile), true);
            $this->requests = $data ?? [];
        }
    }

    private function saveRequests() {
        file_put_contents($this->storageFile, json_encode($this->requests));
    }
}
```

---

### 🟠 7. Adicionar Suporte a PSR-3 (Logger)

**Prioridade:** ALTA
**Impacto:** MÉDIO (Interoperabilidade)
**Esforço:** 4-6 horas
**Breaking Change:** Não

**Solução:**

```bash
composer require psr/log
```

```php
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class WSCaixa implements LoggerAwareInterface {
    use LoggerAwareTrait;

    public function realizarRegistro($debug = false, $xml = false) {
        if ($this->logger) {
            $this->logger->info('Iniciando registro de boleto');
        }

        // ... código
    }
}
```

**Uso com Monolog:**

```php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logger = new Logger('wscaixa');
$logger->pushHandler(new StreamHandler('/var/log/wscaixa.log', Logger::INFO));

$ws = new WSCaixa($dados);
$ws->setLogger($logger);
$resultado = $ws->realizarRegistro();
```

---

## Melhorias de Média Prioridade

### 🟡 8. Adicionar Suporte a Retry com Backoff Exponencial

**Esforço:** 6-8 horas

```php
class WSCaixa {
    private $maxRetries = 3;
    private $retryDelay = 1; // segundos

    public function realizarRegistroComRetry() {
        $attempt = 0;

        while ($attempt < $this->maxRetries) {
            try {
                return $this->realizarRegistro();
            } catch (CaixaException $e) {
                $attempt++;

                if ($attempt >= $this->maxRetries) {
                    throw $e;
                }

                $delay = $this->retryDelay * pow(2, $attempt - 1);
                $this->logger->warning("Tentativa {$attempt} falhou. Retry em {$delay}s");
                sleep($delay);
            }
        }
    }
}
```

---

### 🟡 9. Implementar Cache de Consultas

**Esforço:** 8-10 horas

```php
interface CacheInterface {
    public function get($key);
    public function set($key, $value, $ttl = 3600);
    public function has($key);
}

class FileCache implements CacheInterface {
    // Implementação
}

class WSCaixa {
    private $cache;

    public function consultarRegistro($debug = false, $xml = false) {
        $cacheKey = "boleto_{$this->nossoNumero}";

        if ($this->cache && $this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        $resultado = $this->_consultarRegistro($debug, $xml);

        if ($this->cache && $resultado['COD_RETORNO'] == '0') {
            $this->cache->set($cacheKey, $resultado, 300); // 5 minutos
        }

        return $resultado;
    }
}
```

---

### 🟡 10. Adicionar Testes Unitários

**Esforço:** 20-30 horas

```bash
composer require --dev phpunit/phpunit
```

```php
// tests/WSCaixaTest.php
use PHPUnit\Framework\TestCase;
use WSCaixa\WSCaixa;

class WSCaixaTest extends TestCase {
    public function testConstrutor() {
        $dados = [
            'codigoCedente' => '123456',
            'nossoNumero' => '14000000000000001',
            // ...
        ];

        $ws = new WSCaixa($dados);
        $this->assertInstanceOf(WSCaixa::class, $ws);
    }

    public function testValidacaoNossoNumero() {
        $this->expectException(ValidationException::class);

        $dados = [
            'nossoNumero' => '123', // Inválido
            // ...
        ];

        new WSCaixa($dados);
    }
}
```

---

### 🟡 11-15. Outras Melhorias de Média Prioridade

- **11. Sanitização automática de dados XML** (4-6h)
- **12. Suporte a múltiplos descontos** (4-6h)
- **13. Webhook para notificações** (12-16h)
- **14. CLI para testes** (8-10h)
- **15. Documentação API com PHPDoc** (6-8h)

---

## Melhorias de Baixa Prioridade

### 🟢 16-21. Melhorias de Baixa Prioridade

- **16. Suporte a Composer Scripts** (2-3h)
- **17. GitHub Actions CI/CD** (4-6h)
- **18. Docker para desenvolvimento** (6-8h)
- **19. Geração de código de barras (imagem)** (8-10h)
- **20. Exportação de boleto em PDF** (12-16h)
- **21. Dashboard de monitoramento** (20-30h)

---

## Roadmap

### Versão 1.2.0 (Patch Release)
**Estimativa:** 2-3 semanas

- [ ] Habilitar verificação SSL
- [ ] Remover print_r/die
- [ ] Adicionar logging básico
- [ ] Timeout configurável
- [ ] Documentação atualizada

### Versão 2.0.0 (Major Release)
**Estimativa:** 2-3 meses

- [ ] Validação completa de dados
- [ ] Sistema de exceptions robusto
- [ ] Rate limiting
- [ ] Suporte PSR-3
- [ ] Retry automático
- [ ] Testes unitários (>80% cobertura)
- [ ] PHP 7.4+ (com type hints)

### Versão 2.1.0 (Feature Release)
**Estimativa:** 1-2 meses

- [ ] Cache de consultas
- [ ] CLI para testes
- [ ] Webhook support
- [ ] Múltiplos descontos

### Versão 3.0.0 (Next Gen)
**Estimativa:** 4-6 meses

- [ ] PHP 8.0+ (com enums, attributes)
- [ ] Async/Await support
- [ ] GraphQL API
- [ ] Dashboard web
- [ ] Exportação PDF/Imagem

---

## Como Contribuir

### 1. Fork e Clone

```bash
git clone https://github.com/seu-usuario/wscaixa.git
cd wscaixa
composer install
```

### 2. Criar Branch

```bash
git checkout -b feature/nome-da-melhoria
```

### 3. Implementar e Testar

```bash
# Implementar melhoria
# Adicionar testes
phpunit tests/

# Verificar code style
vendor/bin/phpcs --standard=PSR12 lib/
```

### 4. Pull Request

- Descreva a melhoria
- Referencie issues relacionadas
- Adicione exemplos de uso
- Inclua testes

---

## Priorização

Use a matriz de priorização:

| Impacto \ Esforço | Baixo | Médio | Alto |
|-------------------|-------|-------|------|
| **Alto** | Fazer Primeiro | Fazer Em Seguida | Planejar |
| **Médio** | Fazer Rápido | Planejar | Avaliar |
| **Baixo** | Não Fazer | Avaliar | Não Fazer |

---

## Recursos

- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/)
- [PSR-12: Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHPUnit Documentation](https://phpunit.de/)
- [Semantic Versioning](https://semver.org/)

---

**Última Atualização:** 2025-11-04
**Mantido por:** Thiago Edson (thiago.cassone@gmail.com)
