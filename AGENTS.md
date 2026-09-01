# OpenCode Orchestration Policy

Este projeto usa um agente principal em GPT-5.6 Luna e subagentes especializados.

O objetivo e maximizar qualidade sem desperdiçar contexto, chamadas de modelo ou quota.

## Politica de modelos

O GPT-5.6 Luna e reservado ao agente principal para coordenacao eficiente e tarefas locais.

Nao utilizes o modelo do agente principal em subagentes salvo pedido explicito do utilizador.

Distribui trabalho segundo a especializacao:

- coordenacao, decisoes globais e integracao: GPT-5.6 Luna;
- arquitetura e dominio complexo: GPT-5.6 Terra;
- implementacao Laravel/PHP: Kimi K2.7 Code via OpenCode Go;
- revisao de codigo e regressao: GLM-5.3-Flash via OpenCode Go;
- seguranca: GPT-5.6 Terra;
- testes e debugging mecanico: modelos OpenCode Go quando apropriado.

Um subagente nunca deve alterar ou escalar autonomamente para o modelo do agente principal.

Quando um subagente encontrar uma decisao que exceda a sua funcao, deve devolver o bloqueio ao agente principal.

## Regra principal

Nao delegues por defeito.

Resolve diretamente tarefas pequenas, locais e bem definidas.

Usa subagentes apenas quando houver ganho claro em especializacao, independencia ou reducao de trabalho do agente principal.

## Limite de delegacao

Usa no maximo 1 subagente de cada vez por defeito.

So uses 2 subagentes em paralelo quando:

- as tarefas forem realmente independentes;
- nao precisarem de ler o mesmo contexto;
- a execucao paralela trouxer vantagem clara;
- nao houver duplicacao de investigacao.

Nunca uses mais de 2 subagentes simultaneamente.

Nao envies a mesma pergunta ou investigacao para varios agentes.

Nao uses varios agentes para obter "segunda opiniao" salvo pedido explicito do utilizador.

## Quando nao delegar

Nao uses subagentes para:

- alterar texto, labels ou mensagens;
- pequenas mudancas Blade;
- ajustes visuais simples;
- CRUDs triviais;
- alterar uma ou duas linhas;
- procurar uma unica referencia;
- executar um grep simples;
- ler um ou dois ficheiros;
- corrigir erros de sintaxe evidentes;
- executar um teste focado simples;
- tarefas que possam ser resolvidas diretamente com poucas tool calls.

## Architect

Usa `architect` apenas quando existir uma decisao estrutural real.

Exemplos:

- mudancas de schema;
- migrations relevantes;
- novas relacoes Eloquent;
- alteracoes que atravessem varios dominios;
- regras complexas de horarios;
- fluxos de aprovacao;
- autorizacao transversal;
- alteracao com risco de integridade de dados.

Nao uses `architect` para validar implementacoes simples.

Depois de receber a recomendacao do architect:

- nao repitas a mesma investigacao;
- usa o resumo como contexto;
- decide e avanca.

## Laravel-Filament

Usa `laravel-filament` quando existir trabalho de implementacao significativo.

Exemplos:

- alteracoes em varios ficheiros;
- Resource + Model + Policy;
- implementacao de funcionalidade;
- refactor local necessario;
- criacao ou alteracao de testes associados.

Nao delegues para `laravel-filament` se o agente principal conseguir fazer a alteracao diretamente com baixo custo.

Fornece sempre ao subagente:

- objetivo concreto;
- ficheiros ja identificados;
- restricoes;
- decisao arquitetural ja tomada;
- criterios de aceitacao.

Nao lhe pecas para "investigar tudo".

## Quality

Usa `quality` apenas depois de existir um diff relevante.

Nao uses automaticamente em todas as tarefas.

Usa quando:

- houver alteracao funcional;
- houver risco de regressao;
- forem alteradas regras de dominio;
- houver varios ficheiros envolvidos;
- forem alteradas queries ou autorizacao;
- o utilizador pedir review.

Para alteracoes pequenas e localizadas, o agente principal pode rever diretamente.

## Security

Usa `security` apenas quando a alteracao tiver superficie de seguranca real.

Exemplos:

- autenticacao;
- autorizacao;
- Policies;
- roles/permissions;
- dados pessoais;
- exports;
- uploads;
- impersonation;
- alteracoes de ownership;
- endpoints;
- acesso horizontal a dados;
- secrets;
- configuracao de producao.

Nao uses `security` por defeito em CRUDs ou alteracoes visuais.

## Ordem preferencial

Para uma tarefa complexa:

1. O agente principal compreende o pedido.
2. O agente principal faz uma pesquisa minima.
3. Se necessario, chama `architect`.
4. O agente principal decide a solucao.
5. O agente principal implementa diretamente OU chama `laravel-filament`.
6. Executa testes focados.
7. Usa `quality` apenas se o risco justificar.
8. Usa `security` apenas se existir superficie de seguranca relevante.
9. O agente principal entrega a resposta final.

Nao uses toda a cadeia automaticamente.

## Politica de contexto

Evita carregar contexto desnecessario.

Nao leias o repositorio inteiro.

Nao leias ficheiros que nao sejam relevantes para a tarefa.

Prefere pesquisas direcionadas.

Quando um subagente devolver:

- ficheiros relevantes;
- linhas;
- conclusoes;
- recomendacoes;

nao voltes a repetir a mesma pesquisa sem motivo concreto.

## Politica de testes

Executa primeiro testes focados.

Nao executes automaticamente toda a suite.

Executa a suite completa apenas quando:

- a alteracao for transversal;
- houver risco significativo de regressao;
- os testes focados nao forem suficientes;
- o utilizador pedir explicitamente.

Nao executes `npm run build`, `composer audit` ou `npm audit` sem razao relacionada com a alteracao.

## Politica de eficiencia

Antes de cada delegacao, pergunta internamente:

1. Preciso mesmo de outro agente?
2. O agente principal consegue resolver isto com poucas tool calls?
3. Ja tenho informacao suficiente?
4. Outro agente vai repetir trabalho ja feito?
5. O custo adicional traz valor real?

Se a resposta indicar pouco beneficio, nao delegues.

## Conclusao antecipada

Quando os criterios de aceitacao estiverem cumpridos:

- para de investigar;
- para de refatorar;
- nao procures melhorias adicionais fora do escopo;
- entrega o resultado.

Nao expandas o pedido sem necessidade.
