<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\Discounts\DiscountManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Display a listing of the Order.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'customerId' => 'required'
        ]);

        $customer = Customer::findOrFail($request->customerId);

        return response()->json(OrderResource::collection($customer->orders));
    }

    /**
     * Store a newly created Order in storage.
     * @throws \Exception
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Start DB transaction
            DB::beginTransaction();;

            $request->validate([
                'customerId' => 'required|exists:customers,id',
                'items' => 'required|array',
                'items.*.productId' => [
                    'required',
                    'distinct:strict', // productId must be unique withing the items list
                    Rule::in(Product::pluck('id')->toArray()) // productId must exist in 'products' table
                ],
                'items.*.quantity' => 'required|integer',
            ]);

            $totalPrice = 0;
            $items = [];

            foreach ($request->items as $item) {
                $productId = $item['productId'];
                $quantity = $item['quantity'];

                $product = Product::find($productId);

                // Product out of stock
                if ($product->stock < $quantity) {
                    // Rollback previous changes in case of an error
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Item #' . $productId . ': the requested quantity ('. $quantity . ') exceeds available stock!'
                    ], 400);
                }

                $totalPrice += $product->unit_price;
                $items[$productId] = ['quantity' => $quantity, 'unit_price' => $product->price];

                // Normally, this operation should be done after order creation and items attachment.
                // But since we are using DB functions like begin, commit and rollback to control errors and exception,
                // this should be safe. -- Muhammed
                $product->stock -= $quantity;
                $product->save();
            }

            $order = Order::create([
                'customer_id' => $request->customerId,
                'total' => $totalPrice
            ]);

            // Add items to order
            $order->items()->attach($items);

            // Commit changes
            DB::commit();

            return response()->json([
                'message' => 'Order created.',
                'order' => OrderResource::make($order)
            ], 201);

        } catch (\Exception $exception) {
            // Rollback DB changes in case of exception thrown
            DB::rollBack();
            return response()->json([ 'error' => $exception->getMessage() ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        return response()->json(OrderResource::make($order));
    }

    /**
     * Remove the specified Order from storage.
     */
    public function destroy(Order $order)
    {
        try {
            $order->delete();

            return response()->json([ 'message' => 'Order deleted.' ]);
        } catch (\Exception $exception) {
            return response()->json([ 'error' => $exception->getMessage() ], 400);
        }
    }
    /**
     * Calculate discounts for specified Order
     */
    public function discounts(Order $order)
    {
        try {
            $manager = new DiscountManager();
            $result = $manager->applyDiscounts($order);

            return response()->json($result);
        } catch (\Exception $exception) {
            return response()->json([ 'error' => $exception->getMessage() ], 400);
        }

    }
}
