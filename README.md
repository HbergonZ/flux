# PROJETA

**Sistema de Gerenciamento de Projetos e Iniciativas**

## ✨ Sobre o PROJETA

O **PROJETA** é um sistema desenvolvido para facilitar o acompanhamento, a atualização e o gerenciamento de projetos e suas iniciativas.

Criado para atender inicialmente às demandas do Núcleo de Monitoramento e da Gerência de Transformação Digital da Coordenadoria de Gestão Estratégica da SETIC de Rondônia, o sistema busca eliminar a necessidade de planilhas compartilhadas, proporcionando uma plataforma mais segura, eficiente e confiável.

## 🎯 Objetivo

- Facilitar o gerenciamento de projetos pelas equipes gestoras.
- Permitir que usuários atualizem o status e datas das etapas dos projetos de forma prática.
- Padronizar informações e evitar perda de dados.
- Apoiar a geração de relatórios estratégicos.

## 🚀 Visão de Futuro

O PROJETA será evoluído para oferecer:

- Mais dinamismo na configuração de projetos.
- Expansão para novos escopos.
- Mantendo sempre a **simplicidade e facilidade de uso** como prioridade.

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP (CodeIgniter 4)
- **Frontend:** HTML5, CSS3, JavaScript
- **Banco de Dados:** MySQL
- **Hospedagem Inicial:** Ambiente local (localhost)

## ⚙️ Configuração Inicial

Para rodar o projeto localmente, siga os passos abaixo:

1. Clone o repositório do projeto.

2. Instale as dependências do CodeIgniter:

   ```bash
   composer install
   ```

3. Copie o arquivo `env` para `.env`:

   ```bash
   cp env .env
   ```

   > Personalize o `.env` conforme necessário:
   >
   > - Defina o `baseURL` do seu projeto.
   > - Configure as credenciais de conexão com o banco de dados.

4. Certifique-se de que o servidor web (Apache, Nginx, etc.) esteja apontando para a **pasta `public/`**, não para a raiz do projeto.

5. Atualize sempre que necessário:

   ```bash
   composer update
   ```

6. Acesse o sistema através do seu navegador, utilizando a URL configurada no `baseURL`.

## 📋 Requisitos do Servidor

- PHP 8.1 ou superior
- Extensões PHP necessárias:
  - intl
  - mbstring
  - json (habilitado por padrão)
  - mysqlnd (se for utilizar MySQL)
  - libcurl (se for utilizar requisições HTTP via CURL)

> ⚠️ Atenção: PHP 7.4 e 8.0 já atingiram o fim de vida útil (EOL). Recomenda-se utilizar PHP 8.1 ou superior.

## 📈 Benefícios do Sistema

- ✅ Redução de retrabalho
- ✅ Acompanhamento em tempo real do progresso
- ✅ Padronização de dados
- ✅ Geração facilitada de relatórios
- ✅ Experiência amigável para usuários e gestores

## 📌 Status do Projeto

> **Em desenvolvimento** — Versão inicial para implantação local.

## 💡 Como Contribuir

Caso tenha sugestões, identifique algum problema ou deseje contribuir com melhorias para o PROJETA, entre em contato ou abra uma issue.
