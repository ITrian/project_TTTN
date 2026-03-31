# Hướng dẫn sử dụng chức năng Bảo hành

## 📋 Tổng quan chức năng

Chức năng bảo hành đã được cập nhật với các tính năng sau:

1. **Tra cứu bảo hành theo Serial hoặc Mã hàng** (giữ nguyên)
2. **Xem danh sách toàn bộ sản phẩm đã xuất bán** (mới)
3. **Kiểm tra trạng thái bảo hành tự động** - So sánh ngày hiện tại với ngày xuất
4. **Chi tiết bảo hành từng phiếu xuất** (mới)

---

## 🚀 Cách sử dụng

### 1. Danh sách sản phẩm đã xuất

**URL:** `/warranty/listExported`

**Chức năng:**
- Hiển thị **danh sách tất cả sản phẩm đã xuất (đã bán)** của kho
- Tính toán **thời gian bảo hành** dựa trên:
  - Ngày xuất trong phiếu xuất
  - Thời gian bảo hành của sản phẩm (đơn vị: tháng)
- Hiển thị **trạng thái bảo hành**:
  - **Còn hạn** (badge xanh): Với số ngày còn lại
  - **Hết hạn** (badge đỏ): Với số ngày quá hạn
- Thống kê:
  - Tổng số sản phẩm đã xuất
  - Số sản phẩm còn bảo hành
  - Số sản phẩm hết bảo hành

**Thao tác:**
- Nhấp vào mã phiếu xuất để xem chi tiết
- Bấm **"Tạo phiếu"** để tạo phiếu bảo hành cho sản phẩm còn hạn
- Các sản phẩm hết hạn sẽ khóa nút "Tạo phiếu"

---

### 2. Chi tiết bảo hành phiếu xuất

**URL:** `/warranty/exportDetail/{maPX}`

Ví dụ: `/warranty/exportDetail/PX-12012026-001`

**Chức năng:**
- Hiển thị **thông tin phiếu xuất**:
  - Mã phiếu
  - Ngày xuất
  - Khách hàng
  - Người xuất
  - Ghi chú
  
- Hiển thị **danh sách sản phẩm trong phiếu**:
  - Mã và tên sản phẩm
  - Model
  - Số lượng
  - Loại hàng (Serial/Lô)
  - Thời gian bảo hành
  - **Ngày hết hạn bảo hành**
  - **Khoảng thời gian còn lại** (ngày/tháng)
  - Trạng thái (Còn hạn / Hết hạn)

**Thao tác:**
- Tạo phiếu bảo hành cho từng sản phẩm
- Quay lại danh sách sản phẩm đã xuất

---

## 🔧 Tính toán bảo hành

### Công thức cơ bản
```
Ngày hết hạn = Ngày xuất + Thời gian bảo hành (tháng)
Còn bảo hành? = Ngày hiện tại <= Ngày hết hạn
```

### Ví dụ
- Sản phẩm xuất: **01/01/2026**
- Thời gian BH: **12 tháng**
- Ngày hết hạn: **01/01/2027**
- Ngày hiện tại: **31/03/2026** → **Còn 275 ngày (9 tháng)**

---

## 📊 Trạng thái bảo hành

| Trạng thái | Badge | Ý nghĩa | Hành động |
|-----------|-------|---------|----------|
| Còn hạn | 🟢 | Sản phẩm vẫn trong thời gian bảo hành | Có thể tạo phiếu BH |
| Hết hạn | 🔴 | Sản phẩm đã quá hạn bảo hành | Không thể tạo phiếu |

---

## 💡 Lưu ý

1. **Bộ lọc tự động**: Nếu sản phẩm không có thời gian bảo hành (0 tháng), sẽ được coi là không có bảo hành
2. **Tính toán theo tháng**: Bảo hành được tính theo tháng, không phải ngày
3. **So sánh thời gian thực**: Hệ thống so sánh với ngày/giờ hiện tại của hệ thống
4. **Thống kê chính xác**: Các chỉ số thống kê được cập nhật theo dữ liệu trong database

---

## 🔗 Liên kết nhanh

```
Trang chủ bảo hành:     /warranty
Danh sách hàng đã xuất: /warranty/listExported
Chi tiết phiếu xuất:    /warranty/exportDetail/{maPX}
```

---

## 📝 Bảng dữ liệu liên quan

| Bảng | Cột quan trọng | Mục đích |
|------|---------------|---------|
| **PHIEUXUAT** | maPX, ngayXuat, maKH | Lưu thông tin phiếu xuất |
| **CT_PHIEUXUAT** | maPX, maHH, soLuong | Chi tiết dòng sản phẩm |
| **HANGHOA** | maHH, tenHH, thoiGianBaoHanh | Thông tin sản phẩm & thời gian BH |
| **KHACHHANG** | maKH, tenKH, sdt | Thông tin khách hàng |

