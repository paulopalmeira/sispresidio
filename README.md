# Sispresidio - Sistema de Gerenciamento Prisional

**Projeto Integrador Transdisciplinar em Sistemas de Informação II**
**Turma:** Turma_001 - 2025

---

## 1. Introdução e Contextualização do Projeto

[cite_start]Professor, este documento apresenta a solução de software desenvolvida para a "Atividade de Entrega" da matéria de PIT - Sistemas de Informação II[cite: 13]. O projeto **Sispresidio** é uma aplicação web funcional que atende a todos os requisitos e desafios propostos, desde a modelagem e implementação do banco de dados até a codificação e a realização de testes de qualidade.

[cite_start]O desenvolvimento observou as três situações-problema descritas no material teórico[cite: 8, 9, 10], resultando em um sistema robusto que aborda:
* [cite_start]**Situação-Problema 1:** O projeto físico do banco de dados foi implementado em MySQL, a partir da modelagem conceitual[cite: 318].
* [cite_start]**Situação-Problema 2:** A aplicação foi codificada utilizando PHP para o back-end e HTML/CSS/Bootstrap para o front-end, seguindo uma arquitetura organizada[cite: 323, 326].
* [cite_start]**Situação-Problema 3:** Foram implementados testes de verificação (automatizados com PHPUnit) e o sistema está preparado para os testes de validação (aceitação pelos colegas), conforme solicitado[cite: 331, 332].

## 2. Visão Geral e Funcionalidades

O Sispresidio é um sistema para gerenciamento de uma unidade prisional, com controle de acesso baseado em dois perfis de usuário: **Diretor** e **Agente**.

#### Funcionalidades do Diretor:
* **Gerenciamento de Pavilhões:** Permite ativar ou desativar pavilhões, com a obrigatoriedade de registrar uma motivação para cada ação, garantindo a rastreabilidade administrativa.
* **Relatório de Movimentações:** Acesso a um log completo de todas as transferências de presos, com filtros para facilitar a auditoria.

#### Funcionalidades do Agente:
* **Controle de Detentos:** CRUD completo para o cadastro e gestão de presos. O formulário possui máscaras de entrada para documentos, melhorando a experiência do usuário (UX).
* **Movimentação de Presos:** Ferramenta para transferir detentos entre celas, validando regras de negócio como a disponibilidade de vagas e o status ativo do pavilhão de destino.

## 3. Arquitetura e Tecnologias Utilizadas

[cite_start]Conforme a liberdade de escolha de tecnologias sugerida[cite: 352, 353, 354, 355, 356], o projeto foi desenvolvido com as seguintes ferramentas:

| Categoria                | Tecnologia            | Justificativa                                                                                             |
| :----------------------- | :-------------------- | :-------------------------------------------------------------------------------------------------------- |
| **Linguagem (Back-end)** | PHP 8                 | Linguagem robusta e amplamente utilizada para desenvolvimento web.                                          |
| **Banco de Dados** | MySQL                 | [cite_start]SGBD relacional popular e de fácil manutenção, como sugerido no material[cite: 345].                       |
| **Hospedagem** | InfinityFree          | [cite_start]Plataforma de hospedagem gratuita que suporta PHP e MySQL, atendendo ao requisito de um código funcional[cite: 354, 357]. |
| **Frontend** | HTML, CSS, Bootstrap  | Construção de interfaces responsivas e consistentes.                                                      |
| **Testes Automatizados** | PHPUnit               | [cite_start]Framework padrão da indústria para testes unitários em PHP, citado no material da disciplina[cite: 242]. |
| **Gerenciador de Pacotes** | Composer              | Para gerenciamento das dependências de desenvolvimento (PHPUnit).                                         |

### **Acesso ao Sistema em Funcionamento**

A aplicação está hospedada e pode ser acessada publicamente através do seguinte link:

* **URL:** **http://sispresidio.ct.ws**

## 4. Como Executar o Projeto

Para testar a aplicação, por favor, siga os passos abaixo:

#### Passo 1: Configuração do Banco de Dados
1.  Crie um banco de dados MySQL no seu servidor.
2.  Atualize o arquivo `sispresidio/db/conexao.php` com as credenciais de acesso (host, nome do banco, usuário e senha).

#### Passo 2: Criação da Estrutura
1.  Após configurar a conexão, acesse o arquivo `sispresidio/db/criar_banco.php` através do navegador.
2.  Este script criará todas as tabelas necessárias e inserirá os usuários padrão, além da estrutura de pavilhões e celas (8 celas por pavilhão).

#### Passo 3: (Opcional) Povoar com Dados de Teste
* Para popular o sistema com 20 presos gerados aleatoriamente, acesse o arquivo `sispresidio/db/povoar_presos.php` pelo navegador.

#### Passo 4: Acessar o Sistema
* Acesse a raiz do projeto pelo navegador.
* Utilize as credenciais abaixo para fazer login:
    * **Usuário Diretor:**
        * **Matrícula:** `diretor123`
        * **Senha:** `senha_diretor`
    * **Usuário Agente:**
        * **Matrícula:** `agente123`
        * **Senha:** `senha_agente`

## 5. Testes de Qualidade (Verificação)

[cite_start]Para atender ao requisito de testes da "Situação-Problema 3"[cite: 331], foi implementada uma suíte de testes automatizados com PHPUnit. Estes testes de verificação garantem que a lógica interna do sistema (back-end) funciona conforme o esperado.

#### Como Executar os Testes
1.  Clone o repositório e execute `composer install`.
2.  Configure o arquivo `phpunit.xml` com as credenciais de um banco de dados de teste local.
3.  Execute o comando `vendor\bin\phpunit -c phpunit.xml` na raiz do projeto.

#### Testes Implementados:
A suíte é composta por 6 testes que validam as funcionalidades mais críticas:

1.  **`AuthenticationTest.php`**: Garante que o sistema de login é seguro, testando cenários de sucesso, senha incorreta e usuário inexistente.
2.  **`CadastroPresoTest.php`**: Valida a integridade dos dados no cadastro de presos, confirmando a inserção correta e o bloqueio de matrículas duplicadas.
3.  **`MovimentacaoTest.php`**: Testa a principal regra de negócio, verificando se a realocação de um preso atualiza sua cela e cria o devido registro de auditoria.

#### Resultado dos Testes
A execução da suíte de testes resulta em 100% de aprovação, confirmando a estabilidade e a qualidade do código desenvolvido.