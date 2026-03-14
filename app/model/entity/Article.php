<?php

class Article {
    
    private int $id;
    private ?int $authorId;
    private string $slug;
    private string $title;
    private string $content;
    private ?string $imageUrl;
    private ?string $publishedAt;
    private ?string $authorName;

    public function __construct(
        int $id,
        ?int $authorId,
        string $slug,
        string $title,
        string $content,
        ?string $imageUrl,
        ?string $publishedAt,
        ?string $authorName = null
    ) {
        $this->id = $id;
        $this->authorId = $authorId;
        $this->slug = $slug;
        $this->title = $title;
        $this->content = $content;
        $this->imageUrl = $imageUrl;
        $this->publishedAt = $publishedAt;
        $this->authorName = $authorName;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getAuthorId(): ?int {
        return $this->authorId;
    }

    public function getSlug(): string {
        return $this->slug;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function getImageUrl(): ?string {
        return $this->imageUrl;
    }

    public function getPublishedAt(): ?string {
        return $this->publishedAt;
    }

    public function getAuthorName(): ?string {
        return $this->authorName;
    }

    // Setters
    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setContent(string $content): void {
        $this->content = $content;
    }

    public function setImageUrl(?string $imageUrl): void {
        $this->imageUrl = $imageUrl;
    }

    public function setAuthorName(?string $authorName): void {
        $this->authorName = $authorName;
    }

    // Factory method
    public static function fromArray(array $data): self {
        return new self(
            (int)$data['id'],
            isset($data['author_id']) ? (int)$data['author_id'] : null,
            $data['slug'],
            $data['title'],
            $data['content'],
            $data['image_url'] ?? null,
            $data['published_at'] ?? null,
            $data['author_name'] ?? null
        );
    }
}