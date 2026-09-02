# State of the Art do Projeto

Data: 2026-09-02
Escopo: avaliacao global estatica do repositorio atual.

## Resumo executivo

O projeto e uma aplicacao Laravel 12 com Filament 3, Livewire/Volt e Tailwind,
orientada a gestao academica, professores, alunos, matriculas, horarios,
pedidos de alteracao, anos letivos e administracao. A base funcional e ampla e
tem bons controlos recentes de MFA, ativacao de contas, impersonation e
historico de anos letivos.

A maturidade estimada e 2.5-3/5. O principal problema nao e falta de
funcionalidade, mas a ausencia de contratos de dominio centralizados, testes de
integracao para o dominio academico e validacao operacional automatizada.

Nao e possivel declarar o projeto pronto para producao/importacao sem resolver
os itens criticos e altos abaixo. A avaliacao foi estatica; PHP nao esta
disponivel no host. O ambiente DDEV foi identificado como disponivel, mas os
testes ainda nao foram executados.

## Arquitetura atual

- `app/Models`: entidades academicas e administrativas, com concerns para
  isolamento por ano letivo.
- `app/Filament/Resources`: CRUDs, tabelas, formularios, actions e scopes de
  acesso.
- `app/Filament/Pages` e `Widgets`: operacoes administrativas, MFA, horarios e
  dashboards.
- `app/Services`: ativacao de utilizadores e composicao de calendarios.
- `app/Policies` + `Gate::before`: autorizacao por permissao, ownership,
  historico e modo de leitura durante impersonation.
- `database/migrations`: schema evolutivo com ajustes recentes para MFA,
  pedidos, anos letivos, matriculas e ativacao.
- `tests/Feature`: cobertura razoavel de autenticacao e seguranca, mas fraca no
  dominio academico e quase inexistente nos importadores.

## Pontos fortes confirmados

- MFA e aplicado no painel e revalidado apos login.
- Impersonation exige privilegio elevado, MFA e modo read-only.
- Tokens de ativacao sao aleatorios, armazenados com hash, expiram e sao
  invalidados apos uso.
- Login aplica conta ativa, rate limiting e regeneracao de sessao.
- Existe isolamento por ano letivo em varios recursos de horarios.
- O ano letivo ativo tem protecao aplicacional e constraint de base de dados
  para evitar mais de um ativo.
- A selecao de turnos valida servidor-side contexto, estado, capacidade e ano
  letivo dentro de transacao.
- Existem policies para a maioria dos modelos e testes de seguranca recentes.
- O importador de disciplinas usa Eloquent e valida campos de input; o novo
  campo `student_can_enroll` esta em `$fillable`.

## Backlog priorizado

### P0 - bloquear antes de importar/publicar

#### SOT-001 - Contrato canonico de `Subject.type`

Evidencia:

- `database/migrations/2025_06_10_202640_create_subjects_table.php:18` usa
  `Nao Letiva`.
- `app/Filament/Imports/SubjectImporter.php:44-62` usa `Não letiva`.
- `app/Filament/Resources/SubjectResource.php:53-60` usa `Não Letiva`.

Impacto: disciplinas nao letivas podem falhar ao gravar ou ficar com valores
inconsistentes. Decidir primeiro o valor canonico; depois alinhar schema,
importador, formulario e dados existentes.

#### SOT-002 - Autorizar actions de ativacao de utilizadores

Evidencia: `app/Filament/Resources/UserResource.php:145-205`.

As actions `activationCode`, `sendActivation` e `exportActivationCodes` nao
fazem autorizacao server-side especifica. `visible()` nao e uma fronteira de
seguranca.

Impacto: emissao, envio ou exportacao de tokens que permitem ativar contas e
definir passwords. Exigir policy/permission dentro de cada action e bulk
action, idealmente com uma permission dedicada.

### P1 - corrigir antes da proxima evolucao significativa

#### SOT-003 - Decidir compatibilidade de CSV de disciplinas

`student_can_enroll` esta em `requiredMapping()` e `'required'` em
`SubjectImporter.php:75-84`. CSVs antigos sem a coluna deixam de funcionar.
Decidir entre coluna obrigatoria/versionamento de template ou default seguro.

#### SOT-004 - Casts booleanos de `Subject`

`app/Models/Subject.php:10-16` nao define casts para `status` e
`student_can_enroll`. Adicionar casts e testar valores retornados como bool.

#### SOT-005 - Corrigir pedidos de alteracao de horarios

- `app/Policies/ScheduleRequestPolicy.php:14-49` autoriza por permission sem
  confirmar requerente, destinatario ou gestor.
- `app/Filament/Pages/EditScheduleRequest.php:34-42,364-367` usa ID fixo
  `[1]` para identificar gestor.
- Transicoes nao mostram lock/versionamento para evitar corrida entre
  aprovacao, rejeicao e eliminacao.

Confirmar o escopo no Resource e reforcar ownership/estado no servidor.

#### SOT-006 - Corrigir selecao de ano anterior

`app/Filament/Resources/ScheduleResource.php:417-428` assume `id - 1` para
encontrar o ano anterior. Usar ordenacao por ano/data, nao por continuidade de
IDs.

