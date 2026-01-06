/* cost gio hang */

const KHOA_GIO_HANG = "gio_hang_cua_toi";
const KHOA_GIO_MUA_NGAY = "gio_hang_mua_ngay";
function layGioHang() {
  return JSON.parse(localStorage.getItem(KHOA_GIO_HANG)) || [];
}

function luuGioHang(gio) {
  localStorage.setItem(KHOA_GIO_HANG, JSON.stringify(gio));
}
function layGioMuaNgay() {
  return JSON.parse(localStorage.getItem(KHOA_GIO_MUA_NGAY)) || [];
}
function luuGioMuaNgay(gio) {
  localStorage.setItem(KHOA_GIO_MUA_NGAY, JSON.stringify(gio));
}
function themVaoGio(btn) {
  if (window.IS_LOGGED_IN === false) {
    const quayLai = window.location.pathname.split("/").pop() || "home.php";
    window.location.href = "login.php?next=" + encodeURIComponent(quayLai);
    return;
  }
  const sanPham = {
    id: btn.dataset.id,
    ten: btn.dataset.ten,
    hang: btn.dataset.hang,
    gia: Number(btn.dataset.gia),
    anh: btn.dataset.anh,
    soLuong: 1
  };

  let gio = layGioHang();
  const tonTai = gio.find(sp => sp.id === sanPham.id);

  if (tonTai) {
    tonTai.soLuong += 1;
  } else {
    gio.push(sanPham);
  }

  luuGioHang(gio);
  alert("✅ Đã thêm sản phẩm vào giỏ hàng");
}
function muaNgay(btn) {
  if (window.IS_LOGGED_IN === false) {
    // quay lại đúng trang + giữ query ?id=...
    const next = (window.location.pathname.split("/").pop() || "home.php") + window.location.search;
    window.location.href = "login.php?next=" + encodeURIComponent(next);
    return;
  }

  const sanPham = {
    id: btn.dataset.id,
    ten: btn.dataset.ten,
    hang: btn.dataset.hang,
    gia: Number(btn.dataset.gia),
    anh: btn.dataset.anh,
    soLuong: 1
  };

  // Lưu riêng cho "Mua ngay" để không phá giỏ hàng đang có
  luuGioMuaNgay([sanPham]);

  // Sang order.php để checkout module render
  window.location.href = "order.php";
}

/* giỏ hàng */
function dinhDangTien(tien) {
  return tien.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
}
function capNhatTongTien() {
  const tongTienEl = document.getElementById("tongTien");
  const nutMua = document.getElementById("nutMua");

  let tong = 0;
  let dem = 0;

  document.querySelectorAll(".the-sanpham").forEach(div => {
    const cb = div.querySelector(".chon-sanpham");
    if (!cb || !cb.checked) return;

    const gia = Number(div.dataset.gia || 0);
    const sl = Number(div.querySelector(".so-luong")?.innerText || 1);

    tong += gia * sl;
    dem++;
  });

  tongTienEl.innerText = dinhDangTien(tong);
  nutMua.innerText = `Mua ngay (${dem})`;

  // đồng bộ checkbox "Chọn tất cả"
  const chonTatCa = document.getElementById("chonTatCa");
  if (chonTatCa) {
    const all = document.querySelectorAll(".chon-sanpham");
    const checked = document.querySelectorAll(".chon-sanpham:checked");
    chonTatCa.checked = (all.length > 0 && all.length === checked.length);
  }
}

function veGioHang() {
  const ds = document.getElementById("danhsachGio");
  const tongTienEl = document.getElementById("tongTien");
  const nutMua = document.getElementById("nutMua");

  let gio = layGioHang();

  if (gio.length === 0) {
    ds.innerHTML = "<p>Giỏ hàng trống</p>";
    tongTienEl.innerText = "0đ";
    nutMua.innerText = "Mua ngay (0)";
    return;
  }

  let tong = 0;

  ds.innerHTML = gio.map(sp => {
    

    return `
      <div class="the-sanpham" data-gia="${sp.gia}">
      <input type="checkbox" class="chon-sanpham" checked>
        <img src="${sp.anh}">
        <div class="thongtin">
          <h4>${sp.hang} ${sp.ten}</h4>
          <p class="gia">${dinhDangTien(sp.gia)}</p>
        </div>

        <div class="soluong">
          <button onclick="doiSoLuong('${sp.id}',-1)">-</button>
          <span class="so-luong">${sp.soLuong}</span>
          <button onclick="doiSoLuong('${sp.id}',1)">+</button>
        </div>

        <button class="nut-xoa" onclick="xoaSanPham('${sp.id}')">
          <i class="fa-regular fa-trash-can"></i>
        </button>
      </div>
    `;
  }).join("");
  capNhatTongTien();

  
}

