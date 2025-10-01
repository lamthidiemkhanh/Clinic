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
    dog: 'Ch\u00f3',
    cat: 'M\u00e8o',
    other: 'Kh\u00e1c'
  };

  function money(n){
    try { return Number(n || 0).toLocaleString("vi-VN") + " \u20ab"; }
    catch (err){ return (n || 0) + " \u20ab"; }
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
      dateInput: document.getElementById("bk-date"),
      ownerInput: document.getElementById("owner-name"),
      colorInput: document.getElementById("pet-color"),
      weightInput: document.getElementById("pet-weight"),
      birthInput: document.getElementById("pet-birth"),
      emailInput: document.getElementById("bk-email")
    };
  }

  function updateSummary(){
    const inputs = getInputs();
    if (inputs.dateInput && inputs.dateInput.value) state.date = inputs.dateInput.value;

    const summaryCenter = document.getElementById("sum-center");
    if (summaryCenter) summaryCenter.textContent = state.centerName || (state.centerId ? "Ph\u00f2ng kh\u00e1m #" + state.centerId : "Ph\u00f2ng kh\u00e1m");

    const summaryService = document.getElementById("sum-service");
    if (summaryService) summaryService.textContent = state.serviceName || (state.serviceId ? "D\u1ecbch v\u1ee5 #" + state.serviceId : "D\u1ecbch v\u1ee5");

    const summaryPrice = document.getElementById("sum-price");
    if (summaryPrice) summaryPrice.textContent = money(state.price);

    const summaryTotal = document.getElementById("sum-total");
    if (summaryTotal) summaryTotal.textContent = money(state.price);

    const summaryTime = document.getElementById("sum-time");
    if (summaryTime) {
      summaryTime.textContent = (state.time && state.date)
        ? `${state.time}, ${formatDate(state.date)}`
        : "Ch\u01b0a ch\u1ecdn";
    }

    updateButton(inputs);
  }

  function updateButton(inputs){
    const btn = document.getElementById("btn-confirm");
    if (!btn) return;
    const hasDate = Boolean(state.date);
    const hasTime = Boolean(state.time);
    const hasType = Boolean(inputs.typeSelect && inputs.typeSelect.value);
    const hasName = Boolean(inputs.nameInput && inputs.nameInput.value.trim());
    const ownerRequired = Boolean(inputs.ownerInput);
    const hasOwner = !ownerRequired || Boolean(inputs.ownerInput.value.trim());
    btn.disabled = !(hasDate && hasTime && hasType && hasName && hasOwner);
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
          $all(".tg-btn").forEach(function(b){ b.classList.remove("active"); });
          btn.classList.add("active");
          updateSummary();
        });
        grid.appendChild(btn);
      });
    });
  }

  async function submitBooking(event){
    if (event && typeof event.preventDefault === "function") event.preventDefault();
    const inputs = getInputs();
    const { typeSelect, nameInput, ownerInput, colorInput, weightInput, birthInput, emailInput } = inputs;
    updateButton(inputs);
    const ownerRequired = Boolean(ownerInput);
    const missingRequired = !state.time || !state.date ||
      !typeSelect || !typeSelect.value ||
      !nameInput || !nameInput.value.trim() ||
      (ownerRequired && !ownerInput.value.trim());
    if (missingRequired) {
      const messageBox = document.getElementById("bk-message");
      if (messageBox){
        messageBox.textContent = "Vui l\u00f2ng \u0111i\u1ec1n \u0111\u1ea7y \u0111\u1ee7 th\u00f4ng tin \u0111\u1eb7t l\u1ecbch.";
        messageBox.className = "bk-message error";
      }
      return;
    }

    const messageBox = document.getElementById("bk-message");
    const confirmBtn = document.getElementById("btn-confirm");
    if (messageBox){ messageBox.textContent = ""; messageBox.className = "bk-message"; }
    if (confirmBtn) confirmBtn.disabled = true;

    let weightValue = null;
    if (weightInput && weightInput.value !== "") {
      const parsedWeight = Number(weightInput.value);
      weightValue = Number.isFinite(parsedWeight) ? parsedWeight : null;
    }

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
      owner_name: ownerInput ? ownerInput.value.trim() : "",
      color: colorInput ? colorInput.value.trim() : "",
      weight_gram: weightValue,
      birth_date: birthInput && birthInput.value ? birthInput.value : null,
      email: emailInput ? emailInput.value : ""
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
      if (!res.ok) throw new Error(data && data.error ? data.error : "\u0110\u1eb7t l\u1ecbch th\u1ea5t b\u1ea1i");
      if (messageBox){
        messageBox.textContent = data.message || "\u0110\u1eb7t l\u1ecbch th\u00e0nh c\u00f4ng";
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
        messageBox.textContent = err.message || "C\u00f3 l\u1ed7i x\u1ea3y ra khi \u0111\u1eb7t l\u1ecbch";
        messageBox.className = "bk-message error";
      }
      if (confirmBtn) confirmBtn.disabled = false;
    } finally {
      updateButton(inputs);
    }
  }

  function bindInputs(){
    const inputs = getInputs();
    const { typeSelect, nameInput, dateInput, ownerInput, colorInput, weightInput, birthInput } = inputs;
    if (typeSelect) typeSelect.addEventListener("change", updateSummary);
    if (nameInput) nameInput.addEventListener("input", updateSummary);
    if (ownerInput) ownerInput.addEventListener("input", updateSummary);
    if (colorInput) colorInput.addEventListener("input", updateSummary);
    if (weightInput) weightInput.addEventListener("input", updateSummary);
    if (birthInput) birthInput.addEventListener("change", updateSummary);
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
