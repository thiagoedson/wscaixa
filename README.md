# WSCaixa

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/ac7f2f9d821b4569a09dae3fce38a23a)](https://www.codacy.com/manual/thiagoedson/wscaixa?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=thiagoedson/wscaixa&amp;utm_campaign=Badge_Grade)
[![Version](https://img.shields.io/badge/version-1.1.8-blue.svg)](https://github.com/cassone200/wscaixa)
[![PHP Version](https://img.shields.io/badge/php-%3E5.4-8892BF.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-ISC-green.svg)](LICENSE)

Biblioteca PHP para integração com o webservice da Caixa Econômica Federal para criação, registro e consulta de boletos bancários.

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
