# DRSP Access — Sistema de Gestão de Processos CEBAS

Sistema interno em Laravel para substituir gradualmente o uso operacional do banco Microsoft Access (`CGCEB v.03_be.accdb`) no departamento DRSP/CGCEB.

## Contexto

O departamento utilizava o arquivo Access como base operacional para acompanhamento de processos de certificação CEBAS (Certificação de Entidades Beneficentes de Assistência Social). Com ~23 mil processos e 173 campos na tabela principal, o Access não supria mais a demanda por uso simultâneo, rastreabilidade e relatórios. Este sistema substitui gradualmente essas operações.

## Requisitos

- PHP 8.2+
- Node.js (para assets)
- Banco de dados compatível (SQLite para desenvolvimento local; MySQL/MariaDB para produção)
- XAMPP ou servidor Apache equivalente

## Instalação

```bash
composer run setup
```

O comando instala dependências PHP e Node, copia `.env.example` para `.env`, gera a chave da aplicação, executa as migrações e compila os assets.

## Desenvolvimento

```bash
composer run dev
```

Inicia em paralelo: servidor PHP, queue, log viewer (Pail) e Vite para hot-reload de assets.

## Testes

```bash
composer run test

# Rodar um único teste
php artisan config:clear && php artisan test --filter NomeDoTeste
```

## Lint

```bash
./vendor/bin/pint
```

## Módulos

### Base Externa (`/base-externa`)

Fluxo principal de análise de processos CEBAS:

- **Inserir Processo** — cadastra novos registros na base (permissão 1 ou 2)
- **Análise de Processo** — busca e edição completa dos campos do processo
- **Parecer Técnico** — edição e geração de PDF do parecer, com log de alterações
- **Nota Técnica** — edição e geração de PDF da nota técnica, com log de alterações
- **Manifestação** — edição e geração de PDF da manifestação

A legislação aplicável (Lei 12.101/2009 ou LC 187/2021) é determinada automaticamente pela data do protocolo.

### Dashboard (`/dashboard`)

Painel de consulta pública da base CEBAS (VisData), com busca por entidade/CNPJ/município, totais por estado e download em XLS por UF ou completo.

### Coordenação (`/coordenacao`)

Restrito a usuários com permissão 1 ou 2:

- **Automações** — geração de planilha CNEAS
- **Planilhas** — importação da VisData-CEBAS e da Base Externa (importa arquivos `.xls` exportados do Access)

## Permissões

Controladas pelo campo `permission` do usuário:

| Valor | Acesso |
|-------|--------|
| `1` ou `2` | Base Externa completa + Coordenação |
| Outros | Dashboard e análise de processos (somente leitura) |

## Banco de dados

A tabela principal é `processos_sei`, que espelha a `tblProcessos` do Access. Os nomes de coluna são mantidos em MAIÚSCULAS por compatibilidade com os dados legados importados. As migrações usam estratégia "ensure" — adicionam colunas sem destruir dados existentes.

Logs de alterações de parecer e nota técnica são gravados na tabela `logs` em formato JSON.

## Stack

- **Backend**: Laravel 12, PHP 8.2
- **Frontend**: Tailwind CSS 4, Vite
- **PDF**: barryvdh/laravel-dompdf
- **Banco**: SQLite (dev) / MySQL ou MariaDB (produção recomendada)
