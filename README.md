# Meu Horario

Aplicacao Laravel/Filament para gestao de horarios escolares.

## Stack

- PHP `^8.2`
- Laravel `^12.0`
- Filament `^3.3`
- Livewire/Volt e Flux
- Spatie Permissions via `althinect/filament-spatie-roles-permissions`
- Vite `^6.4`
- Tailwind CSS `^4.3`

## Modulos Principais

- Docentes, alunos, utilizadores e permissoes.
- Cursos, turmas, disciplinas, salas, polos/nucleos e departamentos.
- Marcacao de horarios por docente, disciplina, turma, aluno, sala, dia e hora.
- Pedidos de troca, conflitos e estados de aprovacao.
- Vista semanal do professor.
- Vista de horario sobreposto para administracao e coordenacao de departamento.
- Pagina `About` com informacao tecnica do sistema.

## Regras Funcionais Importantes

- O painel Filament esta em `/meuhorario`.
- A home do professor usa dashboard em uma coluna, com widgets empilhados.
- A pagina `About` deve estar disponivel apenas para `Super Admin`.
- O cargo de coordenador nao e uma role Spatie; vem da relacao `teacher_positions` com `positions`.
- O `Horario (sobreposto)` deve estar disponivel para:
  - `Super Admin`.
  - Docente que tenha cargo de coordenador no ano letivo ativo e tenha departamento definido.
- Na vista sobreposta, o coordenador so deve ver docentes:
  - Do seu proprio departamento.
  - Com horarios no ano letivo ativo.
  - Com horarios em estado `Aprovado` ou `Aprovado DP`.
- Professores sem cargo de coordenador nao devem ver a vista de horario sobreposto.

## Comandos Uteis

Instalar dependencias PHP:

```bash
composer install
```

Instalar dependencias JavaScript:

```bash
npm install
```

Executar ambiente de desenvolvimento:

```bash
composer run dev
```

Executar testes:

```bash
php artisan test
```

Verificar vulnerabilidades PHP:

```bash
composer audit
```

Verificar vulnerabilidades npm:

```bash
npm audit
```

Limpar caches comuns:

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

Nota: `php artisan optimize:clear` pode falhar fora do ambiente Docker se a cache estiver configurada para usar MariaDB no host `db`.

## Seguranca

- Nunca versionar `.env`; apenas `.env.example` deve estar no Git.
- Confirmar que `APP_DEBUG=false` em producao.
- Rever regularmente `composer audit` e `npm audit`.
- Validar permissoes Filament para paginas sensiveis, incluindo acesso direto por URL.
- Rever fluxos de impersonate, uploads, exportacoes e pedidos de troca.
- Confirmar que rotas administrativas exigem autenticacao e autorizacao adequada.

## Documentacao de Alteracoes

As alteracoes funcionais relevantes devem ser registadas em `CHANGELOG.md`.

## Contexto Para Proximas Sessoes

- A branch principal de trabalho e `main`.
- A antiga branch `verHorariosAdmin` foi integrada em `main` e removida localmente e no GitHub.
- Antes de iniciar novo trabalho, executar `git fetch origin` e confirmar `git status -sb`.
- Ler este ficheiro e `CHANGELOG.md` para recuperar regras funcionais e decisoes recentes.
- Existem ficheiros locais nao versionados que nao devem ser apagados sem revisao:
  - `app/Filament/Resources/AboutSystemResource/`
  - `resources/views/filament/resources/`
  - `resources/js/`
  - `report.json`
