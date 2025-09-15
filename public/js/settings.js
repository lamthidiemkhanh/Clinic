// settings.js - left menu with dynamic right content
(function(){
  function $(s, r=document){ return r.querySelector(s); }
  function $all(s, r=document){ return Array.from(r.querySelectorAll(s)); }
  function save(key, obj){ try{ localStorage.setItem(key, JSON.stringify(obj)); }catch(e){} }
  function load(key, def){ try{ return JSON.parse(localStorage.getItem(key)||'null')||def; }catch(e){ return def; } }

  function viewProfile(){
    const data = load('profile', { name:'', gender:'', dob:'', phone:'', address:'' });
    return `
      <h2 class="section-title">THÔNG TIN CÁ NHÂN</h2>
      <form id="f-profile" class="form-grid">
        <label>Họ và tên<input name="name" type="text" value="${escapeHtml(data.name)}" placeholder="Nguyễn Văn A"></label>
        <fieldset class="inline">
          <legend>Giới tính</legend>
          <label><input type="radio" name="gender" value="Nam" ${data.gender==='Nam'?'checked':''}> Nam</label>
          <label><input type="radio" name="gender" value="Nữ" ${data.gender==='Nữ'?'checked':''}> Nữ</label>
        </fieldset>
        <label>Sinh nhật<input name="dob" type="date" value="${escapeHtml(data.dob)}"></label>
        <label>Số điện thoại<input name="phone" type="tel" value="${escapeHtml(data.phone)}" placeholder="09xxxxxxxx"></label>
        <label>Địa chỉ<textarea name="address" rows="3" placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành">${escapeHtml(data.address)}</textarea></label>
        <button class="btn-primary" type="submit">LƯU</button>
      </form>`;
  }

  function viewPet(){
    const data = load('pet', { name:'', type:'', age:'', sex:'', weight:'', avatar:'' });
    return `
      <h2 class="section-title">PET PROFILE</h2>
      <form id="f-pet" class="form-grid">
        <label>Tên PET<input name="name" type="text" value="${escapeHtml(data.name)}"></label>
        <fieldset class="inline">
          <legend>Loại</legend>
          <label><input type="radio" name="type" value="Chó" ${data.type==='Chó'?'checked':''}> Chó</label>
          <label><input type="radio" name="type" value="Mèo" ${data.type==='Mèo'?'checked':''}> Mèo</label>
          <label><input type="radio" name="type" value="Khác" ${data.type==='Khác'?'checked':''}> Khác</label>
        </fieldset>
        <label>Tuổi<input name="age" type="number" min="0" value="${escapeHtml(data.age)}"></label>
        <fieldset class="inline">
          <legend>Giới tính</legend>
          <label><input type="radio" name="sex" value="Đực" ${data.sex==='Đực'?'checked':''}> Đực</label>
          <label><input type="radio" name="sex" value="Cái" ${data.sex==='Cái'?'checked':''}> Cái</label>
        </fieldset>
        <label>Cân nặng<div class="input-row"><input name="weight" type="number" step="0.1" min="0" value="${escapeHtml(data.weight)}"><span>kg</span></div></label>
        <label>Avatar<input name="avatar" type="file" accept="image/*"></label>
        <button class="btn-primary" type="submit">LƯU</button>
      </form>`;
  }

  function viewDelete(){
    return `
      <h2 class="section-title danger">XÓA TÀI KHOẢN</h2>
      <div class="note">Hành động này không thể hoàn tác. Vui lòng xác nhận.</div>
      <button id="btn-delete" class="btn-danger">XÓA TÀI KHOẢN</button>
    `;
  }

  function viewLogout(){
    return `
      <h2 class="section-title warn">ĐĂNG XUẤT</h2>
      <div class="note">Bạn sẽ cần đăng nhập lại để tiếp tục.</div>
      <button id="btn-logout" class="btn-warn">ĐĂNG XUẤT</button>
    `;
  }

  function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }

  function render(view){
    const root = document.getElementById('settings-view');
    if (!root) return;
    if (view==='profile') root.innerHTML = viewProfile();
    else if (view==='pet') root.innerHTML = viewPet();
    else if (view==='delete') root.innerHTML = viewDelete();
    else if (view==='logout') root.innerHTML = viewLogout();
    bindForms();
  }

  function bindForms(){
    const fp = document.getElementById('f-profile');
    if (fp){
      fp.addEventListener('submit', function(e){ e.preventDefault();
        const fd = new FormData(fp);
        const data = {
          name: fd.get('name')||'',
          gender: fd.get('gender')||'',
          dob: fd.get('dob')||'',
          phone: fd.get('phone')||'',
          address: fd.get('address')||''
        };
        save('profile', data);
        toast('Đã lưu thông tin cá nhân');
      });
    }

    const fpet = document.getElementById('f-pet');
    if (fpet){
      fpet.addEventListener('submit', function(e){ e.preventDefault();
        const fd = new FormData(fpet);
        const data = {
          name: fd.get('name')||'',
          type: fd.get('type')||'',
          age: fd.get('age')||'',
          sex: fd.get('sex')||'',
          weight: fd.get('weight')||''
        };
        // Skip avatar persistence for now
        save('pet', data);
        toast('Đã lưu thông tin PET');
      });
    }

    const btnDel = document.getElementById('btn-delete');
    if (btnDel){
      btnDel.addEventListener('click', function(){
        if (confirm('Bạn có chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác.')){
          try{ localStorage.clear(); }catch(e){}
          alert('Tài khoản đã bị xóa.');
          window.location.href = 'index.html';
        }
      });
    }

    const btnLogout = document.getElementById('btn-logout');
    if (btnLogout){
      btnLogout.addEventListener('click', function(){
        try{ localStorage.removeItem('auth'); }catch(e){}
        alert('Đã đăng xuất');
        window.location.href = 'index.html';
      });
    }
  }

  function toast(msg){
    try{ console.log(msg); }catch(e){}
    const n = document.createElement('div'); n.className='toast'; n.textContent=msg; document.body.appendChild(n);
    setTimeout(()=>{ n.classList.add('show'); }, 10);
    setTimeout(()=>{ n.classList.remove('show'); n.remove(); }, 2500);
  }

  function init(){
    // menu interactions
    $all('.settings-menu .s-item').forEach(btn => {
      btn.addEventListener('click', () => {
        $all('.settings-menu .s-item').forEach(b=>b.classList.remove('active'));
        btn.classList.add('active');
        render(btn.dataset.view);
      });
    });
    render('profile');
  }

  document.addEventListener('DOMContentLoaded', init);
})();


