<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Get all orders with pagination
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $page = $request->get('page', 1);
            
            // Sample data - replace with actual database query
            $allOrders = [
                [
                    'id' => '56038079',
                    'name' => 'Kiên Ph?m',
                    'email' => 'admin@admin.com',
                    'phone' => '0394859392',
                    'address' => 'tran phu, hoang mai',
                    'subtotal' => 200.00,
                    'discount_coupon' => '',
                    'discount' => 0,
                    'shipping' => 30.00,
                    'total' => 230.00,
                    'status' => 'new',
                    'note' => '',
                    'currency' => '$',
                    'products' => [
                        [
                            'sku' => '789-07',
                            'name' => 'Sesame Street Ut',
                            'quantity' => 1,
                            'price' => 200.00
                        ]
                    ]
                ],
                [
                    'id' => '56038080',
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                    'phone' => '0123456789',
                    'address' => 'New York, USA',
                    'subtotal' => 150.00,
                    'discount_coupon' => 'SAVE10',
                    'discount' => 15.00,
                    'shipping' => 25.00,
                    'total' => 160.00,
                    'status' => 'processing',
                    'note' => 'Express delivery',
                    'currency' => '$',
                    'products' => [
                        [
                            'sku' => '123-45',
                            'name' => 'Product Sample',
                            'quantity' => 2,
                            'price' => 75.00
                        ]
                    ]
                ],
                [
                    'id' => '56038081',
                    'name' => 'Jane Smith',
                    'email' => 'jane@example.com',
                    'phone' => '0987654321',
                    'address' => 'London, UK',
                    'subtotal' => 300.00,
                    'discount_coupon' => '',
                    'discount' => 0,
                    'shipping' => 40.00,
                    'total' => 340.00,
                    'status' => 'completed',
                    'note' => '',
                    'currency' => '$',
                    'products' => [
                        [
                            'sku' => '456-78',
                            'name' => 'Premium Item',
                            'quantity' => 1,
                            'price' => 300.00
                        ]
                    ]
                ]
            ];

            $total = count($allOrders);
            $offset = ($page - 1) * $perPage;
            $orders = array_slice($allOrders, $offset, $perPage);
            
            $lastPage = ceil($total / $perPage);

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
    public function show($id): JsonResponse
    {
        try {
            // Sample data - replace with actual database query
            $order = [
                'id' => $id,
                'name' => 'Kiên Ph?m',
                'email' => 'admin@admin.com',
                'phone' => '0394859392',
                'address' => 'tran phu, hoang mai',
                'subtotal' => 200.00,
                'discount_coupon' => '-',
                'discount' => 0,
                'shipping' => 30.00,
                'total' => 230.00,
                'status' => 'new',
                'note' => '',
                'currency' => '$',
                'products' => [
                    [
                        'sku' => '789-07',
                        'name' => 'Sesame Street Ut',
                        'quantity' => 1,
                        'price' => 200.00
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $order
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
    public function export(): Response
    {
        try {
            // Sample data - replace with actual database query
            $orders = [
                [
                    'id' => '56038079',
                    'name' => 'Kiên Ph?m',
                    'email' => 'admin@admin.com',
                    'phone' => '0394859392',
                    'address' => 'tran phu, hoang mai',
                    'discount_coupon' => '',
                    'currency' => '$',
                    'discount' => 0,
                    'subtotal' => 200,
                    'shipping' => 30,
                    'total' => 230,
                    'note' => ''
                ]
            ];

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
}
