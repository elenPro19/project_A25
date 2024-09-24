<?php

namespace  App\Presentation;
require_once '../Application/AdminService.php'; use App\Application\AdminService;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $tariff = $_POST['tariff'];

    $adminService = new AdminService();
    $result = $adminService->addNewProduct($name, $price, $tariff);

    echo json_encode($result);
}

