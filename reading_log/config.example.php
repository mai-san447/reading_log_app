<?php
return [
    'db_dsn' => 'mysql:dbname=YOUR_DATABASE;charset=utf8;host=YOUR_DATABASE_HOST',
    'db_user' => 'YOUR_DATABASE_USER',
    'db_pass' => 'YOUR_DATABASE_PASSWORD',

    // 会員登録の合言葉（招待コード）。空 '' なら誰でも登録できる。
    // 公開先（さくら等）ではここに合言葉を設定すると、
    // その合言葉を知っている人だけが会員登録でき、見ず知らずの人の登録を防げる。
    'register_code' => '',
];
