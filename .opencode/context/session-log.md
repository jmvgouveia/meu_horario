# Session Log

This file contains a lightweight operational history of meaningful work sessions.

Keep entries short.

Do not copy conversations.

Do not copy full command outputs.

Do not copy complete diffs.

Do not record trivial interactions.

---

## Entry Format

### YYYY-MM-DD HH:MM

Worked on:

- Main task or area.

Changed:

- Meaningful changes completed.

Discovered:

- Important findings, if any.

Validated:

- Relevant tests or checks performed.

Remaining:

- Relevant unfinished work.

Checkpoint:

- `current-state.md` updated.

---

## Sessions

### 2026-09-03

Worked on:

- Decisão de preparação para produção e limitação temporária de inscrições
  duplicadas no mesmo horário.

Changed:

- Registada a decisão `DEC-001`.

Discovered:

- A distinção correta exige usar a identidade de `registration`, não apenas
  `student`, mas foi adiada para depois do lançamento.

Validated:

- Auditorias de arquitetura, segurança e qualidade concluídas.
- Suite completa: 96 testes passaram e 336 asserções.
- Lint PHP e `git diff --check` passaram nos ficheiros críticos.

Remaining:

- Executar testes manuais de aceitação e preparar o deploy.
- Manter a limitação `DEC-001` documentada para correção posterior.

Checkpoint:

- `current-state.md` atualizado.