function doiSoLuong(id, delta) {
  let gio = layGioHang();
  let sp = gio.find(x => x.id === id);

  if (!sp) return;
  sp.soLuong += delta;

  if (sp.soLuong <= 0) {
    gio = gio.filter(x => x.id !== id);
  }

  luuGioHang(gio);
  veGioHang();
}

function xoaSanPham(id) {
  let gio = layGioHang().filter(x => x.id !== id);
  luuGioHang(gio);
  veGioHang();
}

const nutMuaEl = document.getElementById("nutMua");
const dsEl = document.getElementById("danhsachGio");

if (dsEl && nutMuaEl) {
  nutMuaEl.onclick = () => {
    alert("Mua hàng mua hàng mua hàng");
  };

  veGioHang();

  document.addEventListener("change", (e) => {
    if (e.target.classList.contains("chon-sanpham")) {
      capNhatTongTien();
    }

    if (e.target.id === "chonTatCa") {
      document.querySelectorAll(".chon-sanpham").forEach(cb => {
        cb.checked = e.target.checked;
      });
      capNhatTongTien();
    }
  });
}
/* đơn hàng */
/* =========================
   CHECKOUT (gộp chung cart.js)
   Chỉ chạy khi trang có #noi-dung-don-hang và #gio_hang_json
========================= */

(function checkoutModule(){
  const tbody = document.getElementById("noi-dung-don-hang");
  const inputJson = document.getElementById("gio_hang_json");
  const tamTinhEl = document.getElementById("tam-tinh");
  const tongTienEl = document.getElementById("tong-tien");
  const nutDatHang = document.getElementById("nut-dat-hang");

  // Nếu không phải trang checkout thì thoát luôn (tránh ảnh hưởng cart.php)
  if (!tbody || !inputJson || !tamTinhEl || !tongTienEl || !nutDatHang) return;

  function dinhDangVnd(tien){
    // dùng đúng format tiền của bạn (dấu chấm + đ) cho đồng nhất
    return tien.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + "đ";
  }

  function taiGioHangCheckout(){
    // dùng lại key KHOA_GIO_HANG đang có ở đầu file cart.js
    // giỏ hàng item: {id, ten, hang, gia, anh, soLuong}
    const gioMuaNgay = layGioMuaNgay();
  if (gioMuaNgay && gioMuaNgay.length > 0) return gioMuaNgay;
    return layGioHang();
  }

  function veTomTatDonHang(){
    const gio = taiGioHangCheckout();

    tbody.innerHTML = "";
    let tong = 0;

    gio.forEach(sp => {
      const tenHienThi = `${sp.hang ?? ""} ${sp.ten ?? ""}`.trim();
      const donGia = Number(sp.gia || 0);
      const soLuong = Number(sp.soLuong || 0);
      const thanhTien = donGia * soLuong;

      tong += thanhTien;

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>
          <div style="font-weight:600">${tenHienThi || ("SP #" + sp.id)}</div>
          <div class="chu-mo-ta">Đơn giá: ${dinhDangVnd(donGia)} × ${soLuong}</div>
        </td>
        <td class="can-phai">${dinhDangVnd(thanhTien)}</td>
      `;
      tbody.appendChild(tr);
    });

    tamTinhEl.textContent = dinhDangVnd(tong);
    tongTienEl.textContent = dinhDangVnd(tong);

    nutDatHang.disabled = gio.length === 0;

    // Chuẩn hoá để PHP insert DB (đúng field mình xử lý ở checkout.php)
    const gioChuan = gio.map(sp => ({
      product_id: Number(sp.id || 0),
      ten_sp: `${sp.hang ?? ""} ${sp.ten ?? ""}`.trim(),
      so_luong: Number(sp.soLuong || 0),
      don_gia: Number(sp.gia || 0),
    }));

    inputJson.value = JSON.stringify(gioChuan);
  }

  // Render khi load checkout
  document.addEventListener("DOMContentLoaded", veTomTatDonHang);

  // Nếu bạn muốn: sau khi đặt hàng thành công (PHP set biến), gọi hàm này để xoá giỏ
  window.xoaGioHangCheckout = function(){
    try {
      const gioMuaNgay = localStorage.getItem(KHOA_GIO_MUA_NGAY);
    if (gioMuaNgay) localStorage.removeItem(KHOA_GIO_MUA_NGAY);
      localStorage.removeItem(KHOA_GIO_HANG); 
    } catch(e){}
  };
})();
