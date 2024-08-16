<?php

function debuguear($variable) : string {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML limpia algunos caracteres especiales del HTML
function s($html) : string {
    $s = htmlspecialchars($html);
    return $s;
}