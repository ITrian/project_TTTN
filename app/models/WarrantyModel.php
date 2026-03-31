<?php
class WarrantyModel {
    private $conn;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    // 1. Tìm thông tin theo Serial (Dành cho hàng có Serial) -> Update logic tính hạn BH theo ngày xuất
    public function findBySerial($keyword) {
        // Ưu tiên tìm trong danh sách ĐÃ XUẤT (đã bán) trước
        $sqlSold = "SELECT s.serial, 'SERIAL' as loaiTimKiem,
                           h.tenHH, h.maHH, h.model, h.loaiHang, h.thoiGianBaoHanh,
                           px.ngayXuat,
                           kh.tenKH, kh.sdt,
                           'SOLD' as tinhTrang
                    FROM ct_phieuxuat_serial s
                    JOIN phieuxuat px ON s.maPX = px.maPX
                    JOIN hanghoa h ON s.maHH = h.maHH
                    LEFT JOIN khachhang kh ON px.maKH = kh.maKH
                    WHERE s.serial = :keyword
                    ORDER BY px.ngayXuat DESC 
                    LIMIT 1";
        
        $stmt = $this->conn->prepare($sqlSold);
        $stmt->execute(['keyword' => $keyword]);
        $sold = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sold) {
            // Tính ngày hết hạn = Ngày xuất + Thời gian bảo hành (tháng)
            if (!empty($sold['ngayXuat']) && isset($sold['thoiGianBaoHanh'])) {
                $exportDate = new DateTime($sold['ngayXuat']);
                $months = (int)$sold['thoiGianBaoHanh'];
                $sold['hanBaoHanh'] = $exportDate->modify("+$months months")->format('Y-m-d');
            } else {
                $sold['hanBaoHanh'] = null;
            }
            return $sold;
        }

