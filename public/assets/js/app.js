(function () {
  "use strict";

  var el = document.getElementById("api-status");
  if (!el) {
    return;
  }

  fetch("api/health.php", { headers: { Accept: "application/json" } })
    .then(function (res) {
      if (!res.ok) {
        throw new Error("HTTP " + res.status);
      }
      return res.json();
    })
    .then(function (data) {
      el.textContent = JSON.stringify(data, null, 2);
    })
    .catch(function (err) {
      el.textContent = "Error: " + err.message;
    });
})();
