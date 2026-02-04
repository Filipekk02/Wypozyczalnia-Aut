// === LOGOWANIE ===
function login() {
  const email = document.getElementById("loginEmail").value;
  const password = document.getElementById("loginPassword").value;

  fetch("api.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=login&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
  })
  .then(r => r.json())
  .then(res => {
    if (res.status === "ok") {
      window.location.href = "index.php";
    } else {
      alert(res.msg);
    }
  });
}

// === REJESTRACJA ===
function register() {
  const first = document.getElementById("firstName").value;
  const last = document.getElementById("lastName").value;
  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;
  const confirm = document.getElementById("confirm").value;

  fetch("api.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=register&first=${encodeURIComponent(first)}&last=${encodeURIComponent(last)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&confirm=${encodeURIComponent(confirm)}`
  })
  .then(r => r.json())
  .then(res => {
    alert(res.msg);
    if (res.status === "ok") {
      window.location.href = "login_register.php";
    }
  });
}

// === PRZEKIEROWANIE DO REZERWACJI ===
function bookCar(id) {
  if (!isLoggedIn) {
    window.location.href = "login_register.php";
    return;
  }
  window.location.href = `book.php?car_id=${id}`;
}