        // Nếu chưa bán, tìm trong kho (nhưng chưa kích hoạt bảo hành khách hàng)
        $sqlStock = "SELECT s.serial, 'SERIAL' as loaiTimKiem,
                            h.tenHH, h.maHH, h.model, h.loaiHang,
                            'IN_STOCK' as tinhTrang
                     FROM hanghoa_serial s
                     JOIN lohang lh ON s.maLo = lh.maLo
                     JOIN hanghoa h ON lh.maHH = h.maHH
                     WHERE s.serial = :keyword";
        $stmt2 = $this->conn->prepare($sqlStock);
        $stmt2->execute(['keyword' => $keyword]);
        return $stmt2->fetch(PDO::FETCH_ASSOC);
    }

    // 2. Tìm theo Mã hàng hoặc Tên hàng (Dành cho hàng Lô)
    public function findByProduct($keyword) {
        // Logic mới: Lấy NCC từ phieunhap
        $sql = "SELECT lh.maLo, lh.ngayNhap, lh.hanBaoHanh,
                       hh.tenHH, hh.maHH, hh.model, hh.loaiHang,
                       ncc.tenNCC, ncc.maNCC,
                       'LO' as loaiTimKiem
                FROM lohang lh
                JOIN phieunhap pn ON lh.maPN = pn.maPN      -- Join thêm
                JOIN nhacungcap ncc ON pn.maNCC = ncc.maNCC -- Join thêm
                JOIN hanghoa hh ON lh.maHH = hh.maHH
                WHERE (hh.maHH = :keyword OR hh.tenHH LIKE :keywordLike)
                ORDER BY lh.ngayNhap DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'keyword' => $keyword,
            'keywordLike' => "%$keyword%"
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Tạo phiếu bảo hành (CẬP NHẬT QUAN TRỌNG: Thêm maHH)
    public function createTicket($data) {
        // Bảng phieubh mới yêu cầu cột maHH
        $sql = "INSERT INTO phieubh (maBH, maHH, serial, ngayNhan, moTaLoi, trangThai, maND) 
                VALUES (:maBH, :maHH, :serial, NOW(), :moTaLoi, 0, :maND)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    // 4. Lấy chi tiết phiếu (Giữ nguyên logic nhưng query đơn giản hơn chút)
    public function getTicketDetail($maBH) {
        $sql = "SELECT p.*, nd.tenND, hh.tenHH 
                FROM phieubh p
                LEFT JOIN nguoidung nd ON p.maND = nd.maND
                LEFT JOIN hanghoa hh ON p.maHH = hh.maHH -- Join trực tiếp để lấy tên hàng
                WHERE p.maBH = :maBH";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['maBH' => $maBH]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 5. Lấy danh sách tất cả sản phẩm đã xuất (đã bán) với thông tin bảo hành
    public function getExportedProductsWithWarranty() {
        $sql = "SELECT 
                    px.maPX,
                    px.ngayXuat,
                    ct.maHH,
                    h.tenHH,
                    h.model,
                    h.loaiHang,
                    h.thoiGianBaoHanh,
                    ct.soLuong,
                    ct.donGia,
                    kh.tenKH,
                    kh.sdt,
                    nd.tenND as nguoiTao
                FROM PHIEUXUAT px
                JOIN CT_PHIEUXUAT ct ON px.maPX = ct.maPX
                JOIN HANGHOA h ON ct.maHH = h.maHH
                LEFT JOIN KHACHHANG kh ON px.maKH = kh.maKH
                LEFT JOIN NGUOIDUNG nd ON px.maNDXuat = nd.maND
                ORDER BY px.ngayXuat DESC, ct.maHH ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Xử lý tính toán hạn bảo hành cho mỗi sản phẩm
        foreach ($results as &$item) {
            if (!empty($item['ngayXuat']) && isset($item['thoiGianBaoHanh'])) {
                $exportDate = new DateTime($item['ngayXuat']);
                $months = (int)$item['thoiGianBaoHanh'];
                $expiryDate = clone $exportDate;
                $expiryDate->modify("+$months months");
                
                $item['hanBaoHanh'] = $expiryDate->format('Y-m-d');
                $item['hanBaoHanh_formatted'] = $expiryDate->format('d/m/Y');
                
                // Kiểm tra còn bảo hành hay không
                $now = new DateTime();
                $item['conBaoHanh'] = ($now <= $expiryDate);
                
                // Tính số ngày còn lại
                if ($item['conBaoHanh']) {
                    $interval = $now->diff($expiryDate);
                    $item['ngayConLai'] = $interval->days;
                    $item['soThang'] = $interval->m + ($interval->y * 12);
                } else {
                    $interval = $expiryDate->diff($now);
                    $item['ngayQuaHan'] = $interval->days;
                }
            } else {
                $item['hanBaoHanh'] = null;
                $item['conBaoHanh'] = false;
            }
        }
        
        return $results;
    }

    // 6. Lấy danh sách sản phẩm đã xuất theo phiếu xuất cụ thể với warranty status
    public function getExportedProductsByExportId($maPX) {
        $sql = "SELECT 
                    px.maPX,
                    px.ngayXuat,
                    ct.maHH,
                    h.tenHH,
                    h.model,
                    h.loaiHang,
                    h.thoiGianBaoHanh,
                    ct.soLuong,
                    ct.donGia,
                    kh.tenKH,
                    kh.sdt
                FROM PHIEUXUAT px
                JOIN CT_PHIEUXUAT ct ON px.maPX = ct.maPX
                JOIN HANGHOA h ON ct.maHH = h.maHH
                LEFT JOIN KHACHHANG kh ON px.maKH = kh.maKH
                WHERE px.maPX = :maPX
                ORDER BY ct.maHH ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['maPX' => $maPX]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Xử lý tính toán hạn bảo hành cho mỗi sản phẩm
        foreach ($results as &$item) {
            if (!empty($item['ngayXuat']) && isset($item['thoiGianBaoHanh'])) {
                $exportDate = new DateTime($item['ngayXuat']);
                $months = (int)$item['thoiGianBaoHanh'];
                $expiryDate = clone $exportDate;
                $expiryDate->modify("+$months months");
                
                $item['hanBaoHanh'] = $expiryDate->format('Y-m-d');
                $item['hanBaoHanh_formatted'] = $expiryDate->format('d/m/Y');
                
                // Kiểm tra còn bảo hành hay không
                $now = new DateTime();
                $item['conBaoHanh'] = ($now <= $expiryDate);
                
                // Tính số ngày còn lại
                if ($item['conBaoHanh']) {
                    $interval = $now->diff($expiryDate);
                    $item['ngayConLai'] = $interval->days;
                    $item['soThang'] = $interval->m + ($interval->y * 12);
                } else {
                    $interval = $expiryDate->diff($now);
                    $item['ngayQuaHan'] = $interval->days;
                }
            } else {
                $item['hanBaoHanh'] = null;
                $item['conBaoHanh'] = false;
            }
            
            // Lấy danh sách serial cho sản phẩm này (nếu là loại SERIAL)
            if ($item['loaiHang'] == 'SERIAL') {
                $item['serials'] = $this->getSerialsByProductInExport($maPX, $item['maHH']);
            } else {
                $item['serials'] = [];
            }
        }
        
        return $results;
    }

    // 7. Lấy danh sách serial của một sản phẩm cụ thể trong một phiếu xuất
    public function getSerialsByProductInExport($maPX, $maHH) {
        $sql = "SELECT 
                    cps.serial,
                    CASE 
                        WHEN cps.trangThai = 'ACTIVE' THEN 'Có hạn'
                        WHEN cps.trangThai = 'EXPIRED' THEN 'Hết hạn'
                        ELSE 'Unknown'
                    END as trangThaiSerial
                FROM CT_PHIEUXUAT_SERIAL cps
                WHERE cps.maPX = :maPX AND cps.maHH = :maHH
                ORDER BY cps.serial ASC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['maPX' => $maPX, 'maHH' => $maHH]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>