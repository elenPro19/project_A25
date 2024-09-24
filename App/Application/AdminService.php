<?php
namespace App\Application;
require_once '../Infrastructure/sdbh.php'; use sdbh\sdbh;
require_once '../Domain/Users/UserEntity.php'; use App\Domain\Users\UserEntity;

class AdminService {
    /** @var UserEntity */
    public UserEntity $user;
    private sdbh $db;

    public function __construct()
    {
        $this->user = new UserEntity();
        $this->db =  new sdbh(['host'=> null, 'dbname' => 'test_a25', 'user' => 'root', 'pass' => '']);
    }

    public function addNewProduct($name, $price, $tariff): array
    {
        if (!$this->user->isAdmin) {
            return ['status' => 'error', 'message' => 'Access denied'];
        }

        if (empty($name) || empty($price) || !is_numeric($price)) {
            return ['status' => 'error', 'message' => 'Invalid input'];
        }

        $params = [
            'NAME' => $name,
            'PRICE' => $price,
            'TARIFF' => $tariff
        ];

        if ($this->db->insert_row('a25_products', $params)) {
            return ['status' => 'success', 'message' => 'Product added successfully'];
        } else {
            return ['status' => 'error', 'message' => 'Database error'];
        }
    }
}
