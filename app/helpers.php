<?php

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string
    {
        return match ($status) {
            'valide'      => 'success',
            'en_attente'  => 'warning',
            'rejete'      => 'danger',
            'bloque'      => 'secondary',
            default       => 'secondary',
        };
    }
}
