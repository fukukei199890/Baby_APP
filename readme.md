# 起動コマンド
~~~
docker compose -d --build
~~~

# laravelのインストールコマンド
~~~
docker compose exec app composer create-project laravel/laravel .
~~~

# SQLiteファイル作成
~~~
docker compose exec app touch database/database.sqlite
~~~

# 権限開放(おまじないみたいなものだとおもって実行してください)
~~~
docker compose exec app chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database
~~~

# Breezeパッケージのinstall
~~~
docker compose exec app composer require laravel/breeze --dev
~~~

# Breezeパッケージの展開
~~~
docker compose exec app php artisan breeze:install blade
~~~

# npmのインストール
~~~
docker compose exec app npm install
~~~
## npmのビルド
~~~
docker compose exec app npm run build
~~~