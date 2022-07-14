<?php

namespace IPanov\EasyArClient\Models;

class Target
{
    public string $id;
    public string $trackingImage;
    public string $name;
    public float $width;
    public ?string $meta;
    public string $type;
    public bool $active;

    public function __construct(array $data) {
        $this->id = $data['targetId'];
        $this->trackingImage = $data['trackingImage'];
        $this->name = $data['name'];
        $this->width = $data['size'];
        $this->meta = $data['meta'];
        $this->type = $data['type'];
        $this->active = (bool)($data['active'] ?? false);
    }
}