#### SOT-007 - Corrigir validacao e rollback de datas

- `app/Filament/Resources/SchoolYearResource.php:115-150` compara campos de
  inscricao com campos de horarios docentes.
- `database/migrations/2025_09_04_115215_add__registration_dates_to_schoolyears.php:23-28`
  nao remove no `down()` as colunas adicionadas.

#### SOT-008 - Corrigir relacoes potencialmente quebradas

- `app/Models/SchoolYear.php:52-62`: `students()` aparenta atravessar
  `CourseSubject` com chaves incoerentes em vez de matriculas.
- `app/Models/Schedule.php:23-26`: `subjects()` usa pivot
  `teacher_subjects`, que nao representa uma schedule.
- `app/Models/Room.php`: `isAvailableFor()` aparenta usar colunas diferentes
  das presentes no schema de schedules.

Confirmar uso e corrigir ou remover relacoes mortas. Os findings sao bugs
latentes/confirmados por estrutura, mas requerem testes de execucao.

#### SOT-009 - Criar testes de dominio academico

Nao existem testes dedicados para `SubjectImporter`, conflitos de horarios,
isolamento de pedidos por docente, ano ativo via UI ou maioria dos imports.
Adicionar testes para imports, policies, transicoes de pedidos, turnos,
conflitos de docente/sala/turma e duplicacao de matriculas/pivots.

#### SOT-010 - Criar CI

Nao foi encontrado `.github/workflows`. Configurar pipeline de PR com install,
lint e testes; incluir verificacao de migrations e, quando necessario, um
servico MariaDB para validar constraints nao reproduzidas por SQLite.

### P2 - consolidar qualidade e manutencao

- Adicionar unicidade composta aos pivots `course_subjects` e
  `teacher_subjects`, apos deduplicacao controlada.
- Adicionar unique em `registrations_subjects` para impedir duplicacao de
  disciplinas por matricula.
- Corrigir a regra de existencia de pelo menos um ano ativo, se o dominio a
  exigir.
- Alinhar limite do acronimo: schema 30 versus importador 255.
- Remover codigo comentado/dead code em `RegistrationSubject.php` e
  `Subject.php` depois de confirmar que nao e requisito futuro.
- Rever `TeacherObserver`, atualmente desativado e com inconsistencias
  aparentes entre `email`, `user_id` e `id_user`.
- Reduzir introspecao repetida de schema em
  `MergedScheduleCalendarService.php` e investigar N+1 em contadores/recursos.
- Neutralizar formula injection em todas as exportacoes CSV, especialmente
  `UserResource.php:187-204`.

## Operacao e validacao

- `git diff --check`: passou.
- `php -l` e PHPUnit: nao executados; `php` nao existe no host.
- `StudentImporter.php`: lint e Pint passaram via DDEV.
- Foi adicionado `tests/Feature/StudentImporterTest.php`, cobrindo datas,
  generos por nome e email vazio.
- A limitacao turma-edificio foi corrigida: o edificio deixou de ser atributo
  de `classes`; a localizacao canonica e `schedules.id_room` ->
  `rooms.id_building`. A migration `2026_09_02_130000...` foi aplicada no
  banco DDEV de testes.
- A regra e parametrizavel turma a turma: a pivot `class_buildings` guarda os
  edificios permitidos de cada turma. O filtro e a validacao server-side
  exigem que a sala esteja num edificio permitido por todas as turmas do
  horario.
- `ScheduleStudentAssignmentTest`: 6 testes passaram, incluindo uma turma com
  horarios em dois edificios distintos.
- Suite completa via DDEV: 85 testes passaram e 1 falhou (`DashboardTest`,
  resposta 302 em vez de 200 para utilizador autenticado sem role). Esta falha
  nao esta relacionada com o importador e fica como item de triagem.
- O worker DDEV foi reiniciado e confirmado como `RUNNING`; os jobs antigos
  falhados antes de corrigir `id_buildings` foram limpos no banco de testes.
- O importador de turmas aceita `id_edificio` (cabecalho usado no CSV) e
  `id_buildings` como alias; os jobs/failures antigos foram limpos no banco de
  testes sem serem repetidos.
- Nao foram feitas outras alteracoes de codigo durante a auditoria global.
- Estado pendente: `AGENTS.md`, `SubjectImporter.php` e ficheiros de
  continuidade nao staged/untracked.
- O relatorio e este ficheiro; o checkpoint curto aponta para ele.

## Ordem recomendada de evolucao

1. Decidir e corrigir `SOT-001` e `SOT-003`.
2. Corrigir autorizacao `SOT-002` e pedidos `SOT-005`.
3. Executar baseline em DDEV e criar testes de regressao `SOT-009`.
4. Corrigir relacoes, datas e constraints `SOT-006` a `SOT-008`.
5. Criar CI e endurecer operacao `SOT-010`.
6. Tratar P2 por lotes pequenos, sempre com testes.

## Limites da avaliacao

Os agentes fizeram auditorias estaticas dirigidas e atingiram limites de
exploracao em algumas areas. Nao e uma certificacao formal nem substitui
testes dinamicos, revisao de schema real, verificacao de dados de producao ou
teste de autorizacao com utilizadores reais.
