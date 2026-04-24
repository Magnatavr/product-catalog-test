<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Контроллер для управления товарами через API (RESTful)
 * Все методы, кроме index и show, требуют аутентификации (Sanctum)
 */
class ProductController extends Controller
{
    /**
     * GET /api/products – список товаров с пагинацией и фильтрацией по категории
     *
     * @param Request $request (может содержать ?page=... и ?category_id=...)
     * @return AnonymousResourceCollection – коллекция товаров в формате ProductResource
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // Базовый запрос с подгрузкой связей (категория) для избежания N+1
        $query = Product::with('category');

        // Фильтр по категории: если передан category_id и он не пустой, добавляем условие
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Пагинация: по 5 товаров на страницу (можно изменить в ТЗ). Метод paginate() сам обработает ?page=
        $products = $query->paginate(5);

        // Возвращаем ресурс-коллекцию (JSON с meta и links)
        return ProductResource::collection($products);
    }

    /**
     * POST /api/products – создание нового товара (требует токен)
     *
     * @param ProductStoreRequest $request (уже содержит валидированные данные)
     * @return ProductResource – созданный товар с подгруженной категорией
     */
    public function store(ProductStoreRequest $request)
    {
        // Создаём товар из валидированных данных
        $product = Product::create($request->validated());
        // Загружаем связь category и возвращаем ресурс
        return new ProductResource($product->load('category'));
    }

    /**
     * GET /api/products/{id} – просмотр одного товара (публичный)
     *
     * @param Product $product – модель, найденная через route model binding
     * @return ProductResource – товар с категорией
     */
    public function show(Product $product): ProductResource
    {
        // load('category') подтягивает связанную категорию, чтобы избежать дополнительного запроса при сериализации
        return new ProductResource($product->load('category'));
    }

    /**
     * PUT/PATCH /api/products/{id} – обновление товара (требует токен)
     *
     * @param ProductUpdateRequest $request (валидированные данные)
     * @param Product $product – модель для обновления (route model binding)
     * @return ProductResource – обновлённый товар с категорией
     */
    public function update(ProductUpdateRequest $request, Product $product): ProductResource
    {
        // Обновляем товар только валидированными полями
        $product->update($request->validated());
        // Возвращаем обновлённую модель с подгруженной категорией
        return new ProductResource($product->load('category'));
    }

    /**
     * DELETE /api/products/{id} – удаление товара (мягкое удаление, если используется SoftDeletes)
     *
     * @param Product $product – модель для удаления
     * @return JsonResponse – сообщение об успешном удалении
     */
    public function destroy(Product $product): JsonResponse
    {
        // Производим удаление (при наличии SoftDeletes в модели – запись помечается deleted_at)
        $product->delete();
        // Отдаём JSON-ответ с сообщением (статус 200)
        return response()->json(['message' => 'Deleted']);
    }
}
