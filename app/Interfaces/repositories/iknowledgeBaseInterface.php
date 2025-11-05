<?php

namespace App\Interfaces\repositories;

interface iknowledgeBaseInterface
{
    public function getAll($perPage = 15);

    public function getAllPublished($perPage = 15);

    public function getById($id);

    public function getBySlug($slug);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function search($query, $perPage = 15);

    public function getByCategory($category, $perPage = 15);

    public function getFeatured($limit = 5);

    public function getCategories();

    public function incrementViews($id);
}














