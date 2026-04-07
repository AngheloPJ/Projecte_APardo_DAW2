<?php

function buildAvatarURL(?string $rawAvatar): string {
    if ($rawAvatar && (str_starts_with($rawAvatar, 'http://') || str_starts_with($rawAvatar, 'https://'))) {
        return $rawAvatar;
    }

    return $rawAvatar
        ? BASE_URL . $rawAvatar
        : BASE_URL . 'public/uploads/avatars/default.webp';
}
