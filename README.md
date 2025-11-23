# Application template and use case

## Installation

```
git clone https://github.com/Ilya-Ruk/php-basic-app.git
cd php-basic-app
composer update
```

## Usage

```
curl -X GET basic.local/books
curl -X GET basic.local/books/1
curl -X POST -H "Content-Type: application/json" -d "{\"Author\": \"Тургенев, Иван Сергеевич\", \"Title\": \"Отцы и дети\", \"Year\": 1862}" basic.local/books/add
curl -X PUT -H "Content-Type: application/json" -d "{\"Author\": \"Тургенев, Иван Сергеевич\", \"Title\": \"Отцы и дети\", \"Year\": 2025}" basic.local/books/edit/7
curl -X DELETE basic.local/books/delete/7
```
