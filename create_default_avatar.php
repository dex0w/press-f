<?php
require_once __DIR__.'/../config/database.php';

$avatarDir = __DIR__.'/../assets/images/avatars/';
if (!file_exists($avatarDir)) {
    mkdir($avatarDir, 0755, true);
}

// Создаем дефолтный аватар
$defaultAvatarPath = $avatarDir.'default-avatar.png';
if (!file_exists($defaultAvatarPath)) {
    $image = imagecreatetruecolor(200, 200);
    $bgColor = imagecolorallocate($image, 100, 149, 237); // CornflowerBlue
    imagefill($image, 0, 0, $bgColor);
    
    // Добавляем инициалы "ПФ" (PressF)
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $font = __DIR__.'/../assets/fonts/arial.ttf'; // Убедитесь что шрифт существует
    imagettftext($image, 80, 0, 50, 130, $textColor, $font, 'PF');
    
    imagepng($image, $defaultAvatarPath);
    imagedestroy($image);
    
    echo "Дефолтный аватар создан!";
} else {
    echo "Дефолтный аватар уже существует";
}