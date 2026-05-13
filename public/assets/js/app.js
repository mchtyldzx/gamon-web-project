(function () {
  "use strict";

  function setText(id, text) {
    var el = document.getElementById(id);
    if (el) {
      el.textContent = text;
    }
  }

  function clearList(id) {
    var ul = document.getElementById(id);
    if (!ul) {
      return;
    }
    while (ul.firstChild) {
      ul.removeChild(ul.firstChild);
    }
  }

  function appendListItem(ul, text) {
    var li = document.createElement("li");
    li.textContent = text;
    ul.appendChild(li);
  }

  fetch("api/health.php", { headers: { Accept: "application/json" } })
    .then(function (res) {
      if (!res.ok) {
        throw new Error("HTTP " + res.status);
      }
      return res.json();
    })
    .then(function (data) {
      var el = document.getElementById("api-status");
      if (el) {
        el.textContent = JSON.stringify(data, null, 2);
      }
    })
    .catch(function (err) {
      setText("api-status", "Error: " + err.message);
    });

  fetch("api/meta.php", { headers: { Accept: "application/json" } })
    .then(function (res) {
      return res.json().then(function (body) {
        if (!res.ok) {
          var msg = body.error || body.detail || "HTTP " + res.status;
          var hint = body.hint ? " " + body.hint : "";
          throw new Error(msg + hint);
        }
        return body;
      });
    })
    .then(function (data) {
      var catUl = document.getElementById("category-list");
      var hoodUl = document.getElementById("neighborhood-list");
      clearList("category-list");
      clearList("neighborhood-list");
      if (!catUl || !hoodUl) {
        return;
      }
      if (!data.categories || !data.categories.length) {
        appendListItem(catUl, "No categories.");
      } else {
        data.categories.forEach(function (c) {
          appendListItem(
            catUl,
            c.name + " (" + c.code + ")" + (c.description ? " — " + c.description : "")
          );
        });
      }
      if (!data.neighborhoods || !data.neighborhoods.length) {
        appendListItem(hoodUl, "No neighborhoods.");
      } else {
        data.neighborhoods.forEach(function (n) {
          appendListItem(hoodUl, n.name + ", " + n.locality);
        });
      }
    })
    .catch(function (err) {
      clearList("category-list");
      clearList("neighborhood-list");
      var catUl = document.getElementById("category-list");
      if (catUl) {
        appendListItem(catUl, err.message);
      }
    });
})();
