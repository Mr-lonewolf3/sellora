<?php
// config/helpers.php

function getProductImage($image, $name) {
    if (!empty($image)) {
        $server_path = UPLOAD_DIR . $image;

        if (file_exists($server_path)) {
            return UPLOAD_URL . $image;
        }
    }

    return 'https://via.placeholder.com/300x300?text=' . urlencode($name);
}