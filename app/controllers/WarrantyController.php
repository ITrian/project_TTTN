<?php
class WarrantyController extends Controller {
    private $warrantyModel;

    public function __construct() {
        $this->requireLogin();
        // Chỉ người có quyền Bảo hành (hoặc Admin) mới được vào
        $this->requirePermission('Q_BAOHANH');
        $this->warrantyModel = $this->model('WarrantyModel');
    }

    public function index() {
        $result = null;
        $type = '';
        $message = "";

        if (isset($_GET['keyword'])) {
            $keyword = trim($_GET['keyword']);
            
            // 1. Tìm Serial trước
            $serialInfo = $this->warrantyModel->findBySerial($keyword);

            if ($serialInfo) {
                $result = $serialInfo;
                $type = 'SINGLE';
            } else {
                // 2. Tìm theo sản phẩm (Lô)
                $batchList = $this->warrantyModel->findByProduct($keyword);
                if (!empty($batchList)) {
                    $result = $batchList;
                    $type = 'LIST';
                } else {
                    $message = "Không tìm thấy dữ liệu khớp!";
                }
            }
        }

        $this->view('warranty/index', [
            'title' => 'Tra cứu bảo hành',
            'result' => $result,
            'type' => $type,
            'message' => $message,
            'keyword' => $_GET['keyword'] ?? ''
        ]);
    }

    // Hàm mới: Hiển thị danh sách sản phẩm đã xuất (đã bán) với trạng thái bảo hành
    public function listExported() {
        $exportedProducts = $this->warrantyModel->getExportedProductsWithWarranty();
        
        // Tính toán thống kê
        $stats = [
            'total' => count($exportedProducts),
            'conHan' => 0,
            'hetHan' => 0
        ];
        
        foreach ($exportedProducts as $product) {
            if ($product['conBaoHanh']) {
                $stats['conHan']++;
            } else {
                $stats['hetHan']++;
            }
        }

        $this->view('warranty/exported', [
            'title' => 'Danh sách sản phẩm đã xuất',
            'products' => $exportedProducts,
            'stats' => $stats
        ]);
    }

    // Hàm mới: Hiển thị chi tiết sản phẩm từ một phiếu xuất cụ thể
    public function exportDetail($maPX) {
        $exportedProducts = $this->warrantyModel->getExportedProductsByExportId($maPX);
        
        if (empty($exportedProducts)) {
            die("Không tìm thấy phiếu xuất này!");
        }

        // Lấy thông tin header của phiếu xuất
        $exportModel = $this->model('ExportModel');
        $exportInfo = $exportModel->getExportById($maPX);

        // Lấy lịch sử bảo hành cho mỗi sản phẩm (defensive: ignore errors)
        foreach ($exportedProducts as &$product) {
            try {
                // Lấy lịch sử bảo hành theo maHH (no longer filters by soLuong)
                $product['warrantyHistory'] = $this->warrantyModel->getWarrantyHistoryByProduct($product['maHH'], $product['soLuong']);
            } catch (Exception $e) {
                $product['warrantyHistory'] = [];
                error_log("Warranty history fetch failed for maHH: " . $product['maHH'] . " - " . $e->getMessage());
            }
            
            // Lấy lịch sử bảo hành cho từng serial nếu là loại SERIAL
            if ($product['loaiHang'] == 'SERIAL' && !empty($product['serials'])) {
                foreach ($product['serials'] as &$serial) {
                    $serial['history'] = $this->warrantyModel->getWarrantyHistoryBySerial($serial['serial']);
                }
            }
        }

        $this->view('warranty/export_detail', [
            'title' => 'Chi tiết bảo hành phiếu xuất',
            'exportInfo' => $exportInfo,
            'products' => $exportedProducts
        ]);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $maBH = 'BH' . time();
            
            // Lấy dữ liệu từ form
            $data = [
                'maBH' => $maBH,
                'maHH' => $_POST['maHH'],
                'serial' => $_POST['serial'] ?? '',
                'soLuong' => $_POST['soLuong'] ?? '',
                'moTaLoi' => $_POST['moTaLoi'],
                'maND' => $_SESSION['user_id']
            ];

            if ($this->warrantyModel->createTicket($data)) {
                // Chuyển sang trang chi tiết phiếu
                header('Location: ' . BASE_URL . '/warranty/detail/' . $maBH);
                exit;
            } else {
                die("Lỗi: Không thể tạo phiếu bảo hành. Kiểm tra lại dữ liệu đầu vào.");
            }
        }
    }

    // Hàm detail giữ nguyên logic hiển thị
    public function detail($maBH) {
        $ticket = $this->warrantyModel->getTicketDetail($maBH);
        if (!$ticket) die("Không tìm thấy phiếu!");
        $this->view('warranty/detail', ['title' => 'Chi tiết phiếu', 'ticket' => $ticket]);
    }

    // Hàm trả về dữ liệu báo cáo dưới dạng JSON
    public function getReportData() {
        header('Content-Type: application/json');
        
        $products = $this->warrantyModel->getExportedProductsWithWarranty();
        
        $total = count($products);
        $active = 0;
        $expired = 0;
        
        foreach ($products as $item) {
            if (isset($item['conBaoHanh']) && $item['conBaoHanh']) {
                $active++;
            } else {
                $expired++;
            }
        }
        
        // Lấy top 5 sản phẩm gần hết hạn
        $topProducts = $this->warrantyModel->getWarrantyOverview(5);
        
        echo json_encode([
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'topProducts' => $topProducts
        ]);
    }
}
?>