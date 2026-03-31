<?php
class HomeController extends Controller {
    private $homeModel;
    private $warrantyModel;

    public function __construct() {
        // Bắt buộc đăng nhập mới được xem Dashboard
        $this->requireLogin();
        
        // Load Model vừa tạo
        $this->homeModel = $this->model('HomeModel');
        $this->warrantyModel = $this->model('WarrantyModel');
    }

    public function index() {
        // Lấy dữ liệu thực tế
        $totalProducts = $this->homeModel->countProducts();
        $totalInventory = $this->homeModel->sumInventory();
        $lowStock = $this->homeModel->getLowStockProducts(10); // Lấy top 10 hàng sắp hết
        
        // Lấy danh sách sản phẩm bảo hành (có hạn)
        $warrantyProducts = $this->warrantyModel->getWarrantyOverview(5); // Top 5 sản phẩm gần hết hạn

        $data = [
            'title' => 'Trang chủ - Quản lý kho',
            'total_products' => $totalProducts,
            'total_inventory' => $totalInventory,
            'low_stock_products' => $lowStock,
            'warranty_products' => $warrantyProducts
        ];

        // Gọi View
        $this->view('home/index', $data);
    }
}
?>