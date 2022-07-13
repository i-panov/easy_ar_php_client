<?php

namespace IPanov\EasyArClient\Models;

class Image
{
    private string $encodedContent;

    public function __construct(string $encodedContent) {
        $this->encodedContent = $encodedContent;
    }

    public function encodedContent(): string {
        return $this->encodedContent;
    }

    public static function load(string $path): self {
        if (!is_file($path)) {
            throw new \InvalidArgumentException("File $path not exists");
        }

        return new self(base64_encode(file_get_contents($path)));
    }
}
