<?php
    define("HOST", "localhost"); // Host database
    define("USER", "root"); // Username database
    define("PASSWORD", ""); // Password database
    define("DATABASE", "LSP"); // Nama database

    $conn = new mysqli(HOST, USER, PASSWORD, DATABASE); // Melakukan koneksi ke database berdasarkan konfigurasi diatas

?>