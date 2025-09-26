(function(){
  "use strict";

  function $(selector, root){ return (root || document).querySelector(selector); }
  function $all(selector, root){ return Array.from((root || document).querySelectorAll(selector)); }

  const params = new URLSearchParams(location.search);
  const state = {
    centerId: params.get("center_id") || "",
    serviceId: params.get("service_id") || "",
    serviceName: params.get("service_name") || "",
    price: Number(params.get("price") || 0),
    centerName: params.get("center_name") || "",
    date: "",
    time: ""
  };

  if (typeof window !== "undefined") {
    window.bookingState = state;
  }

  const petTypeLabels = {
    dog: "Chó",
    cat: "Mèo",
    bird: "Chim",
    other: "Khác"
  };

  function money(n){
    try { return Number(n || 0).toLocaleString("vi-VN") + " ₫"; }
    catch (err){ return (n || 0) + " ₫"; }
  }

  function formatDate(iso){
    if (!iso) return "";
    const parts = iso.split("-");
    if (parts.length !== 3) return iso;
    return parts[2] + "/" + parts[1] + "/" + parts[0];
  }

  function getInputs(){
    return {
      typeSelect: document.getElementById("pet-type"),
      nameInput: document.getElementById("pet-name"),
      dateInput: document.getElementById("bk-date")
    };
  }

  function updateSummary(){
    const { typeSelect, nameInput, dateInput } = getInputs();
    if (dateInput && dateInput.value) state.date = dateInput.value;

    const summaryCenter = document.getElementById("sum-center");
    if (summaryCenter) summaryCenter.textContent = state.centerName || (state.centerId ? "Phòng khám #" + state.centerId : "Phòng khám");

    const summaryService = document.getElementById("sum-service");
    if (summaryService) summaryService.textContent = state.serviceName || (state.serviceId ? "Dịch vụ #" + state.serviceId : "Dịch vụ");

    const summaryPrice = document.getElementById("sum-price");
    if (summaryPrice) summaryPrice.textContent = money(state.price);

    const summaryTotal = document.getElementById("sum-total");
    if (summaryTotal) summaryTotal.textContent = money(state.price);

    const summaryTime = document.getElementById("sum-time");
    if (summaryTime) {
      summaryTime.textContent = (state.time && state.date)
        ? `${state.time}, ${formatDate(state.date)}`
        : "Chưa chọn";
    }

    updateButton(typeSelect, nameInput);
  }

  function updateButton(typeSelect, nameInput){
    const btn = document.getElementById("btn-confirm");
    if (!btn) return;
    const hasDate = Boolean(state.date);
    const hasTime = Boolean(state.time);
    const hasType = Boolean(typeSelect && typeSelect.value);
    const hasName = Boolean(nameInput && nameInput.value.trim());
    btn.disabled = !(hasDate && hasTime && hasType && hasName);
  }

  function renderTimeButtons(){
    const slots = {
      morning: ["06:00","07:00","08:00","09:00","10:00","11:00"],
      afternoon: ["12:00","13:00","14:00","15:00","16:00","17:00"],
      evening: ["18:00","19:00","20:00"]
    };

    Object.keys(slots).forEach(function(period){
      const grid = document.querySelector(`.tg-grid[data-period="${period}"]`);
      if (!grid) return;
      grid.innerHTML = "";
      slots[period].forEach(function(time){
        const btn = document.createElement("button");
        btn.type = "button";
        btn.className = "tg-btn";
        btn.textContent = time;
        btn.addEventListener("click", function(){
          state.time = time;
          const { typeSelect, nameInput } = getInputs();
          $all(".tg-btn").forEach(function(b){ b.classList.remove("active"); });
          btn.classList.add("active");
          updateSummary(typeSelect, nameInput);
        });
        grid.appendChild(btn);
      });
    });
  }

  async function submitBooking(event){
    if (event && typeof event.preventDefault === "function") event.preventDefault();
    const { typeSelect, nameInput } = getInputs();
    updateButton(typeSelect, nameInput);
    if (!state.time || !state.date || !typeSelect || !typeSelect.value || !nameInput || !nameInput.value.trim()) {
      const messageBox = document.getElementById("bk-message");
      if (messageBox){
        messageBox.textContent = "Vui lòng điền đầy đủ thông tin đặt lịch.";
        messageBox.className = "bk-message error";
      }
      return;
    }

    const messageBox = document.getElementById("bk-message");
    const confirmBtn = document.getElementById("btn-confirm");
    if (messageBox){ messageBox.textContent = ""; messageBox.className = "bk-message"; }
    if (confirmBtn) confirmBtn.disabled = true;

    const payload = {
      center_id: state.centerId,
      service_id: state.serviceId,
      date: state.date,
      time: state.time,
      price: state.price,
      service_name: state.serviceName,
      center_name: state.centerName,
      pet_type: typeSelect.value,
      pet_type_label: petTypeLabels[typeSelect.value] || "",
      pet_name: nameInput.value.trim(),
      email: document.getElementById("bk-email") ? document.getElementById("bk-email").value : ""
    };

    try {
      const res = await fetch("index.php?page=api.appointments", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });
      const raw = await res.text();
      let data;
      try {
        data = raw ? JSON.parse(raw) : {};
      } catch (parseErr) {
        throw new Error(raw);
      }
      if (!res.ok) throw new Error(data && data.error ? data.error : "Đặt lịch thất bại");
      if (messageBox){
        messageBox.textContent = data.message || "Đặt lịch thành công";
        messageBox.className = "bk-message success";
      }
      const timeLabel = (state.time && state.date) ? `${state.time}, ${formatDate(state.date)}` : (state.time || (state.date ? formatDate(state.date) : ""));
      if (typeof window.notifyBookingSuccess === "function") {
        window.notifyBookingSuccess({
          center_id: state.centerId,
          center_name: state.centerName,
          service_id: state.serviceId,
          service_name: state.serviceName,
          date: state.date,
          time: state.time,
          time_label: timeLabel || undefined,
          price: state.price,
          pet_name: payload.pet_name,
          pet_type: payload.pet_type,
          pet_type_label: payload.pet_type_label,
          appointment_id: data && data.appointment_id ? data.appointment_id : undefined,
          subject: state.serviceName || undefined,
          text: data && data.message ? data.message : undefined
        });
      }
    } catch (err){
      if (messageBox){
        messageBox.textContent = err.message || "Có lỗi xảy ra khi đặt lịch";
        messageBox.className = "bk-message error";
      }
      if (confirmBtn) confirmBtn.disabled = false;
    } finally {
      updateButton(typeSelect, nameInput);
    }
  }

  function bindInputs(){
    const { typeSelect, nameInput, dateInput } = getInputs();
    if (typeSelect) typeSelect.addEventListener("change", updateSummary);
    if (nameInput) nameInput.addEventListener("input", updateSummary);
    if (dateInput){
      dateInput.addEventListener("change", function(){
        state.date = dateInput.value;
        state.time = "";
        $all(".tg-btn").forEach(function(btn){ btn.classList.remove("active"); });
        updateSummary();
      });
    }
  }

  function init(){
    const today = new Date();
    const iso = today.toISOString().slice(0, 10);
    state.date = iso;
    const dateInput = document.getElementById("bk-date");
    if (dateInput) dateInput.value = iso;

    bindInputs();
    renderTimeButtons();
    updateSummary();

    const confirmBtn = document.getElementById("btn-confirm");
    if (confirmBtn) confirmBtn.addEventListener("click", submitBooking);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
