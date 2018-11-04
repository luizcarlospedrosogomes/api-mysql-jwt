# api-mysql-jwt
publicacao


run server
php -S localhost:8000

create database, create table: execute sql.sql and popule  table with data login(password md5)

test
get token
http://localhost:8000/v1/auth
body: {"login": "user", "senha": "passwd"}

access recurse 
http://localhost:8000/v1/book
X-Token: 'token generated'
