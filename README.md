# Sispresidio - Sistema de Gerenciamento Prisional

**Projeto Integrador Transdisciplinar em Sistemas de Informação II** **Turma:** Turma_001 - 2025

---

## 1. Visão Geral do Projeto

O **Sispresidio** é um sistema web desenvolvido como atividade acadêmica para a matéria de PIT. A aplicação simula uma plataforma para o gerenciamento de um estabelecimento prisional, focando em operações essenciais como o controle de detentos, a administração da infraestrutura (pavilhões e celas) e o monitoramento de movimentações internas.

O sistema foi projetado com uma arquitetura baseada em papéis de usuário, onde cada perfil (Diretor e Agente) possui permissões e acesso a funcionalidades específicas, refletindo uma estrutura organizacional realista.

## 2. Funcionalidades Principais

O Sispresidio oferece um conjunto de funcionalidades robustas para diferentes níveis de acesso:

### Funcionalidades Gerais
- **Autenticação Segura:** Sistema de login que verifica as credenciais do usuário e direciona para um painel de controle (dashboard) específico ao seu papel. As senhas são armazenadas de forma segura utilizando `password_hash()`.
- **Dashboard de Indicadores:** A página inicial exibe um painel com dados em tempo real, como o número total de presos, pavilhões ativos e celas ocupadas.
- **Controle de Acesso por Papel (RBAC):** O acesso a cada página é rigorosamente controlado, garantindo que Agentes não acessem funcionalidades de Diretores e vice-versa.

### Módulo do Diretor (`/diretor`)
O Diretor possui uma visão administrativa e estratégica do sistema:

- **Gerenciamento de Pavilhões:** Permite ativar ou desativar pavilhões. Para cada ação, é obrigatório registrar uma motivação (ex: "Inativado para reforma"), que fica salva como uma observação para auditoria.
- **Relatório de Movimentações:** Acesso a um relatório detalhado de todas as transferências de presos entre celas, com a possibilidade de filtrar por nome do detento e data da movimentação.

### Módulo do Agente (`/agente`)
O Agente é responsável pelas operações diárias e pelo controle direto dos detentos:

- **Cadastro e Edição de Presos:** Formulário completo para registrar novos detentos ou atualizar informações de existentes, incluindo dados pessoais e prisionais. O formulário utiliza máscaras de entrada para garantir a formatação correta de CPF, RG e matrícula.
- **Gerenciamento de Presos:** Tela que exibe a lista completa de detentos, com informações sobre sua alocação atual (cela e pavilhão).
- **Movimentação de Presos:** Funcionalidade para transferir um preso de uma cela para outra. O sistema exibe apenas celas de destino que estejam em pavilhões ativos e com vagas disponíveis.

## 3. Estrutura do Banco de Dados

O sistema utiliza um banco de dados **SQLite**, o que o torna leve e portátil, sem a necessidade de um servidor de banco de dados separado. A estrutura é composta pelas seguintes tabelas:

- `usuarios`: Armazena os dados de login (matrícula, senha hasheada, tipo).
- `pavilhoes`: Registra os pavilhões da unidade, com nome e status (Ativo/Inativo).
- `celas`: Contém as celas, sua capacidade e a qual pavilhão pertencem. A regra de negócio define 8 celas por pavilhão.
- `presos`: Tabela principal com todos os dados dos detentos.
- `movimentacoes`: Funciona como um log, registrando cada transferência de um preso.

## 4. Tecnologias Utilizadas

- **Backend:** PHP 8
- **Banco de Dados:** SQLite 3
- **Frontend:** HTML5, CSS3, Bootstrap 4
- **Interatividade:** JavaScript e jQuery (para máscaras de formulário e componentes do Bootstrap)

## 5. Como Executar e Testar o Projeto

### Pré-requisitos
- Servidor web local com suporte a PHP (XAMPP, WAMP, etc.).
- PHP 8 ou superior.

### Passos para Instalação
1.  **Clone ou baixe** os arquivos do projeto para o diretório do seu servidor web (ex: `htdocs` no XAMPP).
2.  **Crie o Banco de Dados:**
    - Abra um terminal ou prompt de comando.
    - Navegue até a pasta raiz do projeto (`sispresidio/`).
    - Execute o comando: `php db/criar_banco.php`.
    - Isso criará o arquivo `sispresidio.db` com as tabelas e os usuários padrão.
3.  **(Opcional) Povoar o Banco de Dados:**
    - Para testar o sistema com dados fictícios, execute: `php db/povoar_presos.php`.
    - Isso irá cadastrar 20 presos com dados aleatórios e únicos.
4.  **Acesse o Sistema:**
    - Abra o navegador e acesse `http://localhost/sispresidio/` (ou o caminho correspondente no seu servidor).
    - Você será redirecionado para a tela de login.

### Credenciais de Teste
Acesse o sistema utilizando os seguintes usuários padrão:

-   **Perfil Diretor:**
    -   **Matrícula:** `diretor123`
    -   **Senha:** `senha_diretor`

-   **Perfil Agente:**
    -   **Matrícula:** `agente123`
    -   **Senha:** `senha_agente`

---
*Este projeto é estritamente para fins acadêmicos e não deve ser utilizado em um ambiente de produção.*