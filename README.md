# 🐳

## 環境構築

### 1. ローカルに clone する

### 2. Docker のインストール

### 3. 「Docker の起動」と「PHP を使う準備」

```
./docker-compose-local.sh up
```

### 4. DBとTableの作成

DBの作成

```
CREATE DATABASE todo-list
```

userテーブルの作成

```
CREATE TABLE users ( id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, user_name varchar(255) NOT NULL, mail varchar(255) NOT NULL, password varchar(255) NOT NULL created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

tasksテーブルの作成

```
CREATE TABLE tasks ( id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, user_id int(11) NOT NULL, status int(11) NOT NULL DEFAULT '0', contents varchar(255) NOT NULL DEFAULT '', category_id int(11) NOT NULL DEFAULT '1', deadline date NOT NULL, created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

categoriesテーブルの作成

```
CREATE TABLE categories ( id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT, name varchar(255) NOT NULL, created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

## ページ紹介

php

[localhost:8080](http://localhost:8080)

PHPMyAdmin

[localhost:3306](http://localhost:3306)
