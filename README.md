# Serieboxd com busca no TMDB

Arquivos: `index.html` (o app), `api.php` (proxy que fala com o TMDB) e `config.example.php`.

## Rodar no seu computador
1. Copie `config.example.php` para `config.php` e cole sua chave do TMDB no lugar de `COLE_SUA_CHAVE_AQUI`.
   (Alternativa: defina a variável de ambiente `TMDB_API_KEY`.)
2. Na pasta, rode: `php -S localhost:8000`
3. Abra http://localhost:8000

Precisa de PHP 7.4+ com a extensão cURL (o XAMPP já traz).

## Hospedar
Envie a pasta para qualquer hospedagem com PHP. O `config.php` precisa existir no servidor, mas não deve ser enviado a repositórios públicos.

## Levar os dados da versão publicada
Na versão antiga, abra Estatísticas e copie o texto da caixa Backup. Na nova, abra Estatísticas, cole o texto na caixa e clique em Importar. (Os dados ficam no navegador de cada endereço, então localhost e o site publicado têm bibliotecas separadas.)

## Segurança
A chave fica só no servidor. Nunca a coloque no `index.html` nem em mensagens de chat.

Este produto usa a API do TMDB, mas não é endossado nem certificado pelo TMDB.

## Se a busca der erro
Abra http://localhost:8000/api.php?a=search&q=dark no navegador:
- Se aparecer um JSON com resultados, está tudo certo.
- Se aparecer o código PHP ou uma página em branco, o servidor não está executando PHP (use `php -S localhost:8000`, não o Live Server nem abrir o arquivo direto).
- Se aparecer "Chave do TMDB não configurada", falta criar o `config.php`.
- Se aparecer erro de autenticação do TMDB, confira se a chave foi colada inteira.
