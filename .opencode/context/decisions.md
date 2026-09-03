# Project Decisions

This file contains significant architectural, domain and implementation decisions that may affect future work.

Do not use this file for ordinary progress tracking.

Do not record trivial implementation choices.

## Decision Format

Use:

### DEC-XXX — Short title

Date: YYYY-MM-DD

Status: Proposed | Active | Superseded | Rejected

Decision:

Describe the decision concisely.

Reason:

Describe why the decision was made when known.

Consequences:

- Relevant consequence.
- Relevant constraint.

Supersedes:

DEC-XXX, when applicable.

---

## Active Decisions

### DEC-001 — Adiar distinção de inscrições duplicadas no horário

Date: 2026-09-03

Status: Active

Decision:

Para o lançamento em produção, aceitar temporariamente a limitação de o mesmo
aluno não poder ser distinguido entre duas inscrições da mesma disciplina em
turmas/cursos diferentes no mesmo horário.

Reason:

O lançamento está previsto para os próximos dias e a alteração correta exige
uma mudança estrutural na associação entre horário e inscrição.

Consequences:

- Não adicionar agora `id_registration` a `schedules_students`.
- Alunos diferentes em turmas diferentes continuam a ser suportados.
- A distinção por inscrição será tratada numa fase posterior, com migration,
  atualização dos fluxos de gravação e testes.

---

---

## Superseded Decisions

No superseded decisions recorded yet.

---

## Rules

- Never delete historical decisions merely because they changed.
- Mark replaced decisions as `Superseded`.
- Record the replacement decision separately.
- Do not invent reasons that were not established.
- Keep entries concise.
- Do not store secrets or credentials.
