<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Get all orders with pagination
     */
    public function index(Request $request, Site $site): JsonResponse
    {
        try {
            if ($site->workspace->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);

            $perPage = max(1, (int) $perPage);
            $page = max(1, (int) $page);

            $domainName = $site->domain_name;
            $dbName = str_replace('.' . DOMAIN_BASE, '', $domainName);
            if (!empty($site->db_name)) {
                $dbName = $site->db_name;
            }
            $connectionName = setupTenantConnection($dbName);

            $query = DB::connection($connectionName)
                ->table('orders')
                ->orderBy('id', 'desc');

            $total = (clone $query)->count();
            $offset = ($page - 1) * $perPage;
            $rawOrders = $query
                ->offset($offset)
                ->limit($perPage)
                ->get();

            $orders = $rawOrders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'name' => $order->name,
                    'email' => $order->email,
                    'phone' => $order->phone,
                    'address' => $order->address,
                    'subtotal' => floatval($order->subtotal ?? 0),
                    'discount_coupon' => $order->discount_coupon ?? '',
                    'discount' => floatval($order->discount ?? 0),
                    'shipping' => floatval($order->shipping ?? 0),
                    'total' => floatval($order->total ?? 0),
                    'status' => $this->mapOrderStatus($order->status ?? 0),
                    'note' => $order->note ?? '',
                    'currency' => $order->currency ?? '$',
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            })->toArray();

            $lastPage = (int) ceil($total / $perPage);

            return response()->json([
                'success' => true,
                'data' => $orders,
                'pagination' => [
                    'current_page' => (int) $page,
                    'per_page' => (int) $perPage,
                    'total' => $total,
                    'last_page' => $lastPage,
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch orders: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order details by ID
     */
    public function show(Site $site, $id): JsonResponse
    {
        try {
            if ($site->workspace->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $domainName = $site->domain_name;
            $dbName = str_replace('.' . DOMAIN_BASE, '', $domainName);
            if (!empty($site->db_name)) {
                $dbName = $site->db_name;
            }
            $connectionName = setupTenantConnection($dbName);

            $order = DB::connection($connectionName)
                ->table('orders')
                ->where('id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            $orderProducts = DB::connection($connectionName)
                ->table('order_product')
                ->where('order_id', $order->id)
                ->get();

            $products = $orderProducts->map(function ($product) {
                return [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'quantity' => (int) $product->quantity,
                    'price' => floatval($product->price ?? 0),
                ];
            })->toArray();

            $orderData = [
                'id' => $order->id,
                'order_no' => $order->order_no,
                'name' => $order->name,
                'email' => $order->email,
                'phone' => $order->phone,
                'address' => $order->address,
                'subtotal' => floatval($order->subtotal ?? 0),
                'discount_coupon' => $order->discount_coupon ?? '',
                'discount' => floatval($order->discount ?? 0),
                'shipping' => floatval($order->shipping ?? 0),
                'total' => floatval($order->total ?? 0),
                'status' => $this->mapOrderStatus($order->status ?? 0),
                'note' => $order->note ?? '',
                'currency' => $order->currency ?? '$',
                'products' => $products,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ];

            return response()->json([
                'success' => true,
                'data' => $orderData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch order details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export orders to Excel
     */
    public function export(Site $site): Response
    {
        try {
            if ($site->workspace->user_id !== Auth::id()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $domainName = $site->domain_name;
            $dbName = str_replace('.' . DOMAIN_BASE, '', $domainName);
            if (!empty($site->db_name)) {
                $dbName = $site->db_name;
            }
            $connectionName = setupTenantConnection($dbName);

            $orders = DB::connection($connectionName)
                ->table('orders')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'name' => $order->name,
                        'email' => $order->email,
                        'phone' => $order->phone,
                        'address' => $order->address,
                        'discount_coupon' => $order->discount_coupon ?? '',
                        'currency' => $order->currency ?? '$',
                        'discount' => floatval($order->discount ?? 0),
                        'subtotal' => floatval($order->subtotal ?? 0),
                        'shipping' => floatval($order->shipping ?? 0),
                        'total' => floatval($order->total ?? 0),
                        'note' => $order->note ?? '',
                    ];
                })
                ->toArray();

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $headers = [
                'A1' => 'Order',
                'B1' => 'Name', 
                'C1' => 'Email',
                'D1' => 'Phone',
                'E1' => 'Address',
                'F1' => 'Discount Coupon',
                'G1' => 'Currency',
                'H1' => 'Discount',
                'I1' => 'Subtotal',
                'J1' => 'Shipping',
                'K1' => 'Total',
                'L1' => 'Note'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Add data rows
            $row = 2;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $order['id']);
                $sheet->setCellValue('B' . $row, $order['name']);
                $sheet->setCellValue('C' . $row, $order['email']);
                $sheet->setCellValue('D' . $row, $order['phone']);
                $sheet->setCellValue('E' . $row, $order['address']);
                $sheet->setCellValue('F' . $row, $order['discount_coupon']);
                $sheet->setCellValue('G' . $row, $order['currency']);
                $sheet->setCellValue('H' . $row, $order['discount']);
                $sheet->setCellValue('I' . $row, $order['subtotal']);
                $sheet->setCellValue('J' . $row, $order['shipping']);
                $sheet->setCellValue('K' . $row, $order['total']);
                $sheet->setCellValue('L' . $row, $order['note']);
                $row++;
            }

            $writer = new Xlsx($spreadsheet);
            
            return new StreamedResponse(function() use ($writer) {
                $writer->save('php://output');
            }, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="orders_export.xlsx"',
                'Cache-Control' => 'max-age=0',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export orders: ' . $e->getMessage()
            ], 500);
        }
    }

    private function mapOrderStatus($status): string
    {
        $map = [
            0 => 'new',
            1 => 'processing',
            2 => 'completed',
            3 => 'cancelled',
        ];

        return $map[$status] ?? 'new';
    }
}
