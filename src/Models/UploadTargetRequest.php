<?php

namespace IPanov\EasyArClient\Models;

class UploadTargetRequest
{
    public ?Image $image = null;
    public ?bool $active = null;
    public ?string $name = null;
    public ?float $width = null;
    public ?string $meta = null;
    public ?bool $allowSimilar = null;

    public function toArray(): array {
        return array_map('strval', array_filter([
            'image' => $this->image ? $this->image->encodedContent() : null,
            'active' => $this->active !== null ? (int)$this->active : null,
            'name' => $this->name,
            'size' => $this->width,
            'meta' => $this->meta,
            'allowSimilar' => $this->allowSimilar != null ? (int)$this->allowSimilar : null,
            'type' => $this->image ? 'ImageTarget' : null,
        ], fn($val) => !is_null($val)));
    }
}
