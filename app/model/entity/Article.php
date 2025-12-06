<?php

class Article {
    private $id;
    private $titol;
    private $cos;
    private $imatge_url;
    private $author_id;
    private $author_nom;
    private $data_creacio;

    public function __construct($id, $titol, $cos, $imatge_url, $author_id, $author_nom, $data_creacio) {
        $this->id = $id;
        $this->titol = $titol;
        $this->cos = $cos;
        $this->imatge_url = $imatge_url;
        $this->author_id = $author_id;
        $this->author_nom = $author_nom;
        $this->data_creacio = $data_creacio;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getTitol() { return $this->titol; }
    public function getCos() { return $this->cos; }
    public function getImatgeUrl() { return $this->imatge_url; }
    public function getAuthorId() { return $this->author_id; }
    public function getAuthorNom() { return $this->author_nom; }
    public function getDataCreacio() { return $this->data_creacio; }

    // Setters
    public function setTitol($titol) { $this->titol = $titol; }
    public function setCos($cos) { $this->cos = $cos; }
    public function setImatgeUrl($img) { $this->imatge_url = $img; }
    public function setAuthor($author_nom) { $this->author = $author_nom; }
}

?>