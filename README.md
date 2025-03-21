# Backend Challenge 20230105 - API de Produtos

Este projeto consiste em uma API RESTful desenvolvida em **Laravel 11** com integração ao banco de dados **SQLite** e autenticação via **Laravel Sanctum**. A API consome dados do [Open Food Facts](https://challenges.coode.sh/food/data/json/index.txt) para gerenciar produtos alimentícios.

## 🛠️ Tecnologias Utilizadas
- **Laravel 11**: Framework PHP para desenvolvimento backend
- **SQLite**: Banco de dados relacional leve
- **Laravel Sanctum**: Autenticação via API Keys
- **PHPUnit**: Testes unitários e de integração

---

## 🚀 Funcionalidades Implementadas

### ✅ **API de Produtos**
- `GET /` - Status da API, com informações sobre uso de memória e último cron executado.
- `GET /products` - Listar produtos com paginação.
- `GET /products/{code}` - Buscar detalhes de um produto por código.
- `PUT /products/{code}` - Atualizar detalhes de um produto.
- `DELETE /products/{code}` - Marcar um produto como `trash`.

### ✅ **Autenticação**
- `POST /generate-api-key` - Geração de API Key para autenticação.
- Os endpoints protegidos exigem autenticação com o cabeçalho `Authorization: Bearer {API_KEY}`.

### ✅ **Sistema CRON de Importação**
- Importação diária de dados do Open Food Facts.
- Processamento de até 100 produtos por arquivo.
- Controle de logs com detalhes sobre o status da importação.

### ✅ **Testes Automatizados**
- Testes de unidade e integração utilizando **PHPUnit**.
- Testes para endpoints da API e para o sistema de importação.

---

## ⚙️ Como Executar o Projeto

### **Pré-requisitos:**
- PHP 8.2+
- Composer
- SQLite

### **Passo a Passo:**

1. Clone o repositório:
```bash
git clone https://github.com/pedroalano/products-parser-20250317.git
cd products-parser-20250317
```

2. Configure o `.env`:
```bash
cp .env.example .env
```
Certifique-se de que o banco de dados SQLite esteja configurado corretamente:
```env
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

3. Crie o arquivo do banco SQLite:
```bash
mkdir -p database
touch database/database.sqlite
```

4. Instale as dependências:
```bash
composer install
```

5. Execute as migrações:
```bash
php artisan migrate
```

6. Inicie o servidor:
```bash
php artisan serve
```

7. Acesse a API:
- API: `http://localhost:8000/api`
- Documentação: `http://localhost:8000/api/documentation`

---

## 🧪 Testes
Para executar os testes:
```bash
php artisan test
```

## 🚀 Postman
Para executar os testes:
```bash
/products-parser-20250317/Products parser test.postman_collection.json
```

---

## 📬 Contato
Caso tenha dúvidas ou sugestões, entre em contato através do e-mail: `pedroalano@gmail.com`

Este projeto foi desenvolvido como parte do **Backend Challenge 20230105** proposto pela **Coodesh**.  

> This is a challenge by [Coodesh](https://coodesh.com/)



----
----
----
----
# Backend Challenge 20230105

## Introdução

Nesse desafio trabalharemos no desenvolvimento de uma REST API para utilizar os dados do projeto Open Food Facts, que é um banco de dados aberto com informação nutricional de diversos produtos alimentícios.

O projeto tem como objetivo dar suporte a equipe de nutricionistas da empresa Fitness Foods LC para que eles possam revisar de maneira rápida a informação nutricional dos alimentos que os usuários publicam pela aplicação móvel.

### Antes de começar
 
- O projeto deve utilizar a Linguagem específica na avaliação. Por exempo: Python, R, Scala e entre outras;
- Considere como deadline da avaliação a partir do início do teste. Caso tenha sido convidado a realizar o teste e não seja possível concluir dentro deste período, avise a pessoa que o convidou para receber instruções sobre o que fazer.
- Documentar todo o processo de investigação para o desenvolvimento da atividade (README.md no seu repositório); os resultados destas tarefas são tão importantes do que o seu processo de pensamento e decisões à medida que as completa, por isso tente documentar e apresentar os seus hipóteses e decisões na medida do possível.

## O projeto
 
- Criar um banco de dados MongoDB usando Atlas: https://www.mongodb.com/cloud/atlas ou algum Banco de Dados SQL se não sentir confortável com NoSQL;
- Criar uma REST API com as melhores práticas de desenvolvimento, Design Patterns, SOLID e DDD.
- Integrar a API com o banco de dados criado para persistir os dados
- Recomendável usar Drivers oficiais para integração com o DB
- Desenvolver Testes Unitários

### Modelo de Dados:

Para a definição do modelo, consultar o arquivo [products.json](./products.json) que foi exportado do Open Food Facts, um detalhe importante é que temos dois campos personalizados para poder fazer o controle interno do sistema e que deverão ser aplicados em todos os alimentos no momento da importação, os campos são:

- `imported_t`: campo do tipo Date com a dia e hora que foi importado;
- `status`: campo do tipo Enum com os possíveis valores draft, trash e published;

### Sistema do CRON

Para prosseguir com o desafio, precisaremos criar na API um sistema de atualização que vai importar os dados para a Base de Dados com a versão mais recente do [Open Food Facts](https://br.openfoodfacts.org/data) uma vez ao día. Adicionar aos arquivos de configuração o melhor horário para executar a importação.

A lista de arquivos do Open Food, pode ser encontrada em: 

- https://challenges.coode.sh/food/data/json/index.txt
- https://challenges.coode.sh/food/data/json/data-fields.txt

Onde cada linha representa um arquivo que está disponível em https://challenges.coode.sh/food/data/json/{filename}.

É recomendável utilizar uma Collection secundária para controlar os históricos das importações e facilitar a validação durante a execução.

Ter em conta que:

- Todos os produtos deverão ter os campos personalizados `imported_t` e `status`.
- Limitar a importação a somente 100 produtos de cada arquivo.

### A REST API

Na REST API teremos um CRUD com os seguintes endpoints:

 - `GET /`: Detalhes da API, se conexão leitura e escritura com a base de dados está OK, horário da última vez que o CRON foi executado, tempo online e uso de memória.
 - `PUT /products/:code`: Será responsável por receber atualizações do Projeto Web
 - `DELETE /products/:code`: Mudar o status do produto para `trash`
 - `GET /products/:code`: Obter a informação somente de um produto da base de dados
 - `GET /products`: Listar todos os produtos da base de dados, adicionar sistema de paginação para não sobrecarregar o `REQUEST`.

## Extras

- **Diferencial 1** Configuração de um endpoint de busca com Elastic Search ou similares;
- **Diferencial 2** Configurar Docker no Projeto para facilitar o Deploy da equipe de DevOps;
- **Diferencial 3** Configurar um sistema de alerta se tem algum falho durante o Sync dos produtos;
- **Diferencial 4** Descrever a documentação da API utilizando o conceito de Open API 3.0;
- **Diferencial 5** Escrever Unit Tests para os endpoints  GET e PUT do CRUD;
- **Diferencial 6** Escrever um esquema de segurança utilizando `API KEY` nos endpoints. Ref: https://learning.postman.com/docs/sending-requests/authorization/#api-key



## Readme do Repositório

- Deve conter o título do projeto
- Uma descrição sobre o projeto em frase
- Deve conter uma lista com linguagem, framework e/ou tecnologias usadas
- Como instalar e usar o projeto (instruções)
- Não esqueça o [.gitignore](https://www.toptal.com/developers/gitignore)
- Se está usando github pessoal, referencie que é um challenge by coodesh:  

>  This is a challenge by [Coodesh](https://coodesh.com/)

## Finalização e Instruções para a Apresentação

1. Adicione o link do repositório com a sua solução no teste
2. Adicione o link da apresentação do seu projeto no README.md.
3. Verifique se o Readme está bom e faça o commit final em seu repositório;
4. Envie e aguarde as instruções para seguir. Sucesso e boa sorte. =)

## Suporte

Use a [nossa comunidade](https://discord.gg/rdXbEvjsWu) para tirar dúvidas sobre o processo ou envie uma mensagem diretamente a um especialista no chat da plataforma. 
