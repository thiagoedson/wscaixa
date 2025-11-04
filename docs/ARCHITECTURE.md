# Arquitetura - WSCaixa

Este documento descreve a arquitetura, design patterns e organização do projeto WSCaixa.

## Índice

- [Visão Geral](#visão-geral)
- [Estrutura do Projeto](#estrutura-do-projeto)
- [Fluxo de Dados](#fluxo-de-dados)
- [Diagrama de Classes](#diagrama-de-classes)
- [Padrões de Design](#padrões-de-design)
- [Integração com Webservice](#integração-com-webservice)
- [Dependências](#dependências)

---

## Visão Geral

WSCaixa é uma biblioteca PHP minimalista que implementa um cliente SOAP para integração com o webservice da Caixa Econômica Federal. A arquitetura foi projetada para ser:

- **Simples:** Sem dependências externas
- **Direta:** API clara e objetiva
- **Leve:** Apenas ~500 linhas de código
- **Extensível:** Fácil de adaptar para novas necessidades

### Princípios de Design

1. **Zero Dependencies:** Usa apenas extensões nativas do PHP
2. **Single Responsibility:** Cada classe tem uma responsabilidade clara
3. **Encapsulation:** Dados sensíveis e lógica interna são privados
4. **Simplicity:** Preferência por soluções simples sobre complexas

---

## Estrutura do Projeto

```
wscaixa/
├── lib/                          # Biblioteca principal
│   └── WSCaixa.php              # Classes principais (WSCaixa + XmlDomConstruct)
├── src/                          # Modelos de dados
│   └── DadosWS.php              # Classe de dados de exemplo
├── docs/                         # Documentação
│   ├── API.md                   # Referência da API
│   ├── ARCHITECTURE.md          # Este arquivo
│   ├── EXAMPLES.md              # Exemplos práticos
│   ├── CONFIGURATION.md         # Guia de configuração
│   ├── SECURITY.md              # Segurança
│   └── IMPROVEMENTS.md          # Roadmap
├── index.php                     # Ponto de entrada para testes
├── composer.json                 # Configuração Composer
└── README.md                     # Documentação principal
```

### Organização por Namespaces

| Namespace | Localização | Propósito |
|-----------|-------------|-----------|
| `WSCaixa\` | `lib/` | Classes principais da biblioteca |
| `DadosWS\` | `src/` | Modelos de dados e exemplos |

---

## Fluxo de Dados

### 1. Registro de Boleto (INCLUI_BOLETO)

```
┌─────────────┐
│   Cliente   │
│ (Seu App)   │
└──────┬──────┘
       │
       │ 1. Instancia WSCaixa($dados)
       ▼
┌─────────────────────────┐
│  WSCaixa::__construct() │
│  - Valida dados         │
│  - Chama _setConfigs()  │
└──────┬──────────────────┘
       │
       │ 2. Configura estrutura
       ▼
┌─────────────────────────┐
│  _setConfigs()          │
│  - Monta array dados    │
│  - Gera hash auth       │
│  - Gera XML SOAP        │
└──────┬──────────────────┘
       │
       │ 3. Chama realizarRegistro()
       ▼
┌─────────────────────────┐
│  realizarRegistro()     │
│  - Prepara cURL         │
│  - Envia XML via POST   │
│  - Recebe resposta      │
└──────┬──────────────────┘
       │
       │ 4. HTTP POST (SOAP XML)
       ▼
┌─────────────────────────┐
│  Webservice Caixa       │
│  (SOAP API)             │
└──────┬──────────────────┘
       │
       │ 5. Resposta SOAP XML
       ▼
┌─────────────────────────┐
│  Parsing Resposta       │
│  - SimpleXMLElement     │
│  - Extrai dados         │
│  - Retorna array        │
└──────┬──────────────────┘
       │
       │ 6. Array resultado
       ▼
┌─────────────┐
│   Cliente   │
│  (Processa) │
└─────────────┘
```

### 2. Consulta de Boleto (CONSULTA_BOLETO)

Fluxo similar ao registro, mas com tipo de operação diferente:

```
Cliente → WSCaixa($dados, null, 'CONSULTA_BOLETO')
       → consultarRegistro()
       → Webservice Caixa
       → Resposta com status do boleto
```

---

## Diagrama de Classes

```
┌─────────────────────────────────────────┐
│           WSCaixa\WSCaixa               │
├─────────────────────────────────────────┤
│ + $urlIntegracao: string                │
│ + $dadosXml: XmlDomConstruct            │
├─────────────────────────────────────────┤
│ + __construct(array, array, string)     │
│ + realizarRegistro(bool, bool): array   │
│ + consultarRegistro(bool, bool): array  │
│ - _setConfigs(array, array, string)     │
│ - _geraHashAutenticacao(array, string)  │
│ - _geraEstruturaXml(array, string)      │
└─────────────────────────────────────────┘
                    │
                    │ usa
                    ▼
┌─────────────────────────────────────────┐
│      WSCaixa\XmlDomConstruct            │
│         extends DOMDocument             │
├─────────────────────────────────────────┤
│ + convertArrayToXml(mixed, DOMElement)  │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│          DadosWS\DadosWS                │
├─────────────────────────────────────────┤
│ + $urlIntegracao                        │
│ + $codigoCedente                        │
│ + $nossoNumero                          │
│ + $dataVencimento                       │
│ + $valorNominal                         │
│ + $cnpj                                 │
│ + ... (outras propriedades)             │
└─────────────────────────────────────────┘
```

---

## Padrões de Design

### 1. Builder Pattern (Implícito)

A classe `WSCaixa` funciona como um builder, construindo progressivamente a requisição SOAP:

```php
// Configuração
$ws = new WSCaixa($dados);  // Constrói estrutura interna

// Execução
$resultado = $ws->realizarRegistro();  // Usa estrutura construída
```

### 2. Data Transfer Object (DTO)

A classe `DadosWS` serve como DTO para transferência de dados:

```php
class DadosWS {
    public $codigoCedente;
    public $nossoNumero;
    // ... outros campos
}
```

### 3. Template Method (Parcial)

Os métodos `realizarRegistro()` e `consultarRegistro()` seguem um template similar:

```php
private function templateMethod() {
    $this->prepararRequisicao();
    $this->enviarRequisicao();
    $this->processarResposta();
    return $this->resultado;
}
```

### 4. Adapter Pattern

`XmlDomConstruct` adapta arrays PHP para DOMDocument XML:

```php
$array = ['CHAVE' => 'valor'];
$xml = new XmlDomConstruct();
$xml->convertArrayToXml($array);
// Resultado: <CHAVE>valor</CHAVE>
```

---

## Integração com Webservice

### Protocolo SOAP

A biblioteca implementa um cliente SOAP customizado usando cURL:

```
┌──────────────┐                    ┌──────────────┐
│   WSCaixa    │     HTTPS/POST     │   Caixa WS   │
│              ├────────────────────►│              │
│              │   SOAP XML         │              │
│              │                    │              │
│              │◄────────────────────┤              │
│              │   SOAP XML         │              │
└──────────────┘                    └──────────────┘
```

### Estrutura da Requisição SOAP

```xml
<?xml version="1.0" encoding="UTF-8"?>
<soapenv:Envelope
    xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/"
    xmlns:cob="http://caixa.gov.br/sibar/manutencao_cobranca_bancaria/boleto/externo">
    <soapenv:Header>
        <wsse:Security>
            <wsse:UsernameToken>
                <wsse:Username>SGCBS02P</wsse:Username>
                <wsse:Password>[HASH_SHA256_BASE64]</wsse:Password>
            </wsse:UsernameToken>
        </wsse:Security>
    </soapenv:Header>
    <soapenv:Body>
        <cob:INCLUI_BOLETO>
            <DADOS>
                <CODIGO_CEDENTE>123456</CODIGO_CEDENTE>
                <NOSSO_NUMERO>14000000000000001</NOSSO_NUMERO>
                <!-- ... outros campos -->
            </DADOS>
        </cob:INCLUI_BOLETO>
    </soapenv:Body>
</soapenv:Envelope>
```

### Autenticação

A autenticação usa **WS-Security** com hash SHA256:

```php
// Para INCLUI_BOLETO
$string = $codigoCedente . $nossoNumero . $dataVencimento . $valor . $cnpj;
$hash = base64_encode(hash('sha256', $string, true));

// Para CONSULTA_BOLETO
$string = $codigoCedente . $nossoNumero . $cnpj;
$hash = base64_encode(hash('sha256', $string, true));
```

---

## Dependências

### Extensões PHP Requeridas

| Extensão | Uso | Métodos Utilizados |
|----------|-----|-------------------|
| **curl** | Requisições HTTP | `curl_init()`, `curl_setopt()`, `curl_exec()` |
| **xml** | Manipulação XML | `DOMDocument`, `DOMElement`, `SimpleXMLElement` |
| **hash** | Criptografia | `hash()` |
| **json** | Serialização | `json_encode()`, `json_decode()` |

### Bibliotecas Nativas PHP

- `preg_replace()` - Regex para limpeza de dados
- `strtotime()` / `date()` - Manipulação de datas
- `str_pad()` - Formatação de strings
- `base64_encode()` - Encoding

---

## Configuração de Ambientes

### Produção

```php
$config = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'usuarioServico' => 'SGCBS02P'
];
```

### Homologação

```php
$config = [
    'urlIntegracao' => 'https://des.barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'usuarioServico' => 'SGCBS01D'
];
```

---

## Ciclo de Vida da Requisição

### 1. Inicialização

```php
$ws = new WSCaixa($dados);
```
- Valida dados obrigatórios
- Chama `_setConfigs()`
- Gera hash de autenticação
- Constrói XML SOAP

### 2. Execução

```php
$resultado = $ws->realizarRegistro();
```
- Configura cURL com SSL
- Envia POST com XML
- Aguarda resposta (timeout: padrão cURL)

### 3. Processamento

```php
// Interno
$xml = new SimpleXMLElement($response);
$dados = $this->parseResponse($xml);
return $dados;
```
- Parse do XML de resposta
- Extração de dados relevantes
- Conversão para array PHP

### 4. Retorno

```php
[
    'COD_RETORNO' => '0',
    'MENSAGEM' => 'SUCESSO',
    'NOSSO_NUMERO' => '14...',
    // ... outros campos
]
```

---

## Performance e Escalabilidade

### Métricas Típicas

- **Tempo de Resposta:** 1-3 segundos (depende da Caixa)
- **Memória:** ~2-5 MB por requisição
- **Concorrência:** Limitada pelo webservice da Caixa

### Otimizações Possíveis

1. **Cache de Consultas:** Cachear resultados de `consultarRegistro()`
2. **Pool de Conexões:** Reutilizar conexões cURL
3. **Async Requests:** Usar cURL multi para requisições paralelas
4. **Retry Logic:** Implementar tentativas automáticas em falhas

### Limitações

- **Sem Pool de Conexões:** Cada requisição cria nova conexão
- **Sem Timeout Configurável:** Usa timeout padrão do cURL
- **Sem Retry Automático:** Falhas requerem intervenção manual
- **Síncrono:** Bloqueia até resposta completa

---

## Segurança

### Implementado

✅ Hash SHA256 para autenticação
✅ Base64 encoding de credenciais
✅ HTTPS para transporte

### Não Implementado (Melhorias Propostas)

⚠️ Verificação de certificado SSL (desabilitada)
⚠️ Validação de dados de entrada
⚠️ Sanitização de campos
⚠️ Rate limiting
⚠️ Logging de segurança

Ver [SECURITY.md](SECURITY.md) para detalhes.

---

## Extensibilidade

### Como Adicionar Novas Operações

Para adicionar uma nova operação (ex: `ALTERA_BOLETO`):

1. **Adicionar método público:**
```php
public function alterarBoleto($debug = false, $xml = false) {
    return $this->_executarOperacao('ALTERA_BOLETO', $debug, $xml);
}
```

2. **Atualizar `_geraHashAutenticacao()`:**
```php
private function _geraHashAutenticacao($dados, $tipo) {
    if ($tipo == 'ALTERA_BOLETO') {
        // Lógica específica
    }
}
```

3. **Atualizar `_geraEstruturaXml()`:**
```php
private function _geraEstruturaXml($dados, $tipo) {
    if ($tipo == 'ALTERA_BOLETO') {
        // Estrutura XML específica
    }
}
```

---

## Testing Strategy

### Testes Recomendados

1. **Unit Tests:**
   - Geração de hash
   - Conversão array → XML
   - Parsing de respostas

2. **Integration Tests:**
   - Comunicação com ambiente de homologação
   - Fluxo completo de registro
   - Fluxo completo de consulta

3. **End-to-End Tests:**
   - Cenários reais com dados válidos
   - Tratamento de erros
   - Edge cases

---

## Próximos Passos

- Ver [IMPROVEMENTS.md](IMPROVEMENTS.md) para roadmap completo
- Consultar [API.md](API.md) para detalhes dos métodos
- Ler [SECURITY.md](SECURITY.md) para hardening de segurança
