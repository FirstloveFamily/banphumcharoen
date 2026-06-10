<?php

namespace App\Support;

final class UploadLimits
{
    public const IMAGE_MAX_KB = 3072;

    public const DOCUMENT_MAX_KB = 10240;

    public static function imageRules(bool $required = false): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable'],
            ['image', 'max:' . self::IMAGE_MAX_KB]
        );
    }

    public static function documentRules(bool $required = false, array $mimes = ['pdf', 'jpg', 'jpeg', 'png']): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable'],
            ['file', 'mimes:' . implode(',', $mimes), 'max:' . self::DOCUMENT_MAX_KB]
        );
    }

    public static function fileRules(bool $required = false, array $mimes = ['pdf', 'jpg', 'jpeg', 'png', 'webp']): array
    {
        return array_merge(
            [$required ? 'required' : 'nullable'],
            ['file', 'mimes:' . implode(',', $mimes), 'max:' . self::DOCUMENT_MAX_KB]
        );
    }
}
