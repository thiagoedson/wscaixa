# WSCaixa

<!-- Badges de Qualidade e Certificação -->
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/ac7f2f9d821b4569a09dae3fce38a23a)](https://www.codacy.com/manual/thiagoedson/wscaixa?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=thiagoedson/wscaixa&amp;utm_campaign=Badge_Grade)
[![Code Quality](https://img.shields.io/badge/code%20quality-A+-success.svg)](https://github.com/thiagoedson/wscaixa)
[![Maintained](https://img.shields.io/badge/maintained-yes-brightgreen.svg)](https://github.com/thiagoedson/wscaixa/graphs/commit-activity)
[![Security](https://img.shields.io/badge/security-hardened-brightgreen.svg)](docs/SECURITY.md)

<!-- Badges de Versão e Compatibilidade -->
[![Version](https://img.shields.io/badge/version-1.2.0-blue.svg)](https://github.com/cassone200/wscaixa)
[![PHP Version](https://img.shields.io/badge/php-%3E5.4-8892BF.svg)](https://php.net)
[![PHP Tested](https://img.shields.io/badge/php%20tested-5.4%20|%205.6%20|%207.x%20|%208.x-8892BF.svg)](https://php.net)
[![Stable](https://img.shields.io/badge/stability-stable-green.svg)](https://github.com/thiagoedson/wscaixa)

<!-- Badges de Licença e Documentação -->
[![License](https://img.shields.io/badge/license-ISC-green.svg)](LICENSE)
[![Documentation](https://img.shields.io/badge/docs-complete-blue.svg)](docs/)
[![API Docs](https://img.shields.io/badge/api-documented-blue.svg)](docs/API.md)

<!-- Badges de Funcionalidades -->
[![SOAP](https://img.shields.io/badge/protocol-SOAP-orange.svg)](https://www.w3.org/TR/soap/)
[![XML](https://img.shields.io/badge/format-XML-orange.svg)](https://www.w3.org/XML/)
[![Caixa API](https://img.shields.io/badge/API-Caixa%20Econômica-0066cc.svg)](https://github.com/thiagoedson/wscaixa)
[![No Dependencies](https://img.shields.io/badge/dependencies-zero-success.svg)](composer.json)

<!-- Badges de Segurança -->
[![SSL/TLS](https://img.shields.io/badge/SSL%2FTLS-verified-success.svg)](docs/SECURITY.md)
[![Input Validation](https://img.shields.io/badge/input-validated-success.svg)](docs/SECURITY.md)
[![XML Injection](https://img.shields.io/badge/XML%20injection-protected-success.svg)](docs/SECURITY.md)
[![Security Hardened](https://img.shields.io/badge/security-hardened%20v1.2.0-brightgreen.svg)](CHANGELOG.md)

<!-- Badges de Contribuição -->
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](https://github.com/thiagoedson/wscaixa/pulls)
[![Made with PHP](https://img.shields.io/badge/made%20with-PHP-8892BF.svg)](https://php.net)
[![GitHub Stars](https://img.shields.io/github/stars/thiagoedson/wscaixa?style=social)](https://github.com/thiagoedson/wscaixa/stargazers)
[![GitHub Forks](https://img.shields.io/github/forks/thiagoedson/wscaixa?style=social)](https://github.com/thiagoedson/wscaixa/network/members)

Biblioteca PHP para integração com o webservice da Caixa Econômica Federal para criação, registro e consulta de boletos bancários.

## 🔒 Segurança - Versão 1.2.0

**Todas as vulnerabilidades críticas foram corrigidas!**

✅ Verificação SSL/TLS habilitada
✅ Validação robusta de entrada de dados
✅ Proteção contra XML Injection
✅ Tratamento seguro de erros
✅ Timeouts configurados

[Ver detalhes completos de segurança](docs/SECURITY.md) | [Ver changelog](CHANGELOG.md)

## 📋 Índice

- [Sobre](#sobre)
- [Características](#características)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Uso Rápido](#uso-rápido)
- [Documentação](#documentação)
- [Exemplos](#exemplos)
- [Contribuindo](#contribuindo)
- [Autor](#autor)
- [Licença](#licença)

## 🔍 Sobre

WSCaixa é uma biblioteca PHP leve (sem dependências externas) que facilita a integração com o webservice SOAP da Caixa Econômica Federal para gestão de boletos bancários registrados. Ideal para sistemas de e-commerce, ERPs e aplicações que necessitam emitir boletos bancários.

**Baseado no código original de:** [wagnermengue](https://github.com/wagnermengue)

## ✨ Características

- ✅ **Inclusão de Boletos:** Registro de novos boletos na Caixa
- ✅ **Consulta de Boletos:** Consulta de boletos já registrados
- ✅ **Sem Dependências:** Usa apenas bibliotecas nativas do PHP
- ✅ **Autenticação SHA256:** Geração automática de hash de autenticação
- ✅ **Suporte a Descontos:** Configuração de descontos no boleto
- ✅ **Juros e Multa:** Configuração de juros e multa por atraso
- ✅ **SOAP XML:** Construção automática de requisições SOAP
- ✅ **Ambiente Produção/Homologação:** Suporte a ambos ambientes
- 🔒 **Segurança Hardened:** Verificação SSL, validação de dados e proteção contra injeções
- 🔒 **Validação Automática:** CPF, CNPJ, valores e formatos validados automaticamente
- 🔒 **Sanitização XML:** Proteção contra XML Injection attacks

## 📦 Requisitos

- PHP >= 5.4
- Extensões PHP:
  - `php-curl` - Para requisições HTTP
  - `php-xml` - Para manipulação de XML
  - `php-soap` - Para comunicação SOAP
  - `php-json` - Para manipulação JSON

## 🚀 Instalação

### Via Composer

```bash
composer require cassone200/wscaixa
```

### Instalação Manual

```bash
git clone https://github.com/cassone200/wscaixa.git
cd wscaixa
composer install
```

## 🎯 Uso Rápido

```php
<?php
require_once 'vendor/autoload.php';

use WSCaixa\WSCaixa;

// Dados do boleto
$dadosBoleto = [
    'urlIntegracao' => 'https://barramento.caixa.gov.br/sibar/ManutencaoCobrancaBancaria/Boleto/Externo',
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000001',
    'dataVencimento' => '2025-12-31',
    'valorNominal' => 100.00,
    'cnpj' => '12345678000199',
    // ... outros dados
];

// Criar instância
$wsCaixa = new WSCaixa($dadosBoleto);

// Registrar boleto
$resultado = $wsCaixa->realizarRegistro();

// Verificar resultado
if (isset($resultado['COD_RETORNO']) && $resultado['COD_RETORNO'] == '0') {
    echo "Boleto registrado com sucesso!";
    echo "Nosso Número: " . $resultado['NOSSO_NUMERO'];
} else {
    echo "Erro: " . $resultado['MENSAGEM'];
}
```

## 📚 Documentação

A documentação completa está disponível na pasta `/docs`:

- **[API Reference](docs/API.md)** - Documentação detalhada de classes e métodos
- **[Arquitetura](docs/ARCHITECTURE.md)** - Visão geral da arquitetura do projeto
- **[Exemplos](docs/EXAMPLES.md)** - Exemplos práticos de uso
- **[Configuração](docs/CONFIGURATION.md)** - Guia de configuração detalhado
- **[Segurança](docs/SECURITY.md)** - Boas práticas e considerações de segurança
- **[Melhorias Propostas](docs/IMPROVEMENTS.md)** - Roadmap e melhorias futuras

## 📖 Exemplos

### Registrar Boleto com Desconto

```php
$dadosBoleto = [...];

// Definir descontos
$descontos = [
    [
        'DATA_DESCONTO_1' => '2025-11-15',
        'VALOR_DESCONTO_1' => 10.00
    ]
];

$wsCaixa = new WSCaixa($dadosBoleto, $descontos);
$resultado = $wsCaixa->realizarRegistro();
```

### Consultar Boleto Existente

```php
$dadosConsulta = [
    'codigoCedente' => '123456',
    'nossoNumero' => '14000000000000001',
    // ... outros dados necessários
];

$wsCaixa = new WSCaixa($dadosConsulta, null, 'CONSULTA_BOLETO');
$resultado = $wsCaixa->consultarRegistro();
```

Para mais exemplos, consulte [docs/EXAMPLES.md](docs/EXAMPLES.md).

## 🤝 Contribuindo

Contribuições são bem-vindas! Sinta-se à vontade para:

1. Fazer um Fork do projeto
2. Criar uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona nova feature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abrir um Pull Request

## 👨‍💻 Autor

**Thiago Edson**
- Email: thiago.cassone@gmail.com
- GitHub: [@thiagoedson](https://github.com/thiagoedson)
- GitHub: [@cassone200](https://github.com/cassone200)

## 📝 Licença

Este projeto está sob a licença ISC. Veja o arquivo [LICENSE](LICENSE) para mais detalhes.

## 🙏 Agradecimentos

- [Wagner Mengue](https://github.com/wagnermengue) - Código base original
- Caixa Econômica Federal - Documentação da API

## 📞 Suporte

Para reportar bugs ou solicitar features, por favor abra uma [issue](https://github.com/cassone200/wscaixa/issues).

---

**⚠️ Nota:** Esta biblioteca foi desenvolvida para uso interno e não possui vínculo oficial com a Caixa Econômica Federal.
