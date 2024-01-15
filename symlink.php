<?php

$laravelStoragePath =  realpath($_SERVER['DOCUMENT_ROOT'] . '/../laravel/storage/app/public/profile_pictures');
$publicHtmlStoragePath = realpath($_SERVER['DOCUMENT_ROOT'] . '/storage/profile_pictures');

// Membuat direktori jika belum ada
if (!file_exists($publicHtmlStoragePath)) {
    mkdir($publicHtmlStoragePath, 0755, true);
}

// Menghapus symlink jika sudah ada
if (is_link($publicHtmlStoragePath)) {
    unlink($publicHtmlStoragePath);
}

// Membuat symlink menggunakan perintah shell
$command = "ln -s $laravelStoragePath $publicHtmlStoragePath";
exec($command, $output, $returnCode);

if ($returnCode === 0) {
    echo 'Symbolic link created successfully.';
} else {
    echo 'Failed to create symbolic link.';
    echo implode("\n", $output); // Menampilkan output dari perintah shell untuk debugging
}
