<?php
// config/helpers.php

function getProductImage($filename, $name) {

    if (!empty($filename) && file_exists(UPLOAD_DIR . $filename)) {
        return UPLOAD_URL . $filename;
    }

    return BASE_URL . "client/images/placeholder.svg";
}