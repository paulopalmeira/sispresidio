# Sispresidio - Sistema de Gerenciamento Prisional

**Atividade de Entrega da Matéria:** Projeto Integrador Transdisciplinar em Sistemas de Informação II
**Turma:** Turma_001 - 2025

---

## 1. Objetivo do Projeto

Professor, este projeto consiste no desenvolvimento de um sistema web, o **Sispresidio**, para o gerenciamento de uma unidade prisional. O objetivo foi aplicar os conceitos aprendidos na disciplina para criar uma aplicação funcional, com lógica de negócio bem definida, controle de acesso por papéis e persistência de dados em um banco de dados relacional.

O sistema foi migrado de SQLite para **MySQL**, tornando-o mais robusto e preparado para um ambiente de produção simulado.

## 2. Funcionalidades Implementadas

A aplicação é dividida em dois níveis de acesso, cada um com suas respectivas responsabilidades:

#### Perfil: Diretor
- **Gerenciamento de Pavilhões:** Permite ativar e desativar pavilhões, exigindo uma justificativa para cada alteração de status para fins de auditoria.
- **Relatório de Movimentações:** Oferece uma visão completa de todas as transferências de presos, com filtros por nome e data.

#### Perfil: Agente
- **Controle de Detentos:** CRUD completo para o cadastro e atualização de presos, incluindo dados pessoais e informações prisionais. O formulário utiliza máscaras para garantir a formatação correta dos documentos.
- **Movimentação de Presos:** Ferramenta para registrar a transferência de um detento entre celas. O sistema valida a regra de negócio, exibindo apenas celas de pavilhões ativos e com vagas disponíveis.

## 3. Arquitetura e Tecnologias

- **Backend:** PHP 8
- **Banco de Dados:** MySQL
- **Frontend:** HTML5, CSS3, Bootstrap 4
- **Cliente-Side Scripting:** JavaScript e jQuery (utilizado para máscaras de formulário e componentes interativos).

## 4. Como Executar o Projeto

Para testar a aplicação, por favor, siga os passos abaixo:

#### Passo 1: Configuração do Banco de Dados
1.  Crie um banco de dados MySQL no seu servidor.
2.  Atualize o arquivo `sispresidio/db/conexao.php` com as credenciais de acesso (host, nome do banco, usuário e senha).

#### Passo 2: Criação da Estrutura
1.  Após configurar a conexão, acesse o arquivo `sispresidio/db/criar_banco.php` através do navegador.
2.  Este script criará todas as tabelas necessárias e inserirá os usuários padrão, além da estrutura de pavilhões e celas (8 celas por pavilhão).

#### Passo 3: (Opcional) Povoar com Dados de Teste
-   Para popular o sistema com 20 presos gerados aleatoriamente, acesse o arquivo `sispresidio/db/povoar_presos.php` pelo navegador.

#### Passo 4: Acessar o Sistema
-   Acesse a raiz do projeto pelo navegador.
-   Utilize as credenciais abaixo para fazer login:

    -   **Usuário Diretor:**
        -   **Matrícula:** `diretor123`
        -   **Senha:** `senha_diretor`

    -   **Usuário Agente:**
        -   **Matrícula:** `agente123`
        -   **Senha:** `senha_agente`

---