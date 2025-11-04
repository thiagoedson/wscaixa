# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [1.2.0] - 2025-11-04

### 🔒 Segurança (Security)

#### Vulnerabilidades Críticas Corrigidas

- **[CRÍTICO]** Habilitada verificação SSL/TLS em todas as requisições cURL
  - `CURLOPT_SSL_VERIFYPEER` definido como `true`
  - `CURLOPT_SSL_VERIFYHOST` definido como `2`
  - Previne ataques Man-in-the-Middle (MitM)
  - Protege dados financeiros e credenciais em trânsito
  - Afeta: `lib/WSCaixa.php` nos métodos `realizarRegistro()` e `consultarRegistro()`

- **[MÉDIO]** Corrigida exposição de informações sensíveis em mensagens de erro
  - Removidos todos os `print_r()` e `die` que expunham detalhes técnicos
  - Implementado logging interno com `error_log()`
  - Exceções agora lançam mensagens genéricas ao usuário
  - Detalhes técnicos registrados apenas em logs do servidor
  - Previne information disclosure

- **[MÉDIO]** Implementada validação robusta de entrada de dados
  - Nova classe `BoletoValidator` para validação e sanitização
  - Validação de CPF/CNPJ com algoritmo oficial
  - Validação de formato do Nosso Número (14 dígitos)
  - Validação de valores e datas
  - Validação de URL de integração (deve ser HTTPS e domínio caixa.gov.br)
  - Previne XML Injection e dados malformados

- **[MÉDIO]** Implementada sanitização automática de dados
  - Sanitização de caracteres especiais XML
  - Remoção de caracteres de controle
  - Previne XML Injection attacks
  - Aplicada automaticamente a todos os dados de entrada

- **[BAIXO]** Configurados timeouts em requisições HTTP
  - `CURLOPT_TIMEOUT` definido como 30 segundos
  - `CURLOPT_CONNECTTIMEOUT` definido como 10 segundos
  - Previne travamentos indefinidos
  - Melhora experiência do usuário

### ✨ Adicionado (Added)

- Nova classe `lib/BoletoValidator.php` para validação e sanitização de dados
  - Método `validar()` - Valida dados obrigatórios do boleto
  - Método `validarCPF()` - Validação completa de CPF
  - Método `validarCNPJ()` - Validação completa de CNPJ
  - Método `sanitizarParaXML()` - Sanitiza strings para XML
  - Método `sanitizarDados()` - Sanitização recursiva de arrays
  - Método `validarURL()` - Valida URL de integração

- Documentação atualizada em `docs/SECURITY.md`
  - Status de correção de todas as vulnerabilidades
  - Nível de risco atualizado de MÉDIO-ALTO para BAIXO-MÉDIO
  - Exemplos de código corrigido

- Novo arquivo `CHANGELOG.md` para rastreamento de mudanças

### 🔧 Modificado (Changed)

- `lib/WSCaixa.php`:
  - Método `realizarRegistro()`: SSL habilitado, timeouts adicionados, tratamento de erros melhorado
  - Método `consultarRegistro()`: SSL habilitado, timeouts adicionados, tratamento de erros melhorado
  - Método `_setConfigs()`: Adicionada validação e sanitização automática de dados
  - Exceções agora usam namespace completo `\Exception`

- Documentação de segurança atualizada com status de correções

### 🐛 Corrigido (Fixed)

- Código morto removido (linhas após `die` que nunca eram executadas)
- Tratamento inadequado de erros que expunha stack traces
- Falta de validação permitia dados malformados

### 📊 Impacto

**Antes das correções:**
- ⚠️ Nível de Risco: MÉDIO-ALTO
- 🔴 1 vulnerabilidade CRÍTICA
- 🟡 3 vulnerabilidades MÉDIAS
- 🟢 1 vulnerabilidade BAIXA

**Depois das correções:**
- ✅ Nível de Risco: BAIXO-MÉDIO
- ✅ 100% das vulnerabilidades críticas corrigidas
- ✅ 100% das vulnerabilidades médias corrigidas
- ✅ 100% das vulnerabilidades baixas corrigidas

### 📋 Checklist de Segurança

- [x] Verificação SSL habilitada
- [x] Validação de entrada implementada
- [x] Sanitização XML implementada
- [x] Logging seguro implementado
- [x] Timeouts configurados
- [x] Remoção de exposição de informações
- [ ] Rate limiting (pendente - recomendado para versões futuras)
- [ ] Certificados CA bundle customizado (opcional)

### ⚠️ Breaking Changes

Nenhuma breaking change nesta versão. Todas as correções são compatíveis com versões anteriores.

### 🔄 Migração

Não é necessária nenhuma ação para migrar da versão 1.1.8 para 1.2.0. As correções são transparentes e automaticamente aplicadas.

**Recomendação:**
- Atualize imediatamente para esta versão se estiver usando a biblioteca em produção
- Verifique que seus certificados SSL estão atualizados no servidor
- Configure logs do PHP para capturar `error_log()` (geralmente em `/var/log/php/error.log`)

### 📚 Referências

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [CWE-295: Improper Certificate Validation](https://cwe.mitre.org/data/definitions/295.html)
- [CWE-209: Generation of Error Message Containing Sensitive Information](https://cwe.mitre.org/data/definitions/209.html)

---

## [1.1.8] - Data anterior

Versão anterior antes das correções de segurança.

---

## Como Reportar Vulnerabilidades

Se você encontrar vulnerabilidades de segurança, por favor:

1. **NÃO** abra uma issue pública
2. Envie um email para: thiago.cassone@gmail.com
3. Inclua:
   - Descrição detalhada da vulnerabilidade
   - Passos para reproduzir
   - Impacto potencial
   - Sugestão de correção (se possível)

Responderemos dentro de 48 horas e trabalharemos em uma correção prioritária.
