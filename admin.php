<?php
require_once 'App/Domain/Users/UserEntity.php';
use App\Domain\Users\UserEntity;

$user = new UserEntity();
if (!$user->isAdmin) die('Доступ закрыт');
?>

<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet"/>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
    <div class="container">
        <h1>Админка</h1>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-3" style="color: dimgrey">Добавление товара</h2>
    <div class="row">
        <div class="col-md-6">
            <form id="product-form">
                <div class="mb-3">
                    <label for="productName" class="form-label">Название</label>
                    <input type="text" class="form-control" id="productName" required>
                </div>

                <div class="mb-3">
                    <label for="productPrice" class="form-label">Цена</label>
                    <input type="number" class="form-control" id="productPrice" required>
                </div>

                <h4 class="mt-4 mb-2" style="color: dimgrey">Тарифы</h4>

                <div id="tariff-container"></div>

                <button type="button" class="btn btn-outline-secondary btn-sm -mt-1 mb-4" id="add-tariff">Добавить тариф
                </button>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="submit" class="btn btn-primary">Добавить</button>
                    <div id="notification" style="display: none;" class="alert alert-success mb-0" role="alert"></div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#product-form').submit(function (event) {
            event.preventDefault();

            let productData = {
                name: $('#productName').val(),
                price: $('#productPrice').val(),
                tariff: (Object.keys(tariffs).length > 0) ? serializeTariffs(tariffs) : null

            };

            $.ajax({
                url: 'App/Presentation/AdminController.php',
                type: 'POST',
                data: productData,
                success: function (response) {
                    $('#notification')
                        .text('Продукт успешно добавлен')
                        .addClass('show')
                        .fadeIn()
                        .delay(1000)
                        .fadeOut(function () {
                            $(this).removeClass('show');
                        });
                    resetFormAndTariffs();
                },
                error: function () {
                    alert('Ошибка при добавлении продукта');
                }
            });
        });

        let tariffs = {};

        // Функция для рендера текущих тарифов в контейнере
        function renderTariffs() {

            $('#tariff-container').empty();
            // Проверяем, есть ли элементы в объекте tariffs
            if (Object.keys(tariffs).length === 0) {
                $('#tariff-container').append(`<p>Тарифы не добавлены.</p>`);
            } else {

                // Проходим по каждому элементу объекта tariffs и выводим его в контейнер
                $.each(tariffs, function (id, tariff) {
                    $('#tariff-container').append(`
                        <div class="tariff-item d-flex justify-content-between align-items-center " data-id="${id}" >
                            <div class="flex-grow-1 me-2">
                                <label class="form-label mb-1">Кол-во дней проката</label>
                                <input type="number" class="form-control tariff-days" value="${tariff.days}">
                                <p class="text-danger days-error small mb-1" style="min-height: 15px;"></p>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <label class="form-label mb-1">Цена</label>
                                <input type="number" class="form-control tariff-price" value="${tariff.price}">
                                <p class="text-danger price-error small mb-1" style="min-height: 15px;"></p>
                            </div>
                            <button class="btn btn-sm  remove-tariff">
                                <i class="fas fa-trash-alt text-muted"></i>
                            </button>
                        </div>
                    `);
                });
            }
        }

        function serializeTariffs(tariffs) {
            let serialized = `a:${Object.keys(tariffs).length}:{`;
            for (const tariff of Object.values(tariffs)) {
                serialized += `i:${tariff.days};i:${tariff.price};`;
            }

            serialized += '}';
            return serialized;
        }

        function addTariff() {
            // Проверка на наличие незаполненных полей
            let hasEmptyTariff = false;

            hasEmptyTariff = validateTariffs();

            // Если есть незаполненные тарифы, прекращаем добавление нового
            if (hasEmptyTariff) {
                return;
            }

            // Генерируем уникальный ID для тарифа
            let id = Date.now();

            // Добавляем пустой тариф в объект
            tariffs[id] = {days: '', price: ''};

            // Обновляем отображение
            renderTariffs();
        }

        function validateTariffs() {
            let hasEmptyTariff = false;

            $.each(tariffs, function (id, tariff) {
                let tariffItem = $(`[data-id="${id}"]`); // Правильный синтаксис

                tariffItem.find('.tariff-days').removeClass('is-invalid');
                tariffItem.find('.days-error').text('');

                // Проверка для кол-ва дней
                if (tariff.days === '') {
                    hasEmptyTariff = true;
                    tariffItem.find('.tariff-days').addClass('is-invalid');
                    tariffItem.find('.days-error').text('Заполните количество дней');
                }

                // Проверка для стоимости
                if (tariff.price === '') {
                    hasEmptyTariff = true;
                    tariffItem.find('.tariff-price').addClass('is-invalid');
                    tariffItem.find('.price-error').text('Заполните стоимость');
                }
            });

            return hasEmptyTariff;
        }

        // Функция для удаления тарифа
        function removeTariff(id) {
            delete tariffs[id];
            renderTariffs();
        }

        function resetFormAndTariffs() {
            tariffs = {}
            $('#tariff-container').empty().append(`<p>Тарифы не добавлены.</p>`)
            $('#product-form')[0].reset();
        }


        // Обработчик для удаления тарифа
        $('#add-tariff').click(function () {
            addTariff();
        });

        // Обработчик для удаления тарифа
        $('#tariff-container').on('click', '.remove-tariff', function () {
            let id = $(this).closest('.tariff-item').data('id');
            removeTariff(id);
        });

        // Обработчик для изменения значений тарифа
        $('#tariff-container').on('change', '.tariff-days, .tariff-price', function () {
            let id = $(this).closest('.tariff-item').data('id');
            let days = $(this).closest('.tariff-item').find('.tariff-days').val();
            let price = $(this).closest('.tariff-item').find('.tariff-price').val();

            // Обновляем данные в объекте tariffs
            tariffs[id] = {days: days, price: price};
            renderTariffs()
        });

        renderTariffs();
    });
</script>
</body>
</html>
